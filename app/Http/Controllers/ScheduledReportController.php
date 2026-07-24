<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ScheduledReport;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class ScheduledReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = ScheduledReport::where('team_id', $request->user()->current_team_id)
            ->orderBy('next_run_at')
            ->paginate(20);

        return view('scheduled-reports.index', compact('reports'));
    }

    public function create(Request $request)
    {
        $modules = Module::where('is_active', true)->orderBy('name')->get();

        return view('scheduled-reports.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateReport($request);
        $user = $request->user();

        $report = new ScheduledReport($validated);
        $report->user_id = $user->id;
        $report->team_id = $user->current_team_id;
        $report->calculateNextRun();
        $report->save();

        return redirect()->route('scheduled-reports.index')->with('success', __('Scheduled report created.'));
    }

    public function edit(Request $request, ScheduledReport $scheduledReport)
    {
        $this->authorizeReport($request, $scheduledReport);

        $modules = Module::where('is_active', true)->orderBy('name')->get();

        return view('scheduled-reports.edit', compact('scheduledReport', 'modules'));
    }

    public function update(Request $request, ScheduledReport $scheduledReport)
    {
        $this->authorizeReport($request, $scheduledReport);

        $validated = $this->validateReport($request);

        $scheduledReport->fill($validated);
        $scheduledReport->calculateNextRun();
        $scheduledReport->save();

        return redirect()->route('scheduled-reports.index')->with('success', __('Scheduled report updated.'));
    }

    public function destroy(Request $request, ScheduledReport $scheduledReport)
    {
        $this->authorizeReport($request, $scheduledReport);

        $scheduledReport->delete();

        return redirect()->route('scheduled-reports.index')->with('success', __('Scheduled report deleted.'));
    }

    protected function validateReport(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'module_key' => ['nullable', 'string', 'max:50'],
            'report_type' => ['required', Rule::in(['dashboard', 'module_summary'])],
            'frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'format' => ['required', Rule::in(['pdf', 'csv'])],
            'email' => ['required', 'email', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    protected function authorizeReport(Request $request, ScheduledReport $scheduledReport): void
    {
        abort_if($scheduledReport->team_id !== $request->user()->current_team_id, 403);
    }
}
