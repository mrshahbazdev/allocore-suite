<?php

namespace App\Jobs;

use App\Models\WebhookCall;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendWebhookCallJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public WebhookCall $webhookCall) {}

    public function handle(): void
    {
        $webhook = $this->webhookCall->webhook;

        if (! $webhook || ! $webhook->is_active) {
            return;
        }

        $payload = [
            'event' => $this->webhookCall->event,
            'payload' => $this->webhookCall->payload ?? [],
            'timestamp' => now()->toIso8601String(),
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'X-Webhook-Event' => $this->webhookCall->event,
        ];

        if ($webhook->secret) {
            $headers['X-Webhook-Signature'] = hash_hmac('sha256', json_encode($payload), $webhook->secret);
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($webhook->url, $payload);

            $this->webhookCall->markSent($response->status(), $response->body());
            $webhook->update(['last_sent_at' => now()]);
        } catch (\Throwable $e) {
            $this->webhookCall->markFailed($e->getMessage());

            if ($this->webhookCall->attempts < 3) {
                $this->release(60);
            }
        }
    }
}
