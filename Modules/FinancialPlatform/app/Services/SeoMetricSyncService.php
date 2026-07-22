<?php

namespace Modules\FinancialPlatform\Services;

use Illuminate\Support\Facades\Http;
use Modules\FinancialPlatform\Models\Setting;

class SeoMetricSyncService
{
    public function syncForTeam(): array
    {
        $token = (string) Setting::get('gsc_access_token', '');
        $siteUrl = (string) Setting::get('gsc_site_url', '');
        $seostory = app(SeoStoryApiClient::class);

        $result = ['success' => true, 'messages' => []];

        if ($token && $siteUrl) {
            $gscResult = $this->syncSearchConsole($token, $siteUrl);
            $result['messages'][] = $gscResult['message'];

            if (! $gscResult['success']) {
                $result['success'] = false;
            }
        }

        $seostoryMetrics = $seostory->leadMetrics(now()->year, now()->month);
        if ($seostoryMetrics) {
            $this->storeIfPresent('page_value_current', $seostoryMetrics['page_value'] ?? null);
            $this->storeIfPresent('page_value_previous', $seostoryMetrics['page_value_previous'] ?? null);
            $result['messages'][] = 'SeoStory metrics synced.';
        }

        return $result;
    }

    private function syncSearchConsole(string $token, string $siteUrl): array
    {
        $currentStart = now()->startOfMonth()->toDateString();
        $currentEnd = now()->endOfMonth()->toDateString();
        $previousStart = now()->subMonth()->startOfMonth()->toDateString();
        $previousEnd = now()->subMonth()->endOfMonth()->toDateString();

        $current = $this->fetchSearchConsole($token, $siteUrl, $currentStart, $currentEnd);
        $previous = $this->fetchSearchConsole($token, $siteUrl, $previousStart, $previousEnd);

        if ($current === null || $previous === null) {
            return ['success' => false, 'message' => 'Google Search Console API call failed. Check access token and site URL.'];
        }

        $this->storeMetrics('current', $current);
        $this->storeMetrics('previous', $previous);

        return ['success' => true, 'message' => 'Google Search Console metrics synced.'];
    }

    private function fetchSearchConsole(string $token, string $siteUrl, string $start, string $end): ?array
    {
        $encodedSite = urlencode($siteUrl);
        $url = "https://www.googleapis.com/webmasters/v3/sites/{$encodedSite}/searchAnalytics/query";

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->post($url, [
                    'startDate' => $start,
                    'endDate' => $end,
                    'dimensions' => ['date'],
                    'rowLimit' => 5000,
                ]);

            if (! $response->successful()) {
                return null;
            }

            $rows = $response->json('rows', []);

            $clicks = 0;
            $impressions = 0;
            $weightedPosition = 0.0;

            foreach ($rows as $row) {
                $impr = (int) ($row['impressions'] ?? 0);
                $clicks += (int) ($row['clicks'] ?? 0);
                $impressions += $impr;
                $weightedPosition += ((float) ($row['position'] ?? 0)) * $impr;
            }

            $ctr = $impressions > 0 ? ($clicks / $impressions) * 100 : 0.0;
            $avgPosition = $impressions > 0 ? $weightedPosition / $impressions : 0.0;

            return [
                'impressions' => $impressions,
                'clicks' => $clicks,
                'ctr' => round($ctr, 2),
                'average_position' => round($avgPosition, 2),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function storeMetrics(string $period, array $metrics): void
    {
        foreach ($metrics as $key => $value) {
            Setting::set("deep_kpi_{$key}_{$period}", (string) $value);
        }
    }

    private function storeIfPresent(string $key, mixed $value): void
    {
        if ($value !== null && is_numeric($value)) {
            Setting::set("deep_kpi_{$key}_current", (string) $value);
        }
    }
}
