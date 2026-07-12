<?php

namespace Modules\InvoiceMaker\Services;

use App\Models\Team;
use Modules\InvoiceMaker\Models\Expense;
use Modules\InvoiceMaker\Models\Invoice;

class DashboardSnapshot
{
    public function forTeam(?Team $team): array
    {
        if (! $team) {
            return [
                'revenue' => 0,
                'outstanding' => 0,
                'overdue' => 0,
                'expenses' => 0,
                'recentInvoices' => collect(),
            ];
        }

        $invoices = Invoice::withoutGlobalScopes()->where('team_id', $team->id);

        return [
            'revenue' => (clone $invoices)->where('type', Invoice::TYPE_INVOICE)->sum('amount_paid'),
            'outstanding' => (clone $invoices)
                ->where('type', Invoice::TYPE_INVOICE)
                ->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE])
                ->sum('amount_due'),
            'overdue' => (clone $invoices)
                ->where('type', Invoice::TYPE_INVOICE)
                ->where('status', Invoice::STATUS_OVERDUE)
                ->count(),
            'expenses' => Expense::withoutGlobalScopes()->where('team_id', $team->id)->sum('amount'),
            'recentInvoices' => (clone $invoices)
                ->with('client')
                ->where('type', Invoice::TYPE_INVOICE)
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }
}
