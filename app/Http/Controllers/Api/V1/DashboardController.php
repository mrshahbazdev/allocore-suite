<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Module;
use App\Models\ToolSubscription;
use App\Support\DashboardWidgetRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardWidgetRegistry $registry): JsonResponse
    {
        $user = $request->user();
        $modules = Module::where('is_active', true)->get();
        $accessible = $user->accessibleModules()->pluck('key')->all();

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

        $widgets = $registry->forUser($user);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'current_team_id' => $user->current_team_id,
            ],
            'stats' => [
                'active_modules' => $activeModules->count(),
                'locked_modules' => $lockedModules->count(),
                'total_modules' => $modules->count(),
                'workspace_members' => DB::table('team_user')->where('team_id', $user->current_team_id)->count(),
                'recent_activities' => $activityLogs->count(),
            ],
            'subscription' => $subscription ? [
                'plan' => $subscription->plan?->name,
                'status' => $subscription->status,
                'ends_at' => $subscription->ends_at?->toIso8601String(),
                'total' => $subscription->total,
            ] : null,
            'active_modules' => $activeModules->map(fn ($m) => ['key' => $m->key, 'name' => $m->name, 'route_prefix' => $m->route_prefix]),
            'locked_modules' => $lockedModules->map(fn ($m) => ['key' => $m->key, 'name' => $m->name, 'route_prefix' => $m->route_prefix]),
            'recent_activity' => $activityLogs->map(fn ($log) => [
                'description' => $log->description,
                'created_at' => $log->created_at->toIso8601String(),
            ]),
            'announcements' => Announcement::active()->latest()->take(3)->get()->map(fn ($a) => [
                'title' => $a->title,
                'body' => $a->body,
            ]),
            'widgets' => array_keys($widgets),
        ]);
    }
}
