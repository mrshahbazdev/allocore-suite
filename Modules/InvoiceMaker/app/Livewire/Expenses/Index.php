<?php

namespace Modules\InvoiceMaker\Livewire\Expenses;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\InvoiceMaker\Models\AccountingCategory;
use Modules\InvoiceMaker\Models\CashBookEntry;
use Modules\InvoiceMaker\Models\Expense;
use Modules\InvoiceMaker\Services\AccountingService;

#[Layout('layouts.shell')]
class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $category_id = null;

    public string $amount = '';

    public string $date = '';

    public ?string $partner_name = null;

    public ?string $reference_number = null;

    public ?string $description = null;

    public function mount(): void
    {
        $this->date = today()->toDateString();
    }

    public function save(AccountingService $accounting): void
    {
        $data = $this->validate([
            'category_id' => ['nullable', 'integer'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'date' => ['required', 'date'],
            'partner_name' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        if ($this->category_id) {
            AccountingCategory::findOrFail($this->category_id);
        }
        $accounting->createExpense($data);
        $this->reset(['showForm', 'category_id', 'amount', 'partner_name', 'reference_number', 'description']);
        $this->date = today()->toDateString();
        session()->flash('success', __('Expense recorded.'));
    }

    public function delete(Expense $expense): void
    {
        CashBookEntry::where('expense_id', $expense->id)->delete();
        $expense->delete();
        session()->flash('success', __('Expense deleted.'));
    }

    public function render()
    {
        $expenses = Expense::with('category')->latest('date')->paginate(15);
        $categories = AccountingCategory::where('type', 'expense')->orderBy('name')->get();

        return view('invoicemaker::livewire.expenses.index', compact('expenses', 'categories'));
    }
}
