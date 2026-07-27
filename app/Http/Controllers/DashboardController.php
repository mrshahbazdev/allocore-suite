<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Module;
use App\Models\ToolSubscription;
use App\Services\AllocoreRecommendationService;
use App\Services\AllocoreScoreService;
use App\Support\DashboardWidgetRegistry;
use App\Support\ModuleStats;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardWidgetRegistry $registry)
    {
        $user = $request->user();
        $modules = Module::where('is_active', true)->get();
        $accessible = $user->accessibleModules()->pluck('key')->all();
        $widgets = $registry->forUser($user);
        $announcements = Announcement::active()->latest()->take(3)->get();

        $activeModules = $modules->filter(fn ($m) => in_array($m->key, $accessible))->values();
        $lockedModules = $modules->filter(fn ($m) => ! in_array($m->key, $accessible))->values();

        $subscription = ToolSubscription::with('plan')
            ->where('billable_type', get_class($user))
            ->where('billable_id', $user->id)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->latest('starts_at')
            ->first();

        $activityLogs = ActivityLog::where('team_id', $user->current_team_id)
            ->orWhere('causer_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'active_modules' => $activeModules->count(),
            'locked_modules' => $lockedModules->count(),
            'total_modules' => $modules->count(),
            'recent_activities' => $activityLogs->count(),
            'workspace_members' => DB::table('team_user')->where('team_id', $user->current_team_id)->count(),
        ];

        $moduleStats = app(ModuleStats::class)->forUser($user);

        $allocoreScore = AllocoreScoreService::latestForTeam($user->current_team_id);
        $allocoreHistory = AllocoreScoreService::historyForTeam($user->current_team_id, 12);
        $allocoreRecommendations = app(AllocoreRecommendationService::class)->forScore($allocoreScore, $user);

        return view('dashboard', compact('modules', 'accessible', 'widgets', 'announcements', 'activeModules', 'lockedModules', 'subscription', 'activityLogs', 'stats', 'moduleStats', 'allocoreScore', 'allocoreHistory', 'allocoreRecommendations'));
    }
}
