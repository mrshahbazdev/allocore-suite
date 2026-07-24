<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Module;
use App\Models\ScheduledReport;
use App\Models\ToolSubscription;
use App\Support\ModuleStats;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportGenerator
{
    protected function directory(): string
    {
        $dir = storage_path('app/reports');

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    public function generate(ScheduledReport $report): array
    {
        $report->load(['user', 'team']);

        if ($report->report_type === 'module_summary' && $report->module_key) {
            return $report->format === 'csv'
                ? $this->moduleCsv($report)
                : $this->moduleSummary($report);
        }

        return $report->format === 'csv'
            ? $this->dashboardCsv($report)
            : $this->dashboardSummary($report);
    }

    protected function dashboardSummary(ScheduledReport $report): array
    {
        $user = $report->user;
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

        return $this->saveFile($pdf->output(), 'dashboard-'.now()->format('Y-m-d').'.pdf');
    }

    protected function moduleSummary(ScheduledReport $report): array
    {
        $module = Module::where('key', $report->module_key)->where('is_active', true)->firstOrFail();
        $stats = app(ModuleStats::class)->forModule($report->user, $module);

        $pdf = Pdf::loadView('reports.module-pdf', [
            'module' => $module,
            'stats' => $stats,
            'generatedAt' => now(),
        ]);

        return $this->saveFile($pdf->output(), $module->key.'-'.now()->format('Y-m-d').'.pdf');
    }

    protected function dashboardCsv(ScheduledReport $report): array
    {
        $filename = 'dashboard-'.now()->format('Y-m-d').'.csv';
        $path = $this->directory().'/'.$filename;
        $modules = Module::where('is_active', true)->get();
        $stats = collect(app(ModuleStats::class)->forUser($report->user))->map(fn ($item, $key) => array_merge(['key' => $key], $item));

        $handle = fopen($path, 'w');
        fputcsv($handle, ['key', 'name', 'accessible', 'count', 'label']);
        foreach ($stats as $row) {
            fputcsv($handle, [$row['key'], $row['name'], $row['accessible'] ? 'yes' : 'no', $row['count'] ?? '', $row['label'] ?? '']);
        }
        fclose($handle);

        return ['path' => $path, 'filename' => $filename];
    }

    protected function moduleCsv(ScheduledReport $report): array
    {
        $module = Module::where('key', $report->module_key)->where('is_active', true)->firstOrFail();
        $modelClass = app(ModuleStats::class)->modelFor($module->key);

        if (! $modelClass || ! class_exists($modelClass)) {
            throw new \RuntimeException(__('Module has no exportable records.'));
        }

        $records = $modelClass::query()->limit(1000)->get();
        $filename = $module->key.'-'.now()->format('Y-m-d').'.csv';
        $path = $this->directory().'/'.$filename;

        $handle = fopen($path, 'w');

        if ($records->isNotEmpty()) {
            fputcsv($handle, array_keys($records->first()->toArray()));
            foreach ($records as $record) {
                fputcsv($handle, $record->toArray());
            }
        }

        fclose($handle);

        return ['path' => $path, 'filename' => $filename];
    }

    protected function saveFile(string $content, string $filename): array
    {
        $path = $this->directory().'/'.$filename;
        file_put_contents($path, $content);

        return ['path' => $path, 'filename' => $filename];
    }

    public function streamCsv(string $path, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($path) {
            echo file_get_contents($path);
        }, $filename);
    }
}
