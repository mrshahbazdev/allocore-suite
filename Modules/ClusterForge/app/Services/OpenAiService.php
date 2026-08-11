<?php

namespace Modules\ClusterForge\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\ClusterForge\Services\Contracts\AiProvider;
use RuntimeException;

class OpenAiService implements AiProvider
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
        $this->apiKey = $this->decryptApiKey((string) $this->setting('api_key', config('services.openai.api_key', config('services.openai.key', ''))));
        $this->model = (string) $this->setting('model', config('services.openai.model', 'gpt-4o-mini'));
        $this->baseUrl = rtrim((string) $this->setting('base_url', config('services.openai.base_url', 'https://api.openai.com/v1')), '/');
        $this->timeout = (int) $this->setting('timeout', config('services.ai.timeout', config('services.gemini.timeout', 120)));
        $this->maxOutputTokens = (int) $this->setting('max_output_tokens', config('services.ai.max_output_tokens', config('services.gemini.max_output_tokens', 8192)));
        $this->maxRetries = (int) $this->setting('max_retries', config('services.ai.max_retries', config('services.gemini.max_retries', 3)));
        $this->retryBaseDelayMs = (int) $this->setting('retry_base_delay_ms', config('services.ai.retry_base_delay_ms', config('services.gemini.retry_base_delay_ms', 1500)));
    }

    public function name(): string
    {
        return 'OpenAI';
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
        $text = $this->callApi($prompt, $temperature, jsonMode: true);

        $decoded = $this->decodeJson($text);

        if (! is_array($decoded)) {
            throw new RuntimeException('OpenAI returned non-array JSON: '.substr($text, 0, 500));
        }

        return $decoded;
    }

    protected function callApi(string $prompt, float $temperature, bool $jsonMode): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException(
                'OpenAI API key is not configured. Add it in Settings > AI or set OPENAI_API_KEY in your .env file.'
            );
        }

        $url = $this->baseUrl.'/chat/completions';

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $temperature,
            'max_tokens' => $this->maxOutputTokens,
        ];

        if ($jsonMode) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $lastStatus = 0;
        $lastBody = '';

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            $response = Http::timeout($this->timeout)
                ->withToken($this->apiKey)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->successful()) {
                return (string) $response->json('choices.0.message.content');
            }

            $lastStatus = $response->status();
            $lastBody = $response->body();

            Log::warning('OpenAI API call failed', [
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
            sprintf('OpenAI API error %d after %d attempt(s): %s', $lastStatus, $this->maxRetries, substr($lastBody, 0, 500))
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

        throw new RuntimeException('Failed to parse OpenAI JSON: '.substr($text, 0, 500));
    }

    private function setting(string $key, mixed $default): mixed
    {
        $siteValue = SiteSetting::value('openai_'.$key);

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
