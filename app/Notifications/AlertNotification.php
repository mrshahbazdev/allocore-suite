<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AlertNotification extends Notification
{
    use Queueable;

    public function __construct(protected Alert $alert, protected float $value) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $subject = $this->alert->name;
        $body = __('Alert :name triggered. Current value is :value (threshold :operator :threshold).', [
            'name' => $this->alert->name,
            'value' => number_format($this->value, 2),
            'operator' => $this->alert->operator,
            'threshold' => number_format($this->alert->threshold, 2),
        ]);

        return [
            'type' => 'warning',
            'subject' => $subject,
            'body' => $body,
            'action_url' => route('alerts.index'),
            'action_text' => __('Manage alerts'),
        ];
    }
}
