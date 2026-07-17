<?php

namespace App\Services;

use App\Jobs\SendWebhookJob;
use App\Models\Webhook;

class WebhookDispatcher
{
    public static function dispatch(string $event, array $payload): void
    {
        Webhook::where('is_active', true)
            ->chunkById(50, function (Webhook $webhook) use ($event, $payload) {
                SendWebhookJob::dispatch($webhook, $event, $payload);
            });
    }
}
