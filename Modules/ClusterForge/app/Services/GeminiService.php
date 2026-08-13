<?php

namespace Modules\ClusterForge\Services;

use App\Models\SiteSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\ClusterForge\Services\Contracts\AiProvider;
use RuntimeException;

class GeminiService implements AiProvider
{
    /** @var array<int, string> */
    protected array $models;

    /** @var array<int, string> */
    protected array $validDefaults = [
        'gemini-3.6-flash',
        'gemini-3.5-flash',
        'gemini-2.5-flash',
    ];

    /** @var array<int, string> */
    protected array $deprecatedModels = [
        'gemini-1.5-flash',
        'gemini-1.5-flash-latest',
        'gemini-1.5-pro',
        'gemini-1.5-pro-latest',
        'gemini-2.0-flash',
        'gemini-2.0-flash-001',
        'gemini-2.0-flash-lite',
        'gemini-2.0-flash-lite-001',
        'gemini-2.5-flash-preview-05-20',
        'gemini-flash-latest',
    ];

    public function __construct(
        protected ?string $apiKey = null,
        ?string $model = null,
        protected ?string $baseUrl = null,
        protected ?int $timeout = null,
    ) {
        $this->apiKey ??= $this->decryptApiKey((string) $this->setting('api_key', config('services.gemini.api_key', '')));
        $this->baseUrl ??= rtrim((string) $this->setting('base_url', config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta')), '/');
        $this->timeout ??= (int) $this->setting('timeout', config('services.gemini.timeout', 120));

        $primary = $model ?: (string) $this->setting('model', config('services.gemini.model', 'gemini-2.5-flash'));
        $fallbacks = $this->fallbackModels();

        $candidates = array_merge([$primary], $fallbacks, $this->validDefaults);
        $this->models = array_values(array_unique(array_filter(array_diff($candidates, $this->deprecatedModels))));
    }

    private function setting(string $key, mixed $default): mixed
    {
        $siteValue = SiteSetting::value('gemini_'.$key);

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

    /**
     * @return array<int, string>
     */
    private function fallbackModels(): array
    {
        $siteValue = SiteSetting::value('gemini_fallback_models');
        $value = $siteValue !== null ? $siteValue : config('services.gemini.fallback_models', [
            'gemini-3.6-flash',
            'gemini-3.5-flash',
            'gemini-2.5-flash',
        ]);

        if (is_array($value)) {
            return array_values(array_filter($value));
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }

    public function name(): string
    {
        return 'Google Gemini';
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->apiKey !== '0';
    }

    public function generateText(string $prompt, float $temperature = 0.7): string
    {
        $lastException = null;

        foreach ($this->models as $model) {
            try {
                $response = $this->callForModel($model, $prompt, $temperature, jsonMode: false);

                return $this->extractText($response);
            } catch (RuntimeException $e) {
                $lastException = $e;
                Log::warning('Gemini text generation failed for model, trying next', [
                    'model' => $model,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        throw $lastException ?? new RuntimeException('All Gemini models failed to generate text.');
    }

    /**
     * @return array<string, mixed>|array<int, mixed>
     */
    public function generateJson(string $prompt, float $temperature = 0.6): array
    {
        $lastException = null;

        foreach ($this->models as $model) {
            try {
                $response = $this->callForModel($model, $prompt, $temperature, jsonMode: true);
                $text = $this->extractText($response);
                $decoded = $this->decodeJson($text);

                if (! is_array($decoded)) {
                    throw new RuntimeException('Gemini returned non-array JSON: '.substr($text, 0, 500));
                }

                return $decoded;
            } catch (RuntimeException $e) {
                $lastException = $e;
                Log::warning('Gemini JSON generation failed for model, trying next', [
                    'model' => $model,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        throw $lastException ?? new RuntimeException('All Gemini models failed to generate JSON.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function callForModel(string $model, string $prompt, float $temperature, bool $jsonMode): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException(
                'Gemini API key is not configured. Add it in Settings > Gemini or set GEMINI_API_KEY in your .env file.'
            );
        }

        $maxRetriesPerModel = (int) $this->setting('max_retries', config('services.gemini.max_retries', 3));
        $baseDelayMs = (int) $this->setting('retry_base_delay_ms', config('services.gemini.retry_base_delay_ms', 1500));

        $lastStatus = 0;
        $lastBody = '';

        for ($attempt = 1; $attempt <= $maxRetriesPerModel; $attempt++) {
            $response = $this->singleCall($model, $prompt, $temperature, $jsonMode);

            if ($response->successful()) {
                return (array) $response->json();
            }

            $lastStatus = $response->status();
            $lastBody = $response->body();

            Log::warning('Gemini API call failed', [
                'model' => $model,
                'attempt' => $attempt,
                'status' => $lastStatus,
                'body' => substr($lastBody, 0, 500),
            ]);

            if (in_array($lastStatus, [400, 404], true)) {
                Log::info('Gemini {} on model — trying next fallback', ['status' => $lastStatus, 'model' => $model]);

                break;
            }

            if (! in_array($lastStatus, [429, 500, 502, 503, 504], true)) {
                throw new RuntimeException(
                    sprintf('Gemini API error %d: %s', $lastStatus, substr($lastBody, 0, 500))
                );
            }

            if ($attempt < $maxRetriesPerModel) {
                $delayMs = $this->retryDelayMs($response, $baseDelayMs * (2 ** ($attempt - 1)));
                usleep($delayMs * 1000);
            }
        }

        throw new RuntimeException(
            sprintf('Gemini API error %d after retries for model %s: %s', $lastStatus, $model, substr($lastBody, 0, 500))
        );
    }

    protected function retryDelayMs(Response $response, int $defaultDelayMs): int
    {
        $retryAfter = $response->header('Retry-After');
        if (is_string($retryAfter) && is_numeric($retryAfter)) {
            return max(1000, (int) (((float) $retryAfter) * 1000));
        }

        $body = $response->body();
        if (preg_match('/retry in ([\d.]+)\s*s/i', $body, $m)) {
            $seconds = (float) $m[1];

            return max(1000, (int) ($seconds * 1000));
        }

        return max(1000, $defaultDelayMs);
    }

    protected function singleCall(string $model, string $prompt, float $temperature, bool $jsonMode): Response
    {
        $url = sprintf('%s/models/%s:generateContent', $this->baseUrl, $model);

        $payload = [
            'contents' => [[
                'role' => 'user',
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => (int) $this->setting('max_output_tokens', config('services.gemini.max_output_tokens', 8192)),
            ],
        ];

        if ($jsonMode) {
            $payload['generationConfig']['responseMimeType'] = 'application/json';
        }

        return Http::timeout($this->timeout)
            ->withHeaders([
                'x-goog-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($url, $payload);
    }

    /**
     * @param  array<string, mixed>  $response
     */
    protected function extractText(array $response): string
    {
        $candidates = $response['candidates'] ?? [];
        if (! is_array($candidates) || empty($candidates)) {
            throw new RuntimeException('Gemini returned no candidates: '.json_encode($response));
        }

        $finishReason = $candidates[0]['finishReason'] ?? null;
        if ($finishReason === 'MAX_TOKENS') {
            throw new RuntimeException('Gemini output hit MAX_TOKENS — response was truncated.');
        }

        $parts = $candidates[0]['content']['parts'] ?? [];
        if (! is_array($parts) || empty($parts)) {
            throw new RuntimeException('Gemini candidate had no parts: '.json_encode($response));
        }

        $text = '';
        foreach ($parts as $part) {
            if (is_array($part) && isset($part['text'])) {
                $text .= $part['text'];
            }
        }

        $trimmed = trim($text);
        if ($trimmed === '') {
            throw new RuntimeException('Gemini returned empty text');
        }

        return $trimmed;
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

        throw new RuntimeException('Failed to parse Gemini JSON: '.substr($text, 0, 500));
    }
}
