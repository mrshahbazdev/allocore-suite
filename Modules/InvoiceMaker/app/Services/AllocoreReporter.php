<?php

namespace Modules\InvoiceMaker\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AllocoreReporter
{
    public function push(string $type, array $body): void
    {
        $url = config('services.allocore.webhook_url');

        if (! is_string($url) || $url === '') {
            return;
        }

        try {
            $response = Http::timeout(5)->acceptJson()->post($url, ['type' => $type] + $body);

            if (! $response->successful()) {
                Log::warning('Allocore metric push rejected', [
                    'type' => $type,
                    'status' => $response->status(),
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('Allocore metric push failed', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
