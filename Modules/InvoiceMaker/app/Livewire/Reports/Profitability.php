<?php

namespace Modules\InvoiceMaker\Livewire\Reports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\InvoiceMaker\Models\CashBookEntry;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Models\Expense;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Product;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Profitability extends Component
{
    use WithPagination;

    public $search = '';

    public $period = '1M';

    public $startDate;

    public $endDate;

    public function mount()
    {
        $this->setPeriod('1M');
    }

    public function setPeriod(string $period)
    {
        $this->period = $period;

        switch ($period) {
            case '1M':
                $this->startDate = now()->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case '3M':
                $this->startDate = now()->subMonths(2)->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case '6M':
                $this->startDate = now()->subMonths(5)->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case '12M':
                $this->startDate = now()->subMonths(11)->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'custom':
            default:
                break;
        }

        $this->resetPage();
    }

    public function updated($property)
    {
        if (in_array($property, ['startDate', 'endDate'])) {
            $this->period = 'custom';
            $this->resetPage();
        }
    }

    public function render()
    {
        $business = app(InvoiceMakerContext::class)->profile();

        // 1. Overall Revenue (Accrual/Invoiced Basis + Cash Book Manual Income)
        $invoicedRevenue = Invoice::where('team_id', $business->team_id)
            ->whereBetween('invoice_date', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->sum('grand_total');

        $manualIncome = CashBookEntry::where('team_id', $business->team_id)
            ->where('type', 'income')
            ->whereNull('invoice_id')
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('amount');

        $totalRevenue = (float) $invoicedRevenue + (float) $manualIncome;

        // 2. Expenses & Cost Classification (Fixed vs Variable)
        $expenses = Expense::where('team_id', $business->team_id)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->with('category')
            ->get();

        $fixedExpensesTotal = 0.0;
        $variableExpensesTotal = 0.0;
        $costCategories = [];

        foreach ($expenses as $expense) {
            $amount = (float) $expense->amount;
            $isFixed = $expense->isFixedCost();

            if ($isFixed) {
                $fixedExpensesTotal += $amount;
            } else {
                $variableExpensesTotal += $amount;
            }

            $catName = $expense->category?->name ?? ($expense->category ?? __('Other / General'));
            if (!isset($costCategories[$catName])) {
                $costCategories[$catName] = [
                    'name' => $catName,
                    'total' => 0.0,
                    'is_fixed' => $isFixed,
                    'count' => 0,
                ];
            }
            $costCategories[$catName]['total'] += $amount;
            $costCategories[$catName]['count']++;
        }

        uasort($costCategories, fn ($a, $b) => $b['total'] <=> $a['total']);

        // 3. Customer Profitability (Invoices vs Linked Expenses)
        $clientProfitability = Client::where('team_id', $business->team_id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('company_name', 'like', '%'.$this->search.'%');
                });
            })
            ->with([
                'invoices' => function ($query) {
                    $query->whereBetween('invoice_date', [$this->startDate, $this->endDate])
                        ->whereNotIn('status', ['draft', 'cancelled']);
                },
            ])
            ->get()
            ->map(function ($client) {
                $sales = (float) $client->invoices->sum('grand_total');
                $directCosts = (float) Expense::where('client_id', $client->id)
                    ->whereBetween('date', [$this->startDate, $this->endDate])
                    ->sum('amount');

                return [
                    'id' => $client->id,
                    'name' => $client->company_name ?? $client->name,
                    'sales' => $sales,
                    'costs' => $directCosts,
                    'difference' => $sales - $directCosts,
                    'margin' => $sales > 0 ? (($sales - $directCosts) / $sales) * 100 : ($directCosts > 0 ? -100 : 0),
                ];
            })
            ->filter(fn ($item) => $item['sales'] > 0 || $item['costs'] > 0)
            ->sortByDesc('difference');

        // 4. Product Profitability (Price vs Purchase Price COGS)
        $productProfitability = Product::where('team_id', $business->team_id)
            ->get()
            ->map(function ($product) {
                $salesData = DB::table('invoicemaker_invoice_items')
                    ->join('invoicemaker_invoices', 'invoicemaker_invoice_items.invoice_id', '=', 'invoicemaker_invoices.id')
                    ->where('invoicemaker_invoice_items.product_id', $product->id)
                    ->whereNotIn('invoicemaker_invoices.status', ['draft', 'cancelled'])
                    ->whereBetween('invoicemaker_invoices.invoice_date', [$this->startDate, $this->endDate])
                    ->select(
                        DB::raw('SUM(invoicemaker_invoice_items.quantity) as total_sold'),
                        DB::raw('SUM(invoicemaker_invoice_items.total) as total_revenue')
                    )
                    ->first();

                $productDirectExpenses = (float) Expense::where('product_id', $product->id)
                    ->whereBetween('date', [$this->startDate, $this->endDate])
                    ->sum('amount');

                $totalSold = (float) ($salesData->total_sold ?? 0);
                $totalRevenue = (float) ($salesData->total_revenue ?? 0);
                $purchaseCost = $totalSold * (float) ($product->purchase_price ?? 0);
                $totalCosts = (float) ($purchaseCost + $productDirectExpenses);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sold' => $totalSold,
                    'sales' => $totalRevenue,
                    'costs' => $totalCosts,
                    'difference' => $totalRevenue - $totalCosts,
                    'margin' => $totalRevenue > 0 ? (($totalRevenue - $totalCosts) / $totalRevenue) * 100 : ($totalCosts > 0 ? -100 : 0),
                ];
            })
            ->filter(fn ($item) => $item['sales'] > 0 || $item['costs'] > 0);

        // Capture one-off / uncategorized line item sales
        $uncategorizedSales = (float) DB::table('invoicemaker_invoice_items')
            ->join('invoicemaker_invoices', 'invoicemaker_invoice_items.invoice_id', '=', 'invoicemaker_invoices.id')
            ->where('invoicemaker_invoices.team_id', $business->team_id)
            ->whereNull('invoicemaker_invoice_items.product_id')
            ->whereNotIn('invoicemaker_invoices.status', ['draft', 'cancelled'])
            ->whereBetween('invoicemaker_invoices.invoice_date', [$this->startDate, $this->endDate])
            ->sum('invoicemaker_invoice_items.total');

        if ($uncategorizedSales > 0) {
            $productProfitability->push([
                'id' => null,
                'name' => __('Other / Custom Line Items'),
                'sold' => 0,
                'sales' => $uncategorizedSales,
                'costs' => 0,
                'difference' => $uncategorizedSales,
                'margin' => 100,
            ]);
        }

        $productProfitability = $productProfitability->sortByDesc('difference');

        // Total Cost Aggregation
        $totalFixedCosts = $fixedExpensesTotal;
        $totalVariableCosts = $variableExpensesTotal;
        $totalOperationalCosts = $totalFixedCosts + $totalVariableCosts;
        $netIncome = $totalRevenue - $totalOperationalCosts;

        // 5. Monthly Averages & Absolute Minimum Revenue Benchmark
        $startDateObj = Carbon::parse($this->startDate);
        $endDateObj = Carbon::parse($this->endDate);
        $periodDays = max(1, $startDateObj->diffInDays($endDateObj) + 1);
        $periodMonths = max(0.5, round($periodDays / 30.4375, 2));

        $monthlyAvgFixed = $totalFixedCosts / $periodMonths;
        $monthlyAvgVariable = $totalVariableCosts / $periodMonths;
        $monthlyAvgTotalCosts = $totalOperationalCosts / $periodMonths;
        $monthlyAvgRevenue = $totalRevenue / $periodMonths;

        // Absolute Minimum Monthly Revenue is the break-even run rate
        $absoluteMinMonthlyRevenue = $monthlyAvgTotalCosts;

        // 6. Real-Time Break-Even & Trend Gap Analysis KPI
        $revenueGap = $totalRevenue - $totalOperationalCosts;
        $isBreakEvenMet = $revenueGap >= 0;
        $costCoveragePercent = $totalOperationalCosts > 0 ? round(($totalRevenue / $totalOperationalCosts) * 100, 1) : ($totalRevenue > 0 ? 100.0 : 0.0);

        // Trend Velocity / Run-Rate Projection
        $today = now();
        if ($today->between($startDateObj, $endDateObj)) {
            $daysElapsed = max(1, $startDateObj->diffInDays($today) + 1);
            $projectedRevenue = ($totalRevenue / $daysElapsed) * $periodDays;
            $projectedCoverage = $totalOperationalCosts > 0 ? round(($projectedRevenue / $totalOperationalCosts) * 100, 1) : 100.0;
            $projectedGap = $projectedRevenue - $totalOperationalCosts;
        } else {
            $projectedRevenue = $totalRevenue;
            $projectedCoverage = $costCoveragePercent;
            $projectedGap = $revenueGap;
        }

        // Top Performers
        $topClients = $clientProfitability->take(3);
        $topProducts = $productProfitability->take(3);

        return view('invoicemaker::livewire.reports.profitability', [
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalOperationalCosts,
            'totalFixedCosts' => $totalFixedCosts,
            'totalVariableCosts' => $totalVariableCosts,
            'netIncome' => $netIncome,
            'periodMonths' => $periodMonths,
            'periodDays' => $periodDays,
            'monthlyAvgFixed' => $monthlyAvgFixed,
            'monthlyAvgVariable' => $monthlyAvgVariable,
            'monthlyAvgTotalCosts' => $monthlyAvgTotalCosts,
            'monthlyAvgRevenue' => $monthlyAvgRevenue,
            'absoluteMinMonthlyRevenue' => $absoluteMinMonthlyRevenue,
            'revenueGap' => $revenueGap,
            'isBreakEvenMet' => $isBreakEvenMet,
            'costCoveragePercent' => $costCoveragePercent,
            'projectedRevenue' => $projectedRevenue,
            'projectedCoverage' => $projectedCoverage,
            'projectedGap' => $projectedGap,
            'costCategories' => $costCategories,
            'clientProfitability' => $clientProfitability,
            'productProfitability' => $productProfitability,
            'topClients' => $topClients,
            'topProducts' => $topProducts,
        ]);
    }
}
