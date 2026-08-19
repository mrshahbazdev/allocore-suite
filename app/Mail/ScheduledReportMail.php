<?php

namespace App\Mail;

use App\Models\ScheduledReport;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;

class ScheduledReportMail extends TemplatedMailable
{
    public function __construct(
        public ScheduledReport $scheduledReport,
        public string $filePath,
        public string $fileName,
    ) {}

    public function templateTool(): string
    {
        return 'core';
    }

    public function templateKey(): string
    {
        return 'scheduled-report';
    }

    public function templateVariables(): array
    {
        return [
            'title' => $this->scheduledReport->title,
            'reportType' => $this->scheduledReport->report_type,
            'frequency' => $this->scheduledReport->frequency,
            'format' => $this->scheduledReport->format,
            'appName' => config('app.name'),
        ];
    }

    protected function defaultSubject(): string
    {
        return __('Scheduled report').': '.$this->scheduledReport->title;
    }

    protected function defaultContent(): Content
    {
        return new Content(
            view: 'emails.scheduled-report',
            with: $this->templateVariables(),
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)->as($this->fileName),
        ];
    }
}
