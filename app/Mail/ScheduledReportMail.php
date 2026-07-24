<?php

namespace App\Mail;

use App\Models\ScheduledReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScheduledReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ScheduledReport $scheduledReport,
        public string $filePath,
        public string $fileName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Scheduled report').': '.$this->scheduledReport->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.scheduled-report',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)->as($this->fileName),
        ];
    }
}
