<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

abstract class TemplatedMailable extends Mailable
{
    use Queueable, SerializesModels;

    protected ?array $rendered = null;

    abstract public function templateTool(): string;

    abstract public function templateKey(): string;

    abstract public function templateVariables(): array;

    protected function defaultSubject(): string
    {
        return '';
    }

    abstract protected function defaultContent(): Content;

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->rendered()['subject'] ?: $this->defaultSubject());
    }

    public function content(): Content
    {
        $rendered = $this->rendered();

        if (filled($rendered['html'])) {
            return new Content(htmlString: $rendered['html']);
        }

        return $this->defaultContent();
    }

    protected function rendered(): array
    {
        if ($this->rendered === null) {
            $this->rendered = app(EmailTemplateService::class)->render(
                $this->templateTool(),
                $this->templateKey(),
                $this->templateVariables()
            );
        }

        return $this->rendered;
    }
}
