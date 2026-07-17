<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Models\User;
use Carbon\Carbon;
use Modules\AuditPro\Models\Audit;
use Modules\FinancialPlatform\Models\Analysis;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Payment;

class AnalyticsController extends Controller
{
    public function index()
    {
        $period = request('period', 30);
        $start = Carbon::now()->subDays((int) $period)->startOfDay();
        $end = Carbon::now()->endOfDay();

        $stats = [
            'users_total' => User::count(),
            'users_new' => User::whereBetween('created_at', [$start, $end])->count(),
            'teams_total' => Team::count(),
            'teams_new' => Team::whereBetween('created_at', [$start, $end])->count(),
            'active_subscriptions' => ToolSubscription::where('status', 'active')->count(),
            'subscriptions_new' => ToolSubscription::whereBetween('created_at', [$start, $end])->count(),
            'audits_total' => Audit::withoutGlobalScope('current_team')->count(),
            'audits_new' => Audit::withoutGlobalScope('current_team')->whereBetween('created_at', [$start, $end])->count(),
            'analyses_total' => Analysis::withoutGlobalScope('current_team')->count(),
            'analyses_new' => Analysis::withoutGlobalScope('current_team')->whereBetween('created_at', [$start, $end])->count(),
            'invoices_total' => Invoice::withoutGlobalScope('current_team')->count(),
            'invoices_new' => Invoice::withoutGlobalScope('current_team')->whereBetween('created_at', [$start, $end])->count(),
            'revenue_total' => (float) Payment::withoutGlobalScope('current_team')->sum('amount'),
            'revenue_new' => (float) Payment::withoutGlobalScope('current_team')->whereBetween('created_at', [$start, $end])->sum('amount'),
        ];

        $userSignups = User::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $revenueByDay = Payment::withoutGlobalScope('current_team')
            ->selectRaw('DATE(date) as date, SUM(amount) as total')
            ->whereBetween('date', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $topTeams = Team::withCount('members')
            ->orderByDesc('members_count')
            ->limit(10)
            ->get();

        return view('admin.analytics.index', compact('stats', 'period', 'userSignups', 'revenueByDay', 'topTeams'));
    }
}
