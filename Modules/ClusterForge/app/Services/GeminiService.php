<?php

namespace Modules\ClusterForge\Services;

use App\Models\SiteSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiService
{
    /** @var array<int, string> */
    protected array $models;

    public function __construct(
        protected ?string $apiKey = null,
        ?string $model = null,
        protected ?string $baseUrl = null,
        protected ?int $timeout = null,
    ) {
        $this->apiKey ??= $this->decryptApiKey((string) $this->setting('api_key', config('services.gemini.api_key', '')));
        $this->baseUrl ??= rtrim((string) $this->setting('base_url', config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta')), '/');
        $this->timeout ??= (int) $this->setting('timeout', config('services.gemini.timeout', 120));

        $primary = $model ?: (string) $this->setting('model', config('services.gemini.model', 'gemini-flash-latest'));
        $fallbacks = $this->fallbackModels();

        $this->models = array_values(array_unique(array_filter(array_merge([$primary], $fallbacks))));
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
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-flash-latest',
        ]);

        if (is_array($value)) {
            return array_values(array_filter($value));
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }

    public function generateText(string $prompt, float $temperature = 0.7): string
    {
        $response = $this->callApi($prompt, $temperature, jsonMode: false);

        return $this->extractText($response);
    }

    /**
     * @return array<string, mixed>|array<int, mixed>
     */
    public function generateJson(string $prompt, float $temperature = 0.6): array
    {
        $response = $this->callApi($prompt, $temperature, jsonMode: true);
        $text = $this->extractText($response);

        $decoded = $this->decodeJson($text);

        if (! is_array($decoded)) {
            throw new RuntimeException('Gemini returned non-array JSON: '.substr($text, 0, 500));
        }

        return $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    protected function callApi(string $prompt, float $temperature, bool $jsonMode): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException(
                'GEMINI_API_KEY is not configured. Set it in your .env file to enable AI generation.'
            );
        }

        $maxRetriesPerModel = (int) $this->setting('max_retries', config('services.gemini.max_retries', 3));
        $baseDelayMs = (int) $this->setting('retry_base_delay_ms', config('services.gemini.retry_base_delay_ms', 1500));

        $lastStatus = 0;
        $lastBody = '';

        foreach ($this->models as $model) {
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

                $retryable = in_array($lastStatus, [429, 500, 502, 503, 504], true);
                if (! $retryable) {
                    throw new RuntimeException(
                        sprintf('Gemini API error %d: %s', $lastStatus, substr($lastBody, 0, 500))
                    );
                }

                if ($lastStatus === 429 && str_contains(strtolower($lastBody), 'quota')) {
                    Log::info('Gemini quota exceeded — switching to next fallback model', [
                        'model' => $model,
                    ]);

                    continue 2;
                }

                if ($attempt < $maxRetriesPerModel) {
                    $delay = $baseDelayMs * (2 ** ($attempt - 1));
                    usleep($delay * 1000);
                }
            }
        }

        throw new RuntimeException(
            sprintf(
                'Gemini API error %d after retries across %d model(s): %s',
                $lastStatus,
                count($this->models),
                substr($lastBody, 0, 500)
            )
        );
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
                'maxOutputTokens' => (int) $this->setting('max_output_tokens', config('services.gemini.max_output_tokens', 32768)),
                'thinkingConfig' => [
                    'thinkingBudget' => 0,
                ],
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
            Log::warning('Gemini output hit MAX_TOKENS — response was truncated.', [
                'finishReason' => $finishReason,
            ]);
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
