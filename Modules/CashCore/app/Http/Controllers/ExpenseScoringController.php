<?php

namespace Modules\CashCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\CashCore\Models\CashExpenseScore;
use Modules\CashCore\Models\CashTransaction;

class ExpenseScoringController extends Controller
{
    public function index(): View
    {
        $unscoredExpenses = CashTransaction::expense()->doesntHave('expenseScore')->with('category')->orderByDesc('amount')->get();
        $scoredExpenses = CashTransaction::expense()->has('expenseScore')->with('category', 'expenseScore')->orderByDesc('amount')->get();

        return view('cashcore::scoring.index', compact('unscoredExpenses', 'scoredExpenses'));
    }

    public function score(CashTransaction $transaction): View
    {
        return view('cashcore::scoring.form', compact('transaction'));
    }

    public function storeScore(Request $request, CashTransaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'purpose' => 'nullable|string|max:255',
            'benefit' => 'nullable|string|max:255',
            'revenue_score' => 'required|integer|min:0|max:10',
            'efficiency_score' => 'required|integer|min:0|max:10',
            'strategic_score' => 'required|integer|min:0|max:10',
            'usage_score' => 'required|integer|min:0|max:10',
        ]);

        $score = CashExpenseScore::updateOrCreate(
            ['cashcore_transaction_id' => $transaction->id],
            $validated
        );

        $score->calculateTotal();
        $score->save();

        return redirect()->route('cashcore.scoring.index')->with('success', __('Score saved.'));
    }
}
