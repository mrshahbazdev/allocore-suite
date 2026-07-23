<?php

namespace App\Notifications;

use App\Models\Alert;
use App\Models\User;
use App\Notifications\Channels\SlackWebhookChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlertNotification extends Notification
{
    use Queueable;

    public function __construct(protected Alert $alert, protected float $value) {}

    public function via(object $notifiable): array
    {
        $preference = $notifiable instanceof User
            ? $notifiable->notificationPreference('alerts')
            : null;

        if (! $preference) {
            return ['database'];
        }

        $channels = [];

        if ($preference->in_app) {
            $channels[] = 'database';
        }

        if ($preference->email) {
            $channels[] = 'mail';
        }

        if ($preference->slack && $preference->slack_webhook) {
            $channels[] = SlackWebhookChannel::class;
        }

        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->alert->name)
            ->line($this->body());
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'warning',
            'subject' => $this->alert->name,
            'body' => $this->body(),
            'action_url' => route('alerts.index'),
            'action_text' => __('Manage alerts'),
        ];
    }

    public function toSlackWebhook(object $notifiable): array
    {
        return ['text' => "*{$this->alert->name}*\n".$this->body()];
    }

    protected function body(): string
    {
        return __('Alert :name triggered. Current value is :value (threshold :operator :threshold).', [
            'name' => $this->alert->name,
            'value' => number_format($this->value, 2),
            'operator' => $this->alert->operator,
            'threshold' => number_format($this->alert->threshold, 2),
        ]);
    }
}
