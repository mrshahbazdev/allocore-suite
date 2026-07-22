<?php

namespace Modules\FocusMatrix\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\FocusMatrix\Models\Delegation;

class DelegationAssigned extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Delegation $delegation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('New delegation assigned to you: ').$this->delegation->task?->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'focusmatrix::emails.delegation-assigned',
        );
    }
}
