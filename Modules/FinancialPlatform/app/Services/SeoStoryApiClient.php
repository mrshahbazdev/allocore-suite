<?php

namespace Modules\FinancialPlatform\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Modules\FinancialPlatform\Models\Setting;

class SeoStoryApiClient
{
    public function revenue(int $year, int $month): ?float
    {
        $response = $this->get('/api/financial/revenue', [
            'year' => $year,
            'month' => $month,
        ]);

        if (! $response || ! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return is_numeric($data['revenue'] ?? null) ? (float) $data['revenue'] : null;
    }

    public function leadMetrics(int $year, int $month): array
    {
        $response = $this->get('/api/seo/metrics', [
            'year' => $year,
            'month' => $month,
        ]);

        if (! $response || ! $response->successful()) {
            return [];
        }

        return $response->json('data', []);
    }

    private function get(string $path, array $query): ?Response
    {
        $baseUrl = rtrim((string) Setting::get('seostory_base_url', 'https://financial.seostory.de'), '/');
        $token = (string) Setting::get('seostory_api_token', '');

        if (empty($baseUrl) || empty($token)) {
            return null;
        }

        try {
            return Http::withToken($token, 'Bearer')
                ->timeout(30)
                ->get("{$baseUrl}{$path}", $query);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
