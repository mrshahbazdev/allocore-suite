<?php

namespace Modules\InvoiceMaker\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AllocoreReporter
{
    /**
     * Push a metric event to the Foundation webhook and to the Allocore
     * Manager signal ingest (dirksoelter.de) when an ingest URL is configured.
     *
     * @param  array<string, mixed>  $body
     */
    public function push(string $type, array $body): void
    {
        $this->post(
            SiteSetting::value('allocore.webhook_url') ?: config('services.allocore.webhook_url'),
            ['type' => $type] + $body,
            'Allocore metric push',
            $type,
        );

        $occurredAt = $body['occurred_at'] ?? null;
        $companyKey = $body['company_key'] ?? null;
        unset($body['occurred_at'], $body['company_key']);

        $this->post(
            SiteSetting::value('manager.ingest_url') ?: config('services.manager.ingest_url'),
            [
                'type' => $type,
                'company_key' => $companyKey,
                'payload' => $body,
                'occurred_at' => $occurredAt,
            ],
            'Manager signal push',
            $type,
        );
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function post(mixed $url, array $body, string $label, string $type): void
    {
        if (! is_string($url) || $url === '') {
            return;
        }

        try {
            $response = Http::timeout(5)->acceptJson()->post($url, $body);

            if (! $response->successful()) {
                Log::warning($label.' rejected', [
                    'type' => $type,
                    'status' => $response->status(),
                ]);
            }
        } catch (Throwable $e) {
            Log::warning($label.' failed', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
