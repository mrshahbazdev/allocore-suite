<?php

namespace Modules\FocusMatrix\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiProvider implements AiProvider
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'gpt-4o-mini',
    ) {}

    public function chat(string $system, string $user, array $options = []): string
    {
        $model = $options['model'] ?? $this->model;
        $wantJson = $options['json'] ?? false;
        $temperature = $options['temperature'] ?? 0.4;

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'temperature' => $temperature,
            'max_tokens' => 1024,
        ];
        if ($wantJson) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = Http::timeout(30)
            ->withToken($this->apiKey)
            ->post('https://api.openai.com/v1/chat/completions', $payload);

        if (! $response->ok()) {
            throw new RuntimeException('OpenAI error: '.$response->body());
        }

        $text = data_get($response->json(), 'choices.0.message.content');
        if (! $text) {
            throw new RuntimeException('OpenAI returned empty response');
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
        return 'OpenAI';
    }
}
