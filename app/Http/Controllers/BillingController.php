<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Cashier;

class BillingController extends Controller
{
    public function plans(Request $request)
    {
        $plans = Plan::where('is_active', true)->with('modules')->get();
        $highlightModule = $request->query('module');

        return view('billing.plans', compact('plans', 'highlightModule'));
    }

    public function subscriptions()
    {
        $user = Auth::user();
        $subscriptions = $user->toolSubscriptions()->with('plan.modules')->latest()->get();
        $teamSubscriptions = collect();
        foreach ($user->teams as $team) {
            $teamSubscriptions = $teamSubscriptions->merge(
                $team->toolSubscriptions()->with('plan.modules')->latest()->get()
            );
        }

        return view('billing.subscriptions', compact('subscriptions', 'teamSubscriptions'));
    }

    public function checkout(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'interval' => 'required|in:monthly,yearly',
            'payment_method' => 'required|in:stripe,paypal,bank',
            'billable' => 'required|in:user,team',
        ]);

        $user = Auth::user();
        $billable = $validated['billable'] === 'team' ? $user->currentTeam : $user;

        if ($validated['billable'] === 'team' && ! $billable) {
            return back()->with('error', __('You must create or select a team first.'));
        }

        $subscription = ToolSubscription::create([
            'billable_type' => $billable->getMorphClass(),
            'billable_id' => $billable->getKey(),
            'plan_id' => $plan->id,
            'payment_method' => $validated['payment_method'],
            'billing_interval' => $validated['interval'],
            'status' => 'pending',
        ]);

        return match ($validated['payment_method']) {
            'stripe' => $this->stripeCheckout($subscription, $plan),
            'paypal' => $this->paypalCheckout($subscription, $plan),
            'bank' => redirect()->route('billing.bank', $subscription),
        };
    }

    protected function stripeCheckout(ToolSubscription $subscription, Plan $plan)
    {
        $session = Cashier::stripe()->checkout->sessions->create([
            'mode' => 'payment',
            'customer_email' => Auth::user()->email,
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($plan->currency),
                    'unit_amount' => (int) round($plan->priceFor($subscription->billing_interval) * 100),
                    'product_data' => ['name' => $plan->name.' ('.$subscription->billing_interval.')'],
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('billing.stripe.success', $subscription).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('billing.plans'),
            'metadata' => ['tool_subscription_id' => $subscription->id],
        ]);

        $subscription->update(['gateway_reference' => $session->id]);

        return redirect()->away($session->url);
    }

    public function stripeSuccess(Request $request, ToolSubscription $subscription)
    {
        $this->authorizeSubscription($subscription);

        $sessionId = $request->query('session_id');
        if (! $sessionId || $sessionId !== $subscription->gateway_reference) {
            return redirect()->route('billing.subscriptions')->with('error', __('Invalid payment session.'));
        }

        $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);

        if ($session->payment_status === 'paid' && $subscription->status === 'pending') {
            $subscription->activate();

            return redirect()->route('dashboard')->with('success', __('Payment successful! Your tools are now unlocked.'));
        }

        return redirect()->route('billing.subscriptions')->with('error', __('Payment was not completed.'));
    }

    protected function paypalCheckout(ToolSubscription $subscription, Plan $plan)
    {
        $paypal = app(PayPalService::class);

        if (! $paypal->isConfigured()) {
            $subscription->delete();

            return back()->with('error', __('PayPal is not configured yet.'));
        }

        $order = $paypal->createOrder(
            $plan,
            $subscription->billing_interval,
            route('billing.paypal.success', $subscription),
            route('billing.plans')
        );

        if (! $order) {
            $subscription->delete();

            return back()->with('error', __('Could not create PayPal order.'));
        }

        $subscription->update(['gateway_reference' => $order['order_id']]);

        return redirect()->away($order['approve_url']);
    }

    public function paypalSuccess(Request $request, ToolSubscription $subscription)
    {
        $this->authorizeSubscription($subscription);

        $orderId = $request->query('token');
        if (! $orderId || $orderId !== $subscription->gateway_reference) {
            return redirect()->route('billing.subscriptions')->with('error', __('Invalid PayPal response.'));
        }

        if ($subscription->status === 'pending' && app(PayPalService::class)->captureOrder($orderId)) {
            $subscription->activate();

            return redirect()->route('dashboard')->with('success', __('Payment successful! Your tools are now unlocked.'));
        }

        return redirect()->route('billing.subscriptions')->with('error', __('PayPal payment could not be captured.'));
    }

    public function bank(ToolSubscription $subscription)
    {
        $this->authorizeSubscription($subscription);

        return view('billing.bank', compact('subscription'));
    }

    public function bankSubmit(Request $request, ToolSubscription $subscription)
    {
        $this->authorizeSubscription($subscription);

        $validated = $request->validate([
            'reference' => 'required|string|max:255',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $receiptPath = $request->hasFile('receipt')
            ? $request->file('receipt')->store('bank-receipts', 'local')
            : null;

        $subscription->update([
            'gateway_reference' => $validated['reference'],
            'receipt_path' => $receiptPath,
        ]);

        return redirect()->route('billing.subscriptions')
            ->with('success', __('Bank transfer submitted. Your subscription will be activated after admin approval.'));
    }

    protected function authorizeSubscription(ToolSubscription $subscription): void
    {
        $user = Auth::user();
        $allowed = ($subscription->billable_type === $user->getMorphClass() && $subscription->billable_id === $user->id)
            || ($subscription->billable_type === (new Team)->getMorphClass()
                && $user->teams()->where('teams.id', $subscription->billable_id)->exists());

        abort_unless($allowed, 403);
    }
}
