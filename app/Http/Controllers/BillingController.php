<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\TaxRate;
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
            'coupon_code' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        $billable = $validated['billable'] === 'team' ? $user->currentTeam : $user;

        if ($validated['billable'] === 'team' && ! $billable) {
            return back()->with('error', __('You must create or select a team first.'));
        }

        $pricing = $this->calculatePricing($plan, $validated['interval'], $validated['coupon_code'] ?? null, $billable);

        if ($pricing['coupon'] && ! $pricing['coupon']->isValid()) {
            return back()->with('error', __('The provided coupon is not valid.'));
        }

        $subscription = ToolSubscription::create([
            'billable_type' => $billable->getMorphClass(),
            'billable_id' => $billable->getKey(),
            'plan_id' => $plan->id,
            'coupon_id' => $pricing['coupon']?->id,
            'tax_rate_id' => $pricing['taxRate']?->id,
            'payment_method' => $validated['payment_method'],
            'billing_interval' => $validated['interval'],
            'subtotal' => $pricing['subtotal'],
            'discount_amount' => $pricing['discountAmount'],
            'tax_amount' => $pricing['taxAmount'],
            'total' => $pricing['total'],
            'status' => 'pending',
        ]);

        return match ($validated['payment_method']) {
            'stripe' => $this->stripeCheckout($subscription, $plan, $pricing),
            'paypal' => $this->paypalCheckout($subscription, $plan, $pricing),
            'bank' => redirect()->route('billing.bank', $subscription),
        };
    }

    protected function calculatePricing(Plan $plan, string $interval, ?string $couponCode, $billable): array
    {
        $subtotal = (float) $plan->priceFor($interval);
        $coupon = null;
        $discountAmount = 0;

        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon && $coupon->isValid()) {
                $discountAmount = $coupon->applyDiscount($subtotal);
            }
        }

        $country = $billable instanceof Team ? $billable->country : null;
        $taxRate = TaxRate::forCountry($country);
        $taxAmount = round(($subtotal - $discountAmount) * (($taxRate?->rate ?? 0) / 100), 2);

        return [
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discountAmount' => $discountAmount,
            'taxRate' => $taxRate,
            'taxAmount' => $taxAmount,
            'total' => round($subtotal - $discountAmount + $taxAmount, 2),
        ];
    }

    protected function stripeCheckout(ToolSubscription $subscription, Plan $plan, array $pricing)
    {
        $session = Cashier::stripe()->checkout->sessions->create([
            'mode' => 'payment',
            'customer_email' => Auth::user()->email,
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($plan->currency),
                    'unit_amount' => (int) round($pricing['total'] * 100),
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

    protected function paypalCheckout(ToolSubscription $subscription, Plan $plan, array $pricing)
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
            route('billing.plans'),
            $pricing['total']
        );

        if (! $order) {
            $subscription->delete();

            return back()->with('error', __('Could not create PayPal order.'));
        }

        $subscription->update(['gateway_reference' => $order['order_id']]);

        return redirect()->away($order['approve_url']);
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
            $this->activateSubscription($subscription);

            return redirect()->route('dashboard')->with('success', __('Payment successful! Your tools are now unlocked.'));
        }

        return redirect()->route('billing.subscriptions')->with('error', __('Payment was not completed.'));
    }

    public function paypalSuccess(Request $request, ToolSubscription $subscription)
    {
        $this->authorizeSubscription($subscription);

        $orderId = $request->query('token');
        if (! $orderId || $orderId !== $subscription->gateway_reference) {
            return redirect()->route('billing.subscriptions')->with('error', __('Invalid PayPal response.'));
        }

        if ($subscription->status === 'pending' && app(PayPalService::class)->captureOrder($orderId)) {
            $this->activateSubscription($subscription);

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

    protected function activateSubscription(ToolSubscription $subscription): void
    {
        $subscription->activate();

        if ($subscription->coupon) {
            $subscription->coupon->recordUse();
        }
    }
}
