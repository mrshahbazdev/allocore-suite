<?php

namespace App\Http\Controllers\Admin;

use App\Models\Module;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::with('modules')->get();
        $modules = Module::all();

        return view('admin.plans', compact('plans', 'modules'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePlan($request);

        $plan = Plan::create($validated + ['slug' => Str::slug($validated['name'])]);
        $plan->modules()->sync($request->input('modules', []));

        return back()->with('success', __('Plan created.'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $this->validatePlan($request);

        $plan->update($validated);
        $plan->modules()->sync($request->input('modules', []));

        return back()->with('success', __('Plan updated.'));
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return back()->with('success', __('Plan deleted.'));
    }

    protected function validatePlan(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'billable_scope' => 'required|in:user,team,both',
            'is_active' => 'boolean',
        ]);
    }
}
