<?php

namespace Modules\RevenuePlanner\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceMakerBridgeService
{
    /**
     * Retrieve aggregated actual revenue and expenses from InvoiceMaker / CashCore.
     *
     * @return array{
     *     has_data: bool,
     *     actual_revenue_annual: float,
     *     actual_revenue_monthly: float,
     *     fixed_costs_annual: float,
     *     fixed_costs_breakdown: array<string, float>,
     *     invoice_count: int
     * }
     */
    public function pullFromInvoiceMaker(int $teamId): array
    {
        $hasData = false;
        $totalPaidRevenue = 0.0;
        $invoiceCount = 0;

        // 1. Pull Paid Invoices
        if (Schema::hasTable('invoicemaker_invoices')) {
            $invoices = DB::table('invoicemaker_invoices')
                ->where('team_id', $teamId)
                ->whereIn('status', ['paid', 'completed'])
                ->where('created_at', '>=', now()->subYear())
                ->get();

            if ($invoices->isNotEmpty()) {
                $hasData = true;
                $invoiceCount = $invoices->count();
                $totalPaidRevenue = (float) $invoices->sum('total_amount');
            }
        }

        // 2. Pull Recorded Expenses from CashCore / InvoiceMaker
        $totalExpenses = 0.0;
        $breakdown = [
            'personnel' => 0.0,
            'rent' => 0.0,
            'insurance' => 0.0,
            'vehicles' => 0.0,
            'software' => 0.0,
            'telecom' => 0.0,
            'marketing' => 0.0,
            'financing' => 0.0,
            'other' => 0.0,
        ];

        if (Schema::hasTable('cashcore_expenses')) {
            $expenses = DB::table('cashcore_expenses')
                ->where('team_id', $teamId)
                ->where('created_at', '>=', now()->subYear())
                ->get();

            if ($expenses->isNotEmpty()) {
                $hasData = true;
                $totalExpenses = (float) $expenses->sum('amount');
                $breakdown['other'] = $totalExpenses;
            }
        }

        return [
            'has_data' => $hasData,
            'actual_revenue_annual' => round($totalPaidRevenue, 2),
            'actual_revenue_monthly' => round($totalPaidRevenue / 12, 2),
            'fixed_costs_annual' => round($totalExpenses, 2),
            'fixed_costs_breakdown' => $breakdown,
            'invoice_count' => $invoiceCount,
        ];
    }
}
