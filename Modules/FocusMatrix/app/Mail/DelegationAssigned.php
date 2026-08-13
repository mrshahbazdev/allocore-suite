<?php

namespace Modules\FocusMatrix\Mail;

use App\Mail\TemplatedMailable;
use Illuminate\Mail\Mailables\Content;
use Modules\FocusMatrix\Models\Delegation;

class DelegationAssigned extends TemplatedMailable
{
    public function __construct(public Delegation $delegation) {}

    public function templateTool(): string
    {
        return 'focusmatrix';
    }

    public function templateKey(): string
    {
        return 'delegation-assigned';
    }

    public function templateVariables(): array
    {
        return [
            'taskTitle' => $this->delegation->task?->title,
            'goal' => $this->delegation->goal,
            'url' => route('focusmatrix.delegations.assigned'),
            'buttonText' => __('View in FocusMatrix'),
            'appName' => config('app.name'),
        ];
    }

    protected function defaultSubject(): string
    {
        return __('New delegation assigned to you: ').$this->delegation->task?->title;
    }

    protected function defaultContent(): Content
    {
        return new Content(
            markdown: 'focusmatrix::emails.delegation-assigned',
        );
    }
}
