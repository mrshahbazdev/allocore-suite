<?php

namespace Modules\FinancialPlatform\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FinancialPlatform\Jobs\SendKpiReportJob;
use Modules\FinancialPlatform\Models\KpiSchedule;

class KpiScheduleController
{
    public function index(): View
    {
        $schedules = KpiSchedule::latest()->paginate(20);

        return view('financialplatform::kpi-schedules.index', compact('schedules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'frequency' => ['required', 'in:daily,weekly,monthly'],
            'recipients' => ['required', 'string'],
        ]);

        $recipients = array_filter(array_map('trim', explode(',', $validated['recipients'])));

        if (empty($recipients)) {
            return back()->with('error', __('Enter at least one email address.'));
        }

        KpiSchedule::create([
            'user_id' => auth()->id(),
            'frequency' => $validated['frequency'],
            'recipients' => $recipients,
            'next_run_at' => now()->addDay(),
        ]);

        return redirect()->route('financial.kpi-schedules.index')->with('success', __('KPI report schedule created.'));
    }

    public function runNow(KpiSchedule $schedule): RedirectResponse
    {
        SendKpiReportJob::dispatch($schedule);

        return redirect()->route('financial.kpi-schedules.index')->with('success', __('KPI report dispatched.'));
    }

    public function destroy(KpiSchedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()->route('financial.kpi-schedules.index')->with('success', __('KPI report schedule deleted.'));
    }
}
