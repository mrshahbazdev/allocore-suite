<?php

namespace Modules\LoopEngine\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\LoopEngine\Models\ProcessRun;
use Modules\LoopEngine\Models\RunLog;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function runsCsv(): StreamedResponse
    {
        $runs = ProcessRun::with('process', 'starter')
            ->where('team_id', auth()->user()->current_team_id)
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($runs): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Process', 'User', 'Status', 'Loops', 'Started', 'Completed']);

            foreach ($runs as $run) {
                fputcsv($handle, [
                    $run->id,
                    $run->process->localizedName(),
                    $run->starter?->name,
                    $run->status,
                    $run->loop_count,
                    $run->started_at?->toDateTimeString(),
                    $run->completed_at?->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, 'loopengine-runs.csv', ['Content-Type' => 'text/csv']);
    }

    public function logsCsv(): StreamedResponse
    {
        $logs = RunLog::with('run', 'user')
            ->where('team_id', auth()->user()->current_team_id)
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($logs): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Run', 'User', 'Action', 'Details', 'Created']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->run?->id,
                    $log->user?->name,
                    $log->action,
                    json_encode($log->details),
                    $log->created_at?->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, 'loopengine-logs.csv', ['Content-Type' => 'text/csv']);
    }
}
