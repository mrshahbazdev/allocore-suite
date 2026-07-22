<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Module;
use App\Models\ToolSubscription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class DashboardExportController extends Controller
{
    public function pdf(Request $request)
    {
        $user = $request->user();
        $modules = Module::where('is_active', true)->get();
        $accessible = $user->accessibleModules()->pluck('key')->all();
        $activeModules = $modules->filter(fn ($m) => in_array($m->key, $accessible))->values();
        $lockedModules = $modules->filter(fn ($m) => ! in_array($m->key, $accessible))->values();
        $announcements = Announcement::active()->latest()->take(3)->get();

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
            'workspace_members' => DB::table('team_user')->where('team_id', $user->current_team_id)->count(),
        ];

        $pdf = Pdf::loadView('exports.dashboard', compact('user', 'activeModules', 'lockedModules', 'subscription', 'activityLogs', 'stats', 'announcements'));

        return $pdf->download('dashboard-'.now()->format('Y-m-d').'.pdf');
    }
}
