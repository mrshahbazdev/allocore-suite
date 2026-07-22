<?php

namespace Modules\CashCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\CashCore\Models\CashCategory;
use Modules\CashCore\Models\CashTransaction;
use Modules\CashCore\Services\CashCoreService;

class CashTransactionController extends Controller
{
    public function __construct(private CashCoreService $service) {}

    public function index(Request $request): View
    {
        $filter = $request->get('filter', 'all');
        $query = CashTransaction::with('category')->orderByDesc('transaction_date');

        if ($filter === 'income') {
            $query->income();
        } elseif ($filter === 'expense') {
            $query->expense();
        }

        $transactions = $query->paginate(20)->withQueryString();
        $categories = CashCategory::all();

        return view('cashcore::transactions.index', compact('transactions', 'categories', 'filter'));
    }

    public function create(): View
    {
        $categories = CashCategory::all();

        return view('cashcore::transactions.form', ['transaction' => new CashTransaction, 'categories' => $categories]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['is_recurring'] = $request->boolean('is_recurring');

        CashTransaction::create($validated);

        return redirect()->route('cashcore.transactions.index')->with('success', __('Transaction created.'));
    }

    public function edit(CashTransaction $transaction): View
    {
        $categories = CashCategory::all();

        return view('cashcore::transactions.form', compact('transaction', 'categories'));
    }

    public function update(Request $request, CashTransaction $transaction): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['is_recurring'] = $request->boolean('is_recurring');

        $transaction->update($validated);

        return redirect()->route('cashcore.transactions.index')->with('success', __('Transaction updated.'));
    }

    public function destroy(CashTransaction $transaction): RedirectResponse
    {
        $transaction->delete();

        return redirect()->route('cashcore.transactions.index')->with('success', __('Transaction deleted.'));
    }

    public function importForm(): View
    {
        return view('cashcore::transactions.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);

        $path = $request->file('csv_file')->getRealPath();
        $count = $this->service->importCsv(auth()->user()->current_team_id, $path);

        return redirect()->route('cashcore.transactions.index')->with('success', __('Imported :count records.', ['count' => $count]));
    }

    private function rules(): array
    {
        return [
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'cashcore_category_id' => 'nullable|exists:cashcore_categories,id',
            'transaction_date' => 'required|date',
            'is_recurring' => 'nullable|boolean',
            'recurring_interval' => 'nullable|in:monthly,quarterly,yearly',
            'notes' => 'nullable|string',
        ];
    }
}
