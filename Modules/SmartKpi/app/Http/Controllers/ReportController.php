<?php

namespace Modules\SmartKpi\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\SmartKpi\Models\KpiValue;
use Modules\SmartKpi\Models\Problem;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function kpiValuesCsv(): StreamedResponse
    {
        $values = KpiValue::with('kpiDefinition', 'recorder')
            ->where('team_id', auth()->user()->current_team_id)
            ->latest('recorded_at')
            ->get();

        return response()->streamDownload(function () use ($values): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['KPI', 'Value', 'Status', 'Recorded', 'Recorder']);

            foreach ($values as $value) {
                fputcsv($handle, [
                    $value->kpiDefinition?->localizedName(),
                    $value->value,
                    $value->status,
                    $value->recorded_at?->toDateString(),
                    $value->recorder?->name,
                ]);
            }

            fclose($handle);
        }, 'smartkpi-kpi-values.csv', ['Content-Type' => 'text/csv']);
    }

    public function problemsCsv(): StreamedResponse
    {
        $problems = Problem::with('kpiDefinition', 'company', 'department')
            ->where('team_id', auth()->user()->current_team_id)
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($problems): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Title', 'KPI', 'Severity', 'Status', 'Company', 'Department', 'Detected']);

            foreach ($problems as $problem) {
                fputcsv($handle, [
                    $problem->title,
                    $problem->kpiDefinition?->localizedName(),
                    $problem->severity,
                    $problem->status,
                    $problem->company?->name,
                    $problem->department?->name,
                    $problem->detected_at?->toDateString(),
                ]);
            }

            fclose($handle);
        }, 'smartkpi-problems.csv', ['Content-Type' => 'text/csv']);
    }
}
