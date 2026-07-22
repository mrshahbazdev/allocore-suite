<?php

namespace Modules\FocusMatrix\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnthropicProvider implements AiProvider
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'claude-3-5-sonnet-latest',
    ) {}

    public function chat(string $system, string $user, array $options = []): string
    {
        $model = $options['model'] ?? $this->model;
        $temperature = $options['temperature'] ?? 0.4;

        $payload = [
            'model' => $model,
            'max_tokens' => 1024,
            'temperature' => $temperature,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => $user],
            ],
        ];

        $response = Http::timeout(30)
            ->withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', $payload);

        if (! $response->ok()) {
            throw new RuntimeException('Anthropic error: '.$response->body());
        }

        $text = data_get($response->json(), 'content.0.text');
        if (! $text) {
            throw new RuntimeException('Anthropic returned empty response');
        }

        return trim($text);
    }

    public function ping(): bool
    {
        $out = $this->chat('You are a health check.', 'Respond with just: OK', ['temperature' => 0]);

        return str_contains(strtoupper($out), 'OK');
    }

    public function name(): string
    {
        return 'Anthropic Claude';
    }
}
