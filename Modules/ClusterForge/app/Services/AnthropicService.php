<?php

namespace Modules\ClusterForge\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\ClusterForge\Services\Contracts\AiProvider;
use RuntimeException;

class AnthropicService implements AiProvider
{
    protected ?string $apiKey;

    protected string $model;

    protected string $baseUrl;

    protected int $timeout;

    protected int $maxRetries;

    protected int $retryBaseDelayMs;

    protected int $maxOutputTokens;

    public function __construct()
    {
        $this->apiKey = $this->decryptApiKey((string) $this->setting('api_key', config('services.anthropic.api_key', '')));
        $this->model = (string) $this->setting('model', config('services.anthropic.model', 'claude-3-5-sonnet-latest'));
        $this->baseUrl = rtrim((string) $this->setting('base_url', config('services.anthropic.base_url', 'https://api.anthropic.com/v1')), '/');
        $this->timeout = (int) $this->setting('timeout', config('services.ai.timeout', config('services.gemini.timeout', 120)));
        $this->maxOutputTokens = (int) $this->setting('max_output_tokens', config('services.ai.max_output_tokens', config('services.gemini.max_output_tokens', 8192)));
        $this->maxRetries = (int) $this->setting('max_retries', config('services.ai.max_retries', config('services.gemini.max_retries', 3)));
        $this->retryBaseDelayMs = (int) $this->setting('retry_base_delay_ms', config('services.ai.retry_base_delay_ms', config('services.gemini.retry_base_delay_ms', 1500)));
    }

    public function name(): string
    {
        return 'Anthropic';
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->apiKey !== '0';
    }

    public function generateText(string $prompt, float $temperature = 0.7): string
    {
        return $this->callApi($prompt, $temperature, jsonMode: false);
    }

    /**
     * @return array<string, mixed>|array<int, mixed>
     */
    public function generateJson(string $prompt, float $temperature = 0.6): array
    {
        $text = $this->callApi($this->jsonPrompt($prompt), $temperature, jsonMode: false);

        $decoded = $this->decodeJson($text);

        if (! is_array($decoded)) {
            throw new RuntimeException('Anthropic returned non-array JSON: '.substr($text, 0, 500));
        }

        return $decoded;
    }

    protected function jsonPrompt(string $prompt): string
    {
        return $prompt."\n\nRespond with ONLY valid JSON, no markdown, no commentary, no code fences.";
    }

    protected function callApi(string $prompt, float $temperature, bool $jsonMode): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException(
                'Anthropic API key is not configured. Add it in Settings > AI or set ANTHROPIC_API_KEY in your .env file.'
            );
        }

        $url = $this->baseUrl.'/messages';

        $payload = [
            'model' => $this->model,
            'max_tokens' => $this->maxOutputTokens,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $temperature,
        ];

        $lastStatus = 0;
        $lastBody = '';

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $payload);

            if ($response->successful()) {
                return (string) $response->json('content.0.text');
            }

            $lastStatus = $response->status();
            $lastBody = $response->body();

            Log::warning('Anthropic API call failed', [
                'model' => $this->model,
                'attempt' => $attempt,
                'status' => $lastStatus,
                'body' => substr($lastBody, 0, 500),
            ]);

            if (in_array($lastStatus, [400, 404], true)) {
                break;
            }

            if ($lastStatus === 429 && str_contains(strtolower($lastBody), 'quota')) {
                break;
            }

            if ($attempt < $this->maxRetries) {
                $delay = $this->retryBaseDelayMs * (2 ** ($attempt - 1));
                usleep($delay * 1000);
            }
        }

        throw new RuntimeException(
            sprintf('Anthropic API error %d after %d attempt(s): %s', $lastStatus, $this->maxRetries, substr($lastBody, 0, 500))
        );
    }

    /**
     * @return mixed
     */
    protected function decodeJson(string $text)
    {
        $text = trim($text);

        if (preg_match('/^```(?:json)?\s*(.+?)\s*```$/is', $text, $m)) {
            $text = $m[1];
        }

        $decoded = json_decode($text, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        if (preg_match('/(\[.*\]|\{.*\})/s', $text, $m)) {
            $decoded = json_decode($m[1], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        throw new RuntimeException('Failed to parse Anthropic JSON: '.substr($text, 0, 500));
    }

    private function setting(string $key, mixed $default): mixed
    {
        $siteValue = SiteSetting::value('anthropic_'.$key);

        return $siteValue !== null ? $siteValue : $default;
    }

    private function decryptApiKey(string $apiKey): string
    {
        if ($apiKey === '') {
            return '';
        }

        try {
            $decrypted = Crypt::decryptString($apiKey);

            return $decrypted;
        } catch (\Throwable) {
            return $apiKey;
        }
    }
}
