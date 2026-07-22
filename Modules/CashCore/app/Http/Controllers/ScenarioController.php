<?php

namespace Modules\CashCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\CashCore\Models\CashScenario;
use Modules\CashCore\Models\CashTransaction;

class ScenarioController extends Controller
{
    public function index(): View
    {
        $scenarios = CashScenario::latest()->get();

        return view('cashcore::scenarios.index', compact('scenarios'));
    }

    public function create(): View
    {
        $period = now()->format('Y-m');
        $currentRevenue = CashTransaction::income()->forPeriod($period)->sum('amount');
        $currentCosts = CashTransaction::expense()->forPeriod($period)->sum('amount');

        return view('cashcore::scenarios.form', ['scenario' => new CashScenario, 'currentRevenue' => $currentRevenue, 'currentCosts' => $currentCosts]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'current_revenue' => 'required|numeric|min:0',
            'current_costs' => 'required|numeric|min:0',
            'adjusted_revenue' => 'required|numeric|min:0',
            'adjusted_costs' => 'required|numeric|min:0',
        ]);

        $validated['projected_profit'] = $validated['adjusted_revenue'] - $validated['adjusted_costs'];

        CashScenario::create($validated);

        return redirect()->route('cashcore.scenarios.index')->with('success', __('Scenario saved.'));
    }

    public function destroy(CashScenario $scenario): RedirectResponse
    {
        $scenario->delete();

        return redirect()->route('cashcore.scenarios.index')->with('success', __('Scenario deleted.'));
    }
}
