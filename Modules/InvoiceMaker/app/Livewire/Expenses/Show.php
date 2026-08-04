<?php

namespace Modules\InvoiceMaker\Livewire\Expenses;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Expense;

#[Layout('layouts.shell')]
class Show extends Component
{
    public Expense $expense;

    public function mount(Expense $expense)
    {
        $this->authorize('view', $expense);
        $this->expense = $expense;
    }

    public function render()
    {
        return view('invoicemaker::livewire.expenses.show');
    }
}
