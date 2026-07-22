<?php

namespace Modules\FocusMatrix\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\FocusMatrix\Models\Integration;

class WebhookNotifier
{
    public function notify(User $user, string $title, string $body, array $facts = [], ?string $link = null): void
    {
        $integrations = Integration::where('user_id', $user->id)
            ->whereIn('provider', [Integration::PROVIDER_SLACK, Integration::PROVIDER_TEAMS])
            ->get();

        foreach ($integrations as $integration) {
            $url = $integration->meta['webhook_url'] ?? null;
            if (! $url) {
                continue;
            }

            try {
                if ($integration->provider === Integration::PROVIDER_SLACK) {
                    Http::timeout(8)->post($url, $this->slackPayload($title, $body, $facts, $link));
                } elseif ($integration->provider === Integration::PROVIDER_TEAMS) {
                    Http::timeout(8)->post($url, $this->teamsPayload($title, $body, $facts, $link));
                }
                $integration->update(['last_synced_at' => now()]);
            } catch (\Throwable $e) {
                Log::channel('daily')->warning('FocusMatrix webhook notification failed', [
                    'integration_id' => $integration->id,
                    'provider' => $integration->provider,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function test(string $provider, string $url): array
    {
        try {
            $payload = $provider === 'slack'
                ? $this->slackPayload('FocusMatrix test', 'If you can read this, your webhook is connected correctly.', [])
                : $this->teamsPayload('FocusMatrix test', 'If you can read this, your webhook is connected correctly.', []);
            $response = Http::timeout(8)->post($url, $payload);

            return ['ok' => $response->successful(), 'status' => $response->status(), 'body' => substr($response->body(), 0, 200)];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => 0, 'body' => $e->getMessage()];
        }
    }

    private function slackPayload(string $title, string $body, array $facts, ?string $link = null): array
    {
        $blocks = [
            ['type' => 'header', 'text' => ['type' => 'plain_text', 'text' => '🎯 '.$title]],
            ['type' => 'section', 'text' => ['type' => 'mrkdwn', 'text' => $body]],
        ];
        if (! empty($facts)) {
            $fields = [];
            foreach ($facts as $k => $v) {
                $fields[] = ['type' => 'mrkdwn', 'text' => "*{$k}*\n{$v}"];
            }
            $blocks[] = ['type' => 'section', 'fields' => array_slice($fields, 0, 10)];
        }
        if ($link) {
            $blocks[] = [
                'type' => 'actions',
                'elements' => [[
                    'type' => 'button',
                    'text' => ['type' => 'plain_text', 'text' => 'Open in FocusMatrix'],
                    'url' => $link,
                ]],
            ];
        }

        return ['text' => $title, 'blocks' => $blocks];
    }

    private function teamsPayload(string $title, string $body, array $facts, ?string $link = null): array
    {
        $facts_array = [];
        foreach ($facts as $name => $value) {
            $facts_array[] = ['name' => (string) $name, 'value' => (string) $value];
        }
        $card = [
            '@type' => 'MessageCard',
            '@context' => 'https://schema.org/extensions',
            'themeColor' => '2F6BFF',
            'summary' => $title,
            'title' => '🎯 '.$title,
            'text' => $body,
            'sections' => [],
        ];
        if (! empty($facts_array)) {
            $card['sections'][] = ['facts' => $facts_array];
        }
        if ($link) {
            $card['potentialAction'] = [[
                '@type' => 'OpenUri',
                'name' => 'Open in FocusMatrix',
                'targets' => [['os' => 'default', 'uri' => $link]],
            ]];
        }

        return $card;
    }
}
