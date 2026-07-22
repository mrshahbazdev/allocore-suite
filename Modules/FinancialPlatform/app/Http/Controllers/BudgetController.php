<?php

namespace Modules\FinancialPlatform\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FinancialPlatform\Models\Budget;
use Modules\InvoiceMaker\Models\Expense;

class BudgetController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $budgets = Budget::query()
            ->where('year', $year)
            ->where('month', $month)
            ->get();

        $actual = Expense::withoutGlobalScopes()
            ->where('team_id', auth()->user()->current_team_id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('financialplatform::budgets.index', [
            'budgets' => $budgets,
            'actual' => $actual,
            'year' => $year,
            'month' => $month,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'year' => 'required|integer|min:2000',
            'month' => 'required|integer|between:1,12',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
        ]);

        Budget::create($validated + [
            'team_id' => auth()->user()->current_team_id,
            'user_id' => auth()->id(),
            'currency' => $validated['currency'] ?? 'EUR',
        ]);

        return back()->with('success', __('Budget saved.'));
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $budget->delete();

        return back()->with('success', __('Budget deleted.'));
    }
}
