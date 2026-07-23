<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Module;
use App\Models\ToolSubscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UsageAnalytics
{
    public function forUser(User $user): array
    {
        $teamId = $user->current_team_id;
        $start = now()->subDays(30)->startOfDay();
        $end = now()->endOfDay();
        $dates = collect(range(0, 30))->map(fn ($i) => $start->copy()->addDays($i)->format('Y-m-d'));

        $modules = Module::where('is_active', true)->get();
        $moduleStats = app(ModuleStats::class);

        $perModule = [];
        $datasets = [];

        foreach ($modules as $module) {
            if (! $user->hasModule($module->key)) {
                continue;
            }

            $modelClass = $moduleStats->modelFor($module->key);
            $label = $moduleStats->labelFor($module->key);

            if (! $modelClass || ! class_exists($modelClass)) {
                continue;
            }

            $model = new $modelClass;
            $table = $model->getTable();

            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'created_at')) {
                continue;
            }

            $query = DB::table($table)
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date');

            if (Schema::hasColumn($table, 'team_id')) {
                $query->where('team_id', $teamId);
            }

            $raw = $query->pluck('count', 'date')->toArray();

            $counts = $dates->map(fn ($date) => (int) ($raw[$date] ?? 0))->all();
            $total = array_sum($counts);

            if ($total === 0 && $moduleStats->forModule($user, $module)['primary_resource_count'] === 0) {
                continue;
            }

            $perModule[] = [
                'key' => $module->key,
                'name' => $module->name,
                'label' => $label,
                'total' => $total,
            ];

            $datasets[] = [
                'label' => $module->name,
                'data' => $counts,
            ];
        }

        $activityCounts = $this->dailyCounts(
            ActivityLog::query()
                ->when($teamId, fn ($q) => $q->where('team_id', $teamId)),
            $start,
            $end,
            $dates
        );

        $subscriptionCounts = $this->dailyCounts(
            ToolSubscription::query()
                ->where('billable_type', get_class($user))
                ->where('billable_id', $user->id),
            $start,
            $end,
            $dates
        );

        return [
            'dates' => $dates->values()->all(),
            'per_module' => collect($perModule)->sortByDesc('total')->values()->all(),
            'datasets' => $datasets,
            'activity' => $activityCounts,
            'subscriptions' => $subscriptionCounts,
            'totals' => [
                'records' => collect($perModule)->sum('total'),
                'activities' => array_sum($activityCounts),
                'subscriptions' => array_sum($subscriptionCounts),
            ],
        ];
    }

    protected function dailyCounts($query, Carbon $start, Carbon $end, $dates): array
    {
        $table = $query->getModel()->getTable();

        if (! Schema::hasColumn($table, 'created_at')) {
            return $dates->map(fn () => 0)->all();
        }

        $raw = $query
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return $dates->map(fn ($date) => (int) ($raw[$date] ?? 0))->all();
    }
}
