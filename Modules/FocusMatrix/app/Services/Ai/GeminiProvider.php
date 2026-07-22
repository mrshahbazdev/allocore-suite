<?php

namespace Modules\FocusMatrix\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProvider implements AiProvider
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'gemini-2.0-flash',
    ) {}

    public function chat(string $system, string $user, array $options = []): string
    {
        $model = $options['model'] ?? $this->model;
        $wantJson = $options['json'] ?? false;
        $temperature = $options['temperature'] ?? 0.4;

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $system]],
            ],
            'contents' => [[
                'role' => 'user',
                'parts' => [['text' => $user]],
            ]],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => 1024,
            ],
        ];

        if ($wantJson) {
            $payload['generationConfig']['responseMimeType'] = 'application/json';
        }

        $response = Http::timeout(30)
            ->withHeaders(['x-goog-api-key' => $this->apiKey, 'Content-Type' => 'application/json'])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", $payload);

        if (! $response->ok()) {
            throw new RuntimeException('Gemini error: '.$response->body());
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
        if (! $text) {
            throw new RuntimeException('Gemini returned empty response');
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
        return 'Google Gemini';
    }
}
