<?php

namespace Modules\FinancialPlatform\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FinancialPlatform\Models\BankTransaction;
use Modules\FinancialPlatform\Services\BankImportService;
use Modules\FinancialPlatform\Services\CashflowForecastService;

class BankTransactionController extends Controller
{
    public function index(Request $request, CashflowForecastService $cashflow): View
    {
        $transactions = BankTransaction::query()
            ->latest('transaction_date')
            ->paginate(25);

        return view('financialplatform::bank-transactions.index', [
            'transactions' => $transactions,
            'cashflow' => $cashflow->forTeam(auth()->user()->currentTeam),
        ]);
    }

    public function import(Request $request, BankImportService $importService): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,mt940',
        ]);

        $result = $importService->import(
            $request->file('file'),
            auth()->user()->current_team_id,
            auth()->id()
        );

        return back()->with($result['imported'] > 0 ? 'success' : 'error', $result['message']);
    }

    public function destroy(BankTransaction $bankTransaction): RedirectResponse
    {
        $bankTransaction->delete();

        return back()->with('success', __('Transaction deleted.'));
    }
}
