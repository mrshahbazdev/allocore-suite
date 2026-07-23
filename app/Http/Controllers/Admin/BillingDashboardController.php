<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ToolSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Payment;

class BillingDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $invoiceQuery = Invoice::withoutGlobalScope('current_team');
        $paymentQuery = Payment::withoutGlobalScope('current_team');

        $totalRevenue = (float) $paymentQuery->sum('amount');
        $outstanding = (float) $invoiceQuery
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->sum('amount_due');

        $overdue = $invoiceQuery->clone()
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->count();

        $overdueAmount = (float) $invoiceQuery->clone()
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->sum('amount_due');

        $start = now()->subMonths(11)->startOfMonth();
        $end = now()->endOfMonth();
        $months = collect(range(0, 11))->map(fn ($i) => $start->copy()->addMonths($i)->format('Y-m'));

        $dateFormat = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', date)"
            : "DATE_FORMAT(date, '%Y-%m')";

        $monthlyRevenue = $paymentQuery->clone()
            ->whereBetween('date', [$start, $end])
            ->selectRaw("{$dateFormat} as month, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $revenueSeries = $months->map(fn ($m) => (float) ($monthlyRevenue[$m] ?? 0))->all();

        $statusCounts = $invoiceQuery->clone()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $recentInvoices = $invoiceQuery->clone()->latest()->limit(10)->get();
        $recentPayments = $paymentQuery->clone()->latest()->limit(10)->get();

        $subscriptions = ToolSubscription::with(['billable', 'plan'])
            ->latest()
            ->limit(10)
            ->get();

        $mrr = (float) ToolSubscription::where('status', 'active')
            ->where('billing_interval', 'monthly')
            ->sum('total');

        $arr = $mrr * 12;

        return view('admin.billing.dashboard', compact(
            'totalRevenue',
            'outstanding',
            'overdue',
            'overdueAmount',
            'months',
            'revenueSeries',
            'statusCounts',
            'recentInvoices',
            'recentPayments',
            'subscriptions',
            'mrr',
            'arr'
        ));
    }
}
