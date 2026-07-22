<?php

namespace Modules\KpiTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\KpiTool\Models\KpiDefinition;
use Modules\KpiTool\Models\KpiValue;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KpiSpreadsheetController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $definitions = KpiDefinition::query()
            ->where('is_active', true)
            ->where('is_template', false)
            ->get();

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $values = KpiValue::query()
            ->whereBetween('recorded_at', [$start, $end])
            ->get()
            ->keyBy(fn ($value) => $value->kpi_definition_id.'-'.$value->recorded_at->format('Y-m-d'));

        $days = collect(range(1, $start->daysInMonth))->map(fn ($day) => Carbon::create($year, $month, $day)->format('Y-m-d'));

        return view('kpitool::spreadsheet.index', compact('definitions', 'values', 'year', 'month', 'days'));
    }

    public function export(Request $request): StreamedResponse
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $definitions = KpiDefinition::query()->where('is_active', true)->where('is_template', false)->get();

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $values = KpiValue::query()
            ->whereBetween('recorded_at', [$start, $end])
            ->with('kpiDefinition')
            ->get();

        $headers = ['Date', 'KPI', 'Value', 'Status', 'Notes'];

        $filename = "kpitool-{$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT).'.csv';

        return response()->streamDownload(function () use ($headers, $values): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($values as $value) {
                fputcsv($handle, [
                    $value->recorded_at->format('Y-m-d'),
                    $value->kpiDefinition->name,
                    $value->value,
                    $value->status,
                    $value->notes,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);

        $map = [
            'date' => array_search('Date', $headers, true),
            'kpi' => array_search('KPI', $headers, true),
            'value' => array_search('Value', $headers, true),
            'notes' => array_search('Notes', $headers, true),
        ];

        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $kpiName = $row[$map['kpi']] ?? null;
            $definition = KpiDefinition::query()
                ->where('is_template', false)
                ->where(function ($query) use ($kpiName): void {
                    $query->where('name_de', $kpiName)->orWhere('name_en', $kpiName);
                })
                ->first();

            if (! $definition) {
                continue;
            }

            $value = (float) ($row[$map['value']] ?? 0);

            $definition->values()->updateOrCreate(
                ['recorded_at' => $row[$map['date']]],
                [
                    'value' => $value,
                    'notes' => $row[$map['notes']] ?? null,
                    'status' => $definition->statusFor($value),
                ]
            );

            $imported++;
        }

        fclose($handle);

        return redirect()->route('kpitool.spreadsheet.index')->with('success', __('Imported :count rows.', ['count' => $imported]));
    }
}
