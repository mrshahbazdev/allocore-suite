<?php

namespace App\Jobs;

use App\Models\Webhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Webhook $webhook,
        public string $event,
        public array $payload
    ) {}

    public function handle(): void
    {
        if (! $this->webhook->is_active) {
            return;
        }

        $events = $this->webhook->events ?? [];
        if (! in_array('*', $events, true) && ! in_array($this->event, $events, true)) {
            return;
        }

        $payload = [
            'event' => $this->event,
            'timestamp' => now()->toIso8601String(),
            'data' => $this->payload,
        ];

        $signature = $this->signature($payload);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Event' => $this->event,
                ])
                ->post($this->webhook->url, $payload);

            $this->webhook->update(['last_sent_at' => now()]);

            if (! $response->successful()) {
                Log::warning('Webhook delivery failed', [
                    'webhook_id' => $this->webhook->id,
                    'event' => $this->event,
                    'status' => $response->status(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Webhook delivery exception', [
                'webhook_id' => $this->webhook->id,
                'event' => $this->event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function signature(array $payload): string
    {
        $secret = $this->webhook->secret ?: '';

        return hash_hmac('sha256', json_encode($payload), $secret);
    }
}
