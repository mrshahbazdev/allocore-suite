<?php

namespace Modules\InvoiceMaker\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Dashboard extends Component
{
    public function mount()
    {
        if (Auth::user()->role === 'client') {
            return redirect()->route('client.dashboard');
        }

        $business = app(InvoiceMakerContext::class)->profile();
        if ($business && $business->templates()->count() === 0) {
            $business->seedDefaultTemplates();
        }
    }

    public function render()
    {
        $business = app(InvoiceMakerContext::class)->profile();

        $stats = [
            'total_invoices' => $business->invoices()->count(),
            'paid_invoices' => $business->invoices()->where('status', Invoice::STATUS_PAID)->count(),
            'pending_invoices' => $business->invoices()->where('status', Invoice::STATUS_SENT)->count(),
            'overdue_invoices' => $business->invoices()->where('status', Invoice::STATUS_OVERDUE)->count(),
            'total_revenue' => $business->invoices()->where('status', Invoice::STATUS_PAID)->sum('grand_total'),
            'total_expenses' => $business->expenses()->sum('amount'),
            'net_profit' => $business->invoices()->where('status', Invoice::STATUS_PAID)->sum('grand_total') - $business->expenses()->sum('amount'),
            'pending_amount' => $business->invoices()->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE])->sum('amount_due'),
            'total_clients' => $business->clients()->count(),
            'recent_payments' => $business->invoices()
                ->whereHas('payments')
                ->with(['payments', 'client'])
                ->latest()
                ->take(5)
                ->get(),
        ];

        $recentInvoices = $business->invoices()
            ->with('client')
            ->latest()
            ->take(5)
            ->get();

        $revenueByMonth = array_fill(1, 12, 0);
        $business->invoices()
            ->where('status', Invoice::STATUS_PAID)
            ->whereYear('invoice_date', now()->year)
            ->get()
            ->each(function ($invoice) use (&$revenueByMonth) {
                $month = (int) $invoice->invoice_date->format('n');
                $revenueByMonth[$month] += (float) $invoice->grand_total;
            });

        $expensesByMonth = array_fill(1, 12, 0);
        $business->expenses()
            ->whereYear('date', now()->year)
            ->get()
            ->each(function ($expense) use (&$expensesByMonth) {
                $month = (int) $expense->date->format('n');
                $expensesByMonth[$month] += (float) $expense->amount;
            });

        $expensesByCategory = $business->expenses()
            ->selectRaw('category, sum(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $maxAmount = max(
            collect($revenueByMonth)->max() ?: 0,
            collect($expensesByMonth)->max() ?: 0,
            100 // Minimum floor for scaling
        );

        return view('invoicemaker::livewire.dashboard', compact('stats', 'recentInvoices', 'revenueByMonth', 'expensesByMonth', 'expensesByCategory', 'maxAmount'));
    }
}
