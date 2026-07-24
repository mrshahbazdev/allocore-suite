<?php

namespace App\Jobs;

use App\Mail\ScheduledReportMail;
use App\Models\ScheduledReport;
use App\Services\ReportGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class GenerateScheduledReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ScheduledReport $scheduledReport) {}

    public function handle(ReportGenerator $generator): void
    {
        $this->scheduledReport->refresh();

        if (! $this->scheduledReport->is_active) {
            return;
        }

        auth()->login($this->scheduledReport->user);

        $file = $generator->generate($this->scheduledReport);

        Mail::to($this->scheduledReport->email)->send(new ScheduledReportMail(
            $this->scheduledReport,
            $file['path'],
            $file['filename'],
        ));

        if (file_exists($file['path'])) {
            unlink($file['path']);
        }

        $this->scheduledReport->last_run_at = now();
        $this->scheduledReport->calculateNextRun();
        $this->scheduledReport->save();
    }

    public function failed(\Throwable $exception): void
    {
        report($exception);
    }
}
