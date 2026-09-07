<?php

namespace Modules\InvoiceMaker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Models\Expense;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Product;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfitabilityExportController
{
    public function exportExcel(Request $request)
    {
        $business = app(InvoiceMakerContext::class)->profile();
        $startDate = $request->get('startDate', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('endDate', now()->endOfMonth()->format('Y-m-d'));

        $fileName = 'Profitability_Report_'.str_replace(' ', '_', $business->name).'_'.$startDate.'_to_'.$endDate.'.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        return new StreamedResponse(function () use ($business, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            // Excel HTML Header
            fwrite($handle, '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">');
            fwrite($handle, '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">');
            fwrite($handle, '<style>
                table { border-collapse: collapse; margin-bottom: 20px; }
                th { background-color: #1e293b; color: white; border: 1px solid #0f172a; font-weight: bold; padding: 10px; }
                td { border: 1px solid #e2e8f0; padding: 8px; vertical-align: top; }
                .amount { text-align: right; font-family: monospace; mso-number-format: "Standard"; }
                .percent { text-align: right; mso-number-format: "0.0%"; }
                .title { font-size: 18px; font-weight: bold; margin: 20px 0 10px 0; color: #1e293b; }
                .positive { color: #15803d; }
                .negative { color: #b91c1c; }
            </style></head><body>');

            // --- 1. OVERALL EVALUATION ---
            $invoicedRevenue = Invoice::where('team_id', $business->team_id)
                ->whereBetween('invoice_date', [$startDate, $endDate])
                ->whereNotIn('status', ['draft', 'cancelled'])
                ->sum('grand_total');

            $manualIncome = \Modules\InvoiceMaker\Models\CashBookEntry::where('team_id', $business->team_id)
                ->where('type', 'income')
                ->whereNull('invoice_id')
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');

            $totalRevenue = (float) $invoicedRevenue + (float) $manualIncome;

            $expenses = Expense::where('team_id', $business->team_id)
                ->whereBetween('date', [$startDate, $endDate])
                ->with('category')
                ->get();

            $totalFixedCosts = 0.0;
            $totalVariableCosts = 0.0;
            $topCosts = [];

            foreach ($expenses as $expense) {
                $amount = (float) $expense->amount;
                $isFixed = $expense->isFixedCost();

                if ($isFixed) {
                    $totalFixedCosts += $amount;
                } else {
                    $totalVariableCosts += $amount;
                }

                $catName = $expense->category?->name ?? ($expense->category ?? __('Uncategorized'));
                if (!isset($topCosts[$catName])) {
                    $topCosts[$catName] = [
                        'amount' => 0.0,
                        'count' => 0,
                        'type' => $isFixed ? __('Fixed Cost') : __('Variable Cost'),
                    ];
                }
                $topCosts[$catName]['amount'] += $amount;
                $topCosts[$catName]['count']++;
            }

            $totalExpenses = $totalFixedCosts + $totalVariableCosts;
            $netProfit = $totalRevenue - $totalExpenses;

            // Monthly Benchmarks & Break-Even
            $startCarbon = \Carbon\Carbon::parse($startDate);
            $endCarbon = \Carbon\Carbon::parse($endDate);
            $periodDays = max(1, $startCarbon->diffInDays($endCarbon) + 1);
            $periodMonths = max(0.5, round($periodDays / 30.4375, 2));

            $monthlyAvgFixed = $totalFixedCosts / $periodMonths;
            $monthlyAvgVariable = $totalVariableCosts / $periodMonths;
            $absoluteMinMonthlyRevenue = $totalExpenses / $periodMonths;
            $costCoveragePercent = $totalExpenses > 0 ? ($totalRevenue / $totalExpenses) * 100 : ($totalRevenue > 0 ? 100.0 : 0.0);
            $revenueGap = $totalRevenue - $totalExpenses;

            fwrite($handle, '<div class="title">'.__('Overall Financial Evaluation').' ('.$startDate.' - '.$endDate.')</div>');
            fwrite($handle, '<table><thead><tr><th>'.__('Metric').'</th><th>'.__('Value').' ('.$business->currency.')</th></tr></thead><tbody>');
            fwrite($handle, '<tr><td>'.__('Total Revenue').'</td><td class="amount positive" x:num="'.$totalRevenue.'">'.number_format($totalRevenue, 2).'</td></tr>');
            fwrite($handle, '<tr><td>'.__('Fixed Costs').'</td><td class="amount negative" x:num="-'.$totalFixedCosts.'">-'.number_format($totalFixedCosts, 2).'</td></tr>');
            fwrite($handle, '<tr><td>'.__('Variable Costs').'</td><td class="amount negative" x:num="-'.$totalVariableCosts.'">-'.number_format($totalVariableCosts, 2).'</td></tr>');
            fwrite($handle, '<tr><td><strong>'.__('Total Operational Costs').'</strong></td><td class="amount negative" x:num="-'.$totalExpenses.'"><strong>-'.number_format($totalExpenses, 2).'</strong></td></tr>');
            fwrite($handle, '<tr><td><strong>'.__('Net Profit').'</strong></td><td class="amount '.($netProfit >= 0 ? 'positive' : 'negative').'" x:num="'.$netProfit.'"><strong>'.number_format($netProfit, 2).'</strong></td></tr>');
            fwrite($handle, '</tbody></table>');

            // --- 2. BENCHMARKS & BREAK-EVEN ANALYSIS ---
            fwrite($handle, '<div class="title">'.__('Cost Benchmarks & Break-Even KPIs').' ('.$periodMonths.' '.__('Months').')</div>');
            fwrite($handle, '<table><thead><tr><th>'.__('Benchmark Metric').'</th><th>'.__('Value').'</th><th>'.__('Notes').'</th></tr></thead><tbody>');
            fwrite($handle, '<tr><td>'.__('Monthly Fixed Costs Avg').'</td><td class="amount" x:num="'.$monthlyAvgFixed.'">'.number_format($monthlyAvgFixed, 2).' '.$business->currency.'</td><td>'.__('Essential recurring overhead').'</td></tr>');
            fwrite($handle, '<tr><td>'.__('Monthly Variable Costs Avg').'</td><td class="amount" x:num="'.$monthlyAvgVariable.'">'.number_format($monthlyAvgVariable, 2).' '.$business->currency.'</td><td>'.__('Activity-driven spending').'</td></tr>');
            fwrite($handle, '<tr><td><strong>'.__('Absolute Minimum Monthly Revenue').'</strong></td><td class="amount '.($totalRevenue >= $totalExpenses ? 'positive' : 'negative').'" x:num="'.$absoluteMinMonthlyRevenue.'"><strong>'.number_format($absoluteMinMonthlyRevenue, 2).' '.$business->currency.'</strong></td><td><strong>'.__('Monthly Break-Even baseline threshold').'</strong></td></tr>');
            fwrite($handle, '<tr><td>'.__('Cost Coverage Ratio').'</td><td class="percent" x:num="'.($costCoveragePercent / 100).'">'.number_format($costCoveragePercent, 1).'%</td><td>'.($costCoveragePercent >= 100 ? __('Covering 100%+ of expenses') : __('Deficit - requires additional revenue')).'</td></tr>');
            fwrite($handle, '<tr><td>'.__('Break-Even Gap').'</td><td class="amount '.($revenueGap >= 0 ? 'positive' : 'negative').'" x:num="'.$revenueGap.'">'.number_format($revenueGap, 2).' '.$business->currency.'</td><td>'.($revenueGap >= 0 ? __('Operating Surplus') : __('Operating Shortfall')).'</td></tr>');
            fwrite($handle, '</tbody></table>');

            // --- 3. TOP CUSTOMERS ---
            $clientData = Client::where('team_id', $business->team_id)
                ->with([
                    'invoices' => function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('invoice_date', [$startDate, $endDate])
                            ->whereNotIn('status', ['draft', 'cancelled']);
                    },
                ])
                ->get()
                ->map(function ($client) use ($startDate, $endDate) {
                    $sales = (float) $client->invoices->sum('grand_total');
                    $costs = (float) Expense::where('client_id', $client->id)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->sum('amount');

                    return [
                        'name' => $client->company_name ?? $client->name,
                        'revenue' => (float) $sales,
                        'costs' => (float) $costs,
                        'profit' => (float) ($sales - $costs),
                        'margin' => $sales > 0 ? (($sales - $costs) / $sales) : ($costs > 0 ? -1.0 : 0),
                    ];
                })
                ->filter(fn ($c) => $c['revenue'] > 0 || $c['costs'] > 0)
                ->sortByDesc('profit');

            fwrite($handle, '<div class="title">'.__('Customer Profitability Analysis').'</div>');
            fwrite($handle, '<table><thead><tr>
                <th>'.__('Customer Name').'</th>
                <th>'.__('Revenue').'</th>
                <th>'.__('Costs').'</th>
                <th>'.__('Net Profit').'</th>
                <th>'.__('Margin').'</th>
            </tr></thead><tbody>');

            foreach ($clientData as $c) {
                fwrite($handle, '<tr>');
                fwrite($handle, '<td>'.$c['name'].'</td>');
                fwrite($handle, '<td class="amount" x:num="'.$c['revenue'].'">'.number_format($c['revenue'], 2).'</td>');
                fwrite($handle, '<td class="amount negative" x:num="-'.$c['costs'].'">-'.number_format($c['costs'], 2).'</td>');
                fwrite($handle, '<td class="amount '.($c['profit'] >= 0 ? 'positive' : 'negative').'" x:num="'.$c['profit'].'">'.number_format($c['profit'], 2).'</td>');
                fwrite($handle, '<td class="percent" x:num="'.$c['margin'].'">'.number_format($c['margin'] * 100, 1).'%</td>');
                fwrite($handle, '</tr>');
            }
            fwrite($handle, '</tbody></table>');

            // --- 4. PRODUCT MARGIN ANALYSIS ---
            $productData = Product::where('team_id', $business->team_id)
                ->get()
                ->map(function ($product) use ($startDate, $endDate) {
                    $salesData = DB::table('invoicemaker_invoice_items')
                        ->join('invoicemaker_invoices', 'invoicemaker_invoice_items.invoice_id', '=', 'invoicemaker_invoices.id')
                        ->where('invoicemaker_invoice_items.product_id', $product->id)
                        ->whereNotIn('invoicemaker_invoices.status', ['draft', 'cancelled'])
                        ->whereBetween('invoicemaker_invoices.invoice_date', [$startDate, $endDate])
                        ->select(
                            DB::raw('SUM(invoicemaker_invoice_items.quantity) as total_sold'),
                            DB::raw('SUM(invoicemaker_invoice_items.total) as total_revenue')
                        )
                        ->first();

                    $directExpenses = (float) Expense::where('product_id', $product->id)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->sum('amount');

                    $totalSold = (float) ($salesData->total_sold ?? 0);
                    $totalRevenue = (float) ($salesData->total_revenue ?? 0);
                    $purchaseCost = $totalSold * (float) ($product->purchase_price ?? 0);
                    $totalCost = (float) ($purchaseCost + $directExpenses);
                    $profit = $totalRevenue - $totalCost;

                    return [
                        'name' => $product->name,
                        'sold' => $totalSold,
                        'revenue' => $totalRevenue,
                        'costs' => $totalCost,
                        'profit' => (float) $profit,
                        'margin' => $totalRevenue > 0 ? ($profit / $totalRevenue) : ($totalCost > 0 ? -1.0 : 0),
                    ];
                })
                ->filter(fn ($p) => $p['revenue'] > 0 || $p['costs'] > 0)
                ->sortByDesc('profit');

            fwrite($handle, '<div class="title">'.__('Product Margin Analysis').'</div>');
            fwrite($handle, '<table><thead><tr>
                <th>'.__('Product Name').'</th>
                <th>'.__('Units Sold').'</th>
                <th>'.__('Revenue').'</th>
                <th>'.__('Total Costs').'</th>
                <th>'.__('Net Profit').'</th>
                <th>'.__('Margin').'</th>
            </tr></thead><tbody>');

            foreach ($productData as $p) {
                $p = (array) $p;
                fwrite($handle, '<tr>');
                fwrite($handle, '<td>'.$p['name'].'</td>');
                fwrite($handle, '<td>'.$p['sold'].'</td>');
                fwrite($handle, '<td class="amount" x:num="'.$p['revenue'].'">'.number_format($p['revenue'], 2).'</td>');
                fwrite($handle, '<td class="amount negative" x:num="-'.$p['costs'].'">-'.number_format($p['costs'], 2).'</td>');
                fwrite($handle, '<td class="amount '.($p['profit'] >= 0 ? 'positive' : 'negative').'" x:num="'.$p['profit'].'">'.number_format($p['profit'], 2).'</td>');
                fwrite($handle, '<td class="percent" x:num="'.$p['margin'].'">'.number_format($p['margin'] * 100, 1).'%</td>');
                fwrite($handle, '</tr>');
            }
            fwrite($handle, '</tbody></table>');

            // --- 5. EXPENSE BREAKDOWN BY CATEGORY ---
            uasort($topCosts, fn ($a, $b) => $b['amount'] <=> $a['amount']);

            fwrite($handle, '<div class="title">'.__('Expense Breakdown by Category & Cost Classification').'</div>');
            fwrite($handle, '<table><thead><tr>
                <th>'.__('Category').'</th>
                <th>'.__('Classification').'</th>
                <th>'.__('Transaction Count').'</th>
                <th>'.__('Total Cost').'</th>
            </tr></thead><tbody>');

            foreach ($topCosts as $category => $data) {
                $data = (array) $data;
                fwrite($handle, '<tr>');
                fwrite($handle, '<td>'.$category.'</td>');
                fwrite($handle, '<td>'.$data['type'].'</td>');
                fwrite($handle, '<td>'.$data['count'].'</td>');
                fwrite($handle, '<td class="amount negative" x:num="-'.$data['amount'].'">-'.number_format($data['amount'], 2).'</td>');
                fwrite($handle, '</tr>');
            }
            fwrite($handle, '</tbody></table>');

            fwrite($handle, '</body></html>');
            fclose($handle);
        }, 200, $headers);
    }
}
