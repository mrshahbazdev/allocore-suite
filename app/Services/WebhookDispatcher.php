<?php

namespace App\Services;

use App\Jobs\SendWebhookCallJob;
use App\Models\Webhook;

class WebhookDispatcher
{
    public static function dispatch(string $event, array $payload = []): void
    {
        $webhooks = Webhook::where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();

        foreach ($webhooks as $webhook) {
            $call = $webhook->calls()->create([
                'event' => $event,
                'payload' => $payload,
            ]);

            SendWebhookCallJob::dispatch($call);
        }
    }
}
