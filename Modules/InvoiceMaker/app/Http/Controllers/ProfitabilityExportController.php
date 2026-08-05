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
            $totalRevenue = Invoice::where('team_id', $business->team_id)
                ->whereBetween('invoice_date', [$startDate, $endDate])
                ->where('status', 'paid')
                ->sum('grand_total');

            $totalExpenses = Expense::where('team_id', $business->team_id)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');

            fwrite($handle, '<div class="title">'.__('Overall Financial Evaluation').' ('.$startDate.' - '.$endDate.')</div>');
            fwrite($handle, '<table><thead><tr><th>'.__('Metric').'</th><th>'.__('Value').' ('.$business->currency.')</th></tr></thead><tbody>');
            fwrite($handle, '<tr><td>'.__('Total Revenue').'</td><td class="amount positive" x:num="'.$totalRevenue.'">'.number_format($totalRevenue, 2).'</td></tr>');
            fwrite($handle, '<tr><td>'.__('Total Expenses').'</td><td class="amount negative" x:num="-'.$totalExpenses.'">-'.number_format($totalExpenses, 2).'</td></tr>');
            fwrite($handle, '<tr><td><strong>'.__('Net Profit').'</strong></td><td class="amount '.($totalRevenue >= $totalExpenses ? 'positive' : 'negative').'" x:num="'.($totalRevenue - $totalExpenses).'"><strong>'.number_format($totalRevenue - $totalExpenses, 2).'</strong></td></tr>');
            fwrite($handle, '</tbody></table>');

            // --- 2. TOP CUSTOMERS ---
            $clientData = Client::where('team_id', $business->team_id)
                ->with([
                    'invoices' => function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('invoice_date', [$startDate, $endDate])->where('status', 'paid');
                    },
                ])
                ->get()
                ->map(function ($client) use ($startDate, $endDate) {
                    $sales = $client->invoices->sum('grand_total');
                    $costs = Expense::where('client_id', $client->id)
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

            // --- 3. TOP PRODUCTS (Comprehensive) ---
            $productData = Product::where('team_id', $business->team_id)
                ->get()
                ->map(function ($product) use ($startDate, $endDate) {
                    // Replicate logic from Livewire component
                    $salesData = DB::table('invoicemaker_invoice_items')
                        ->join('invoicemaker_invoices', 'invoicemaker_invoice_items.invoice_id', '=', 'invoicemaker_invoices.id')
                        ->where('invoicemaker_invoice_items.product_id', $product->id)
                        ->where('invoicemaker_invoices.status', 'paid')
                        ->whereBetween('invoicemaker_invoices.invoice_date', [$startDate, $endDate])
                        ->select(
                            DB::raw('SUM(invoicemaker_invoice_items.quantity) as total_sold'),
                            DB::raw('SUM(invoicemaker_invoice_items.total) as total_revenue')
                        )
                        ->first();

                    $directExpenses = Expense::where('product_id', $product->id)
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

            // --- 4. TOP COSTS (EXPENSES GROUPED BY CATEGORY) ---
            $topCosts = Expense::where('team_id', $business->team_id)
                ->whereBetween('date', [$startDate, $endDate])
                ->with(['accounting_category'])
                ->get()
                ->groupBy(function ($item) {
                    return $item->accounting_category ? $item->accounting_category->name : __('Uncategorized');
                })
                ->map(function ($group) {
                    $collection = collect($group);

                    return [
                        'amount' => $collection->sum('amount'),
                        'count' => $collection->count(),
                    ];
                })
                ->sortByDesc('amount');

            fwrite($handle, '<div class="title">'.__('Expense Breakdown by Category').'</div>');
            fwrite($handle, '<table><thead><tr>
                <th>'.__('Category').'</th>
                <th>'.__('Transaction Count').'</th>
                <th>'.__('Total Cost').'</th>
            </tr></thead><tbody>');

            foreach ($topCosts as $category => $data) {
                $data = (array) $data;
                fwrite($handle, '<tr>');
                fwrite($handle, '<td>'.$category.'</td>');
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
