<?php

namespace App\Console\Commands;

use App\Jobs\GenerateScheduledReport;
use App\Models\ScheduledReport;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:run-scheduled-reports')]
#[Description('Process due scheduled reports')]
class RunScheduledReports extends Command
{
    public function handle(): int
    {
        $reports = ScheduledReport::due()->get();

        foreach ($reports as $report) {
            GenerateScheduledReport::dispatch($report);
        }

        $this->info("{$reports->count()} scheduled report(s) dispatched.");

        return Command::SUCCESS;
    }
}
