<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Module;
use App\Models\ToolSubscription;
use App\Models\UserDashboard;
use App\Support\DashboardWidgetRegistry;
use App\Support\ModuleStats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserDashboardController extends Controller
{
    public function index()
    {
        $dashboards = UserDashboard::where('user_id', Auth::id())
            ->orderBy('position')
            ->get();

        return view('user-dashboards.index', compact('dashboards'));
    }

    public function create()
    {
        $availableModules = Module::where('is_active', true)
            ->pluck('name', 'key')
            ->all();

        return view('user-dashboards.create', compact('availableModules'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateDashboard($request);

        $validated['user_id'] = Auth::id();
        $validated['team_id'] = Auth::user()->current_team_id;

        if (! empty($validated['is_default'])) {
            UserDashboard::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        UserDashboard::create($validated);

        return redirect()->route('dashboards.index')->with('success', __('Dashboard created.'));
    }

    public function show(UserDashboard $dashboard)
    {
        $this->authorizeDashboard($dashboard);

        return view('user-dashboards.show', $this->dashboardData($dashboard));
    }

    public function edit(UserDashboard $dashboard)
    {
        $this->authorizeDashboard($dashboard);

        $availableModules = Module::where('is_active', true)
            ->pluck('name', 'key')
            ->all();

        return view('user-dashboards.edit', compact('dashboard', 'availableModules'));
    }

    public function update(Request $request, UserDashboard $dashboard)
    {
        $this->authorizeDashboard($dashboard);

        $validated = $this->validateDashboard($request);

        if (! empty($validated['is_default'])) {
            UserDashboard::where('user_id', Auth::id())->where('id', '!=', $dashboard->id)->update(['is_default' => false]);
        }

        $dashboard->update($validated);

        return redirect()->route('dashboards.index')->with('success', __('Dashboard updated.'));
    }

    public function destroy(UserDashboard $dashboard)
    {
        $this->authorizeDashboard($dashboard);

        $dashboard->delete();

        return redirect()->route('dashboards.index')->with('success', __('Dashboard deleted.'));
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        foreach ($validated['order'] as $position => $id) {
            UserDashboard::where('id', $id)->where('user_id', Auth::id())->update(['position' => $position]);
        }

        return response()->json(['ok' => true]);
    }

    protected function validateDashboard(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'widgets' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $validated['widgets'] = json_decode($validated['widgets'] ?? '[]', true) ?: [];

        return $validated;
    }

    protected function dashboardData(UserDashboard $dashboard): array
    {
        $user = Auth::user();
        $modules = Module::where('is_active', true)->get();
        $accessible = $user->accessibleModules()->pluck('key')->all();
        $moduleWidgets = app(DashboardWidgetRegistry::class)->forUser($user);
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
        $chartModules = collect($moduleStats)->filter(fn ($v) => $v['accessible'])->values();

        return compact('dashboard', 'modules', 'accessible', 'moduleWidgets', 'announcements', 'activeModules', 'lockedModules', 'subscription', 'activityLogs', 'stats', 'moduleStats', 'chartModules');
    }

    protected function authorizeDashboard(UserDashboard $dashboard): void
    {
        abort_if($dashboard->user_id !== Auth::id(), 403);
    }
}
