<?php

namespace Modules\InvoiceMaker\Livewire\Expenses;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\InvoiceMaker\Models\AccountingCategory;
use Modules\InvoiceMaker\Models\CashBookEntry;
use Modules\InvoiceMaker\Models\Expense;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Edit extends Component
{
    use WithFileUploads;

    public Expense $expense;

    public $source = 'cash';

    public $category_id = null;

    public $invoice_id = null;

    public $category = '';

    public $amount;

    public $date;

    public $description = '';

    public $receipt;

    public $posting_rule = '';

    public $partner_name = '';

    public $reference_number = '';

    protected $rules = [
        'source' => 'required|in:cash,bank',
        'category_id' => 'required|exists:accounting_categories,id',
        'invoice_id' => 'nullable|exists:invoices,id',
        'amount' => 'required|numeric|min:0',
        'date' => 'required|date',
        'description' => 'required|string|max:255',
        'partner_name' => 'nullable|string|max:255',
        'reference_number' => 'nullable|string|max:255',
        'receipt' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:2048',
    ];

    public function mount(Expense $expense)
    {
        $this->authorize('update', $expense);
        $this->expense = $expense;
        $this->category_id = $expense->category_id;
        $this->category = $expense->category;
        $this->amount = $expense->amount;
        $this->date = $expense->date->format('Y-m-d');
        $this->description = $expense->description;
        $this->partner_name = $expense->partner_name;
        $this->reference_number = $expense->reference_number;
        $this->invoice_id = $expense->invoice_id;

        // Try to get source from CashBookEntry
        $cashBookEntry = CashBookEntry::where('expense_id', $expense->id)->first();
        if ($cashBookEntry) {
            $this->source = $cashBookEntry->source;
        }

        $this->updatedCategoryId($this->category_id);
    }

    public function updatedCategoryId($value)
    {
        $category = AccountingCategory::find($value);
        $this->posting_rule = $category ? $category->posting_rule : '';
        $this->category = $category ? $category->name : '';
    }

    public function save()
    {
        $this->validate();

        $data = [
            'category_id' => $this->category_id,
            'category' => $this->category,
            'amount' => $this->amount,
            'date' => $this->date,
            'description' => $this->description,
            'partner_name' => $this->partner_name,
            'reference_number' => $this->reference_number,
            'invoice_id' => $this->invoice_id ?: null,
        ];

        if ($this->receipt) {
            // Delete old receipt if exists
            if ($this->expense->receipt_path) {
                Storage::disk('public')->delete($this->expense->receipt_path);
            }
            $data['receipt_path'] = $this->receipt->store('receipts', 'public');
        }

        DB::transaction(function () use ($data) {
            $this->expense->update($data);

            // Update or create Cash Book Entry
            CashBookEntry::updateOrCreate(
                ['expense_id' => $this->expense->id],
                [
                    'team_id' => $this->expense->team_id,
                    'date' => $this->date,
                    'amount' => $this->amount,
                    'type' => 'expense',
                    'source' => $this->source,
                    'description' => $this->description,
                    'partner_name' => $this->partner_name,
                    'reference_number' => $this->reference_number,
                    'category_id' => $this->category_id,
                    'invoice_id' => $this->invoice_id ?: null,
                ]
            );
        });

        session()->flash('message', 'Expense updated successfully.');

        return redirect()->route('invoicemaker.expenses.index');
    }

    public function render()
    {
        $business = app(InvoiceMakerContext::class)->profile();
        $categories = $business->accounting_categories ?? AccountingCategory::where('team_id', $business->team_id)->get();
        $invoices = $business->invoices()->orderBy('created_at', 'desc')->get();

        return view('invoicemaker::livewire.expenses.edit', [
            'categories' => $categories,
            'invoices' => $invoices,
        ]);
    }
}
