<?php

namespace Modules\LoopEngine\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\LoopEngine\Models\Webhook;
use Modules\LoopEngine\Models\WebhookLog;

class WebhookService
{
    public function dispatch(string $event, array $data, int $teamId): void
    {
        $webhooks = Webhook::where('team_id', $teamId)->where('is_active', true)->get();

        foreach ($webhooks as $webhook) {
            if (! $webhook->listensTo($event)) {
                continue;
            }

            $this->send($webhook, $event, $data);
        }
    }

    protected function send(Webhook $webhook, string $event, array $data): void
    {
        $payload = [
            'event' => $event,
            'timestamp' => now()->toISOString(),
            'data' => $data,
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'X-LoopEngine-Event' => $event,
        ];

        if ($webhook->secret) {
            $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret);
            $headers['X-LoopEngine-Signature'] = $signature;
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->post($webhook->url, $payload);

            WebhookLog::create([
                'team_id' => $webhook->team_id,
                'webhook_id' => $webhook->id,
                'event' => $event,
                'payload' => $payload,
                'response_code' => $response->status(),
                'response_body' => substr($response->body(), 0, 1000),
                'success' => $response->successful(),
            ]);

            if ($response->successful()) {
                $webhook->update([
                    'last_triggered_at' => now(),
                    'failure_count' => 0,
                ]);
            } else {
                $webhook->increment('failure_count');
                if ($webhook->failure_count >= 10) {
                    $webhook->update(['is_active' => false]);
                }
            }
        } catch (\Exception $e) {
            WebhookLog::create([
                'team_id' => $webhook->team_id,
                'webhook_id' => $webhook->id,
                'event' => $event,
                'payload' => $payload,
                'response_code' => 0,
                'response_body' => $e->getMessage(),
                'success' => false,
            ]);

            $webhook->increment('failure_count');
            if ($webhook->failure_count >= 10) {
                $webhook->update(['is_active' => false]);
            }

            Log::warning("Webhook {$webhook->name} failed: {$e->getMessage()}");
        }
    }
}
