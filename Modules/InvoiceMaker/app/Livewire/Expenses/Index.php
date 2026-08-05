<?php

namespace Modules\InvoiceMaker\Livewire\Expenses;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\InvoiceMaker\Models\Expense;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $category = '';

    public $sortBy = 'date';

    public $sortDirection = 'desc';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(Expense $expense)
    {
        $this->authorize('delete', $expense);

        // Also delete the associated Cash Book Entry explicitly if it exists
        // (the DB constraints are just set null, but we want it fully removed to clean up)
        if ($expense->cash_book_entry) {
            $expense->cash_book_entry->delete();
        }

        $expense->delete();
        session()->flash('message', 'Expense and its accounting entry deleted successfully.');
    }

    public function render()
    {
        $expenses = app(InvoiceMakerContext::class)->profile()->expenses()
            ->when($this->search, function ($query) {
                $query->where('description', 'like', '%'.$this->search.'%')
                    ->orWhere('category', 'like', '%'.$this->search.'%');
            })
            ->when($this->category, function ($query) {
                $query->where('category', $this->category);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        $categories = app(InvoiceMakerContext::class)->profile()->expenses()
            ->select('category')
            ->distinct()
            ->pluck('category');

        return view('invoicemaker::livewire.expenses.index', [
            'expenses' => $expenses,
            'categories' => $categories,
        ]);
    }
}
