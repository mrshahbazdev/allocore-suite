<?php

namespace Modules\FinancialPlatform\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Modules\FinancialPlatform\Emails\KpiReportMail;
use Modules\FinancialPlatform\Models\KpiResult;
use Modules\FinancialPlatform\Models\KpiSchedule;

class SendKpiReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public KpiSchedule $schedule) {}

    public function handle(): void
    {
        $team = $this->schedule->team;

        $latestResults = KpiResult::where('team_id', $this->schedule->team_id)
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('financial_kpi_results')
                    ->whereColumn('team_id', 'financial_kpi_results.team_id')
                    ->whereColumn('kpi_code', 'financial_kpi_results.kpi_code')
                    ->groupBy('kpi_code');
            })
            ->get();

        $summary = $latestResults->map(fn (KpiResult $result) => [
            'name' => $result->kpi_name,
            'value' => $result->value.' '.$result->unit,
            'score' => $result->score,
            'status' => $result->trafficLightEmoji(),
        ])->all();

        foreach ($this->schedule->recipients as $recipient) {
            Mail::to($recipient)->send(new KpiReportMail(
                $summary,
                now()->translatedFormat('F Y'),
                $team?->name ?? __('Your team')
            ));
        }

        $this->schedule->update([
            'last_run_at' => now(),
            'next_run_at' => $this->schedule->calculateNextRun(),
        ]);
    }
}
