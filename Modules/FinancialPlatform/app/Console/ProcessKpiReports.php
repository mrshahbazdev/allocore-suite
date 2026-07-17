<?php

namespace Modules\FinancialPlatform\Console;

use Illuminate\Console\Command;
use Modules\FinancialPlatform\Jobs\SendKpiReportJob;
use Modules\FinancialPlatform\Models\KpiSchedule;

class ProcessKpiReports extends Command
{
    protected $signature = 'financial-platform:send-kpi-reports';

    protected $description = 'Dispatch KPI report emails for due schedules';

    public function handle(): int
    {
        $schedules = KpiSchedule::withoutGlobalScopes()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('next_run_at')
                    ->orWhere('next_run_at', '<=', now());
            })
            ->get();

        foreach ($schedules as $schedule) {
            SendKpiReportJob::dispatch($schedule);
        }

        $this->info("Dispatched {$schedules->count()} KPI report jobs.");

        return self::SUCCESS;
    }
}
