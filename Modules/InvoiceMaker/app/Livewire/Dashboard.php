<?php

namespace Modules\InvoiceMaker\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Models\Expense;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Dashboard extends Component
{
    public function mount(InvoiceMakerContext $context): void
    {
        $context->profile();
    }

    public function render()
    {
        $invoiceQuery = Invoice::where('type', Invoice::TYPE_INVOICE);
        $revenue = (clone $invoiceQuery)->sum('amount_paid');
        $expenses = Expense::sum('amount');
        $stats = [
            'total_invoices' => (clone $invoiceQuery)->count(),
            'paid_invoices' => (clone $invoiceQuery)->where('status', Invoice::STATUS_PAID)->count(),
            'outstanding' => (clone $invoiceQuery)
                ->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE])
                ->sum('amount_due'),
            'overdue' => (clone $invoiceQuery)->where('status', Invoice::STATUS_OVERDUE)->count(),
            'revenue' => $revenue,
            'expenses' => $expenses,
            'profit' => $revenue - $expenses,
            'clients' => Client::count(),
        ];
        $recentInvoices = (clone $invoiceQuery)->with('client')->latest()->limit(8)->get();

        return view('invoicemaker::livewire.dashboard', compact('stats', 'recentInvoices'));
    }
}
