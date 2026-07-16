<?php

namespace App\Http\Controllers\Admin;

use App\Models\ToolSubscription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SubscriptionApprovalController extends Controller
{
    public function index(Request $request)
    {
        $pending = ToolSubscription::where('payment_method', 'bank')
            ->where('status', 'pending')
            ->whereNotNull('gateway_reference')
            ->with(['plan', 'billable'])
            ->latest()
            ->get();

        $recent = ToolSubscription::with(['plan', 'billable'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.subscriptions', compact('pending', 'recent'));
    }

    public function approve(ToolSubscription $subscription)
    {
        abort_unless($subscription->payment_method === 'bank' && $subscription->status === 'pending', 422);

        $subscription->activate();

        return back()->with('success', __('Subscription approved and activated.'));
    }

    public function reject(ToolSubscription $subscription)
    {
        abort_unless($subscription->payment_method === 'bank' && $subscription->status === 'pending', 422);

        $subscription->update(['status' => 'rejected']);

        return back()->with('success', __('Subscription rejected.'));
    }

    public function cancel(ToolSubscription $subscription)
    {
        abort_unless(in_array($subscription->status, ['active', 'pending']), 422);

        $subscription->update(['status' => 'cancelled', 'ends_at' => now()]);

        return back()->with('success', __('Subscription cancelled.'));
    }
}
