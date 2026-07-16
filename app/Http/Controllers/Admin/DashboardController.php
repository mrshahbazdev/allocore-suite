<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Plan;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Models\User;
use Modules\AuditPro\Models\Audit;
use Modules\FinancialPlatform\Models\Analysis;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'users' => User::count(),
            'admins' => User::whereHas('roles', fn ($query) => $query->where('name', 'admin'))->count(),
            'teams' => Team::count(),
            'modules' => Module::where('is_active', true)->count(),
            'plans' => Plan::count(),
            'subscriptions' => ToolSubscription::count(),
            'pending_bank' => ToolSubscription::where('payment_method', 'bank')->where('status', 'pending')->count(),
            'analyses' => Analysis::withoutGlobalScope('current_team')->count(),
            'audits' => Audit::withoutGlobalScope('current_team')->count(),
        ];

        $recentUsers = User::with('currentTeam')->latest()->limit(8)->get();
        $recentSubscriptions = ToolSubscription::with(['plan', 'billable'])->latest()->limit(8)->get();
        $recentAnalyses = Analysis::withoutGlobalScope('current_team')->with(['company', 'team'])->latest()->limit(8)->get();
        $recentAudits = Audit::withoutGlobalScope('current_team')->with(['template', 'team'])->latest()->limit(8)->get();

        return view('admin.index', compact('stats', 'recentUsers', 'recentSubscriptions', 'recentAnalyses', 'recentAudits'));
    }
}
