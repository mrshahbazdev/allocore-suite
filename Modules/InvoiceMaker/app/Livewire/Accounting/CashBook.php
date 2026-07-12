<?php

namespace Modules\InvoiceMaker\Livewire\Accounting;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\InvoiceMaker\Models\CashBookEntry;

#[Layout('layouts.shell')]
class CashBook extends Component
{
    use WithPagination;

    public string $type = '';

    public function render()
    {
        $entries = CashBookEntry::query()
            ->with('category')
            ->when($this->type, fn ($query) => $query->where('type', $this->type))
            ->latest('date')
            ->paginate(20);
        $income = CashBookEntry::where('type', 'income')->sum('amount');
        $expenses = CashBookEntry::where('type', 'expense')->sum('amount');

        return view('invoicemaker::livewire.accounting.cash-book', compact('entries', 'income', 'expenses'));
    }
}
