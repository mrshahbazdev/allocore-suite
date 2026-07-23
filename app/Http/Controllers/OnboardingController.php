<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Plan;
use App\Models\Team;
use App\Models\ToolSubscription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OnboardingController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if ($user->onboarding_completed_at) {
            return redirect()->route('dashboard');
        }

        $step = $this->currentStep($user);
        $modules = Module::where('is_active', true)->orderBy('name')->get();
        $plans = Plan::where('is_active', true)->whereHas('modules')->with('modules')->get();

        return view('onboarding.index', compact('user', 'step', 'modules', 'plans'));
    }

    public function storeTeam(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:100',
        ]);

        $team = Team::create($validated + ['owner_id' => $request->user()->id]);
        $team->members()->attach($request->user()->id, ['role' => 'owner']);

        $request->user()->update([
            'current_team_id' => $team->id,
            'onboarding_step' => 1,
        ]);

        return redirect()->route('onboarding.index');
    }

    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $user = $request->user();

        ToolSubscription::create([
            'billable_type' => get_class($user),
            'billable_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'payment_method' => 'trial',
            'billing_interval' => 'monthly',
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 0,
            'starts_at' => now(),
            'ends_at' => now()->addDays(14),
        ]);

        $user->update(['onboarding_step' => 2]);

        return redirect()->route('onboarding.index');
    }

    public function complete(Request $request)
    {
        $request->user()->update([
            'onboarding_step' => 3,
            'onboarding_completed_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', __('Welcome aboard!'));
    }

    protected function currentStep($user): int
    {
        if (! $user->current_team_id) {
            return 0;
        }

        if (! $user->activeSubscriptions()->exists()) {
            return 1;
        }

        return $user->onboarding_step >= 2 ? 2 : 0;
    }
}
