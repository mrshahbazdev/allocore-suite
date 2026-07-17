<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\ToolSubscription;
use App\Models\User;
use Illuminate\Http\Request;

class UserSubscriptionController extends Controller
{
    public function index(User $user)
    {
        $user->load(['toolSubscriptions.plan.modules']);
        $plans = Plan::with('modules')->where('is_active', true)->orderBy('name')->get();

        return view('admin.users.subscriptions', compact('user', 'plans'));
    }

    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_interval' => 'required|in:monthly,yearly',
            'payment_method' => 'required|in:bank,stripe,paypal,manual',
            'status' => 'required|in:pending,active,cancelled',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'gateway_reference' => 'nullable|string|max:255',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $validated = $this->normalizeDates($validated);

        $user->toolSubscriptions()->create($validated);

        return back()->with('success', __('admin.subscriptions.created'));
    }

    public function update(Request $request, User $user, ToolSubscription $subscription)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_interval' => 'required|in:monthly,yearly',
            'payment_method' => 'required|in:bank,stripe,paypal,manual',
            'status' => 'required|in:pending,active,cancelled',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'gateway_reference' => 'nullable|string|max:255',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $validated = $this->normalizeDates($validated);

        $subscription->update($validated);

        return back()->with('success', __('admin.subscriptions.updated'));
    }

    public function approve(User $user, ToolSubscription $subscription)
    {
        $subscription->activate();

        return back()->with('success', __('admin.subscriptions.approved'));
    }

    public function cancel(User $user, ToolSubscription $subscription)
    {
        $subscription->update(['status' => 'cancelled', 'ends_at' => now()]);

        return back()->with('success', __('admin.subscriptions.cancelled'));
    }

    public function destroy(User $user, ToolSubscription $subscription)
    {
        $subscription->delete();

        return back()->with('success', __('admin.subscriptions.deleted'));
    }

    private function normalizeDates(array $data): array
    {
        $interval = ($data['billing_interval'] ?? 'monthly') === 'yearly' ? now()->addYear() : now()->addMonth();

        if ($data['status'] === 'active') {
            $data['starts_at'] ??= now();
            $data['ends_at'] ??= $interval;
        } elseif ($data['status'] === 'cancelled') {
            $data['ends_at'] ??= now();
        }

        return $data;
    }
}
