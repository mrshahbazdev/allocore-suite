<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;

class TestMail extends TemplatedMailable
{
    public function __construct(public User $user) {}

    public function templateTool(): string
    {
        return 'core';
    }

    public function templateKey(): string
    {
        return 'test';
    }

    public function templateVariables(): array
    {
        return [
            'userName' => $this->user->name,
            'appName' => config('app.name'),
        ];
    }

    protected function defaultSubject(): string
    {
        return __('Test email from :app', ['app' => config('app.name')]);
    }

    protected function defaultContent(): Content
    {
        return new Content(
            view: 'emails.test',
            with: [
                'userName' => $this->user->name,
                'appName' => config('app.name'),
            ],
        );
    }
}
