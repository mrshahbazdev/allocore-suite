<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class SlackWebhookChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSlackWebhook')) {
            return;
        }

        $type = $notification->type ?? 'general';
        $preference = $notifiable->notificationPreferences?->firstWhere('type', $type);

        if (! $preference || ! $preference->slack || ! $preference->slack_webhook) {
            return;
        }

        $payload = $notification->toSlackWebhook($notifiable);

        Http::post($preference->slack_webhook, is_array($payload) ? $payload : ['text' => (string) $payload]);
    }
}
