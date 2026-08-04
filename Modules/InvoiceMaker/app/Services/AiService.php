<?php

namespace Modules\InvoiceMaker\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected string $provider;

    protected ?string $openaiKey;

    protected ?string $anthropicKey;

    public function __construct()
    {
        $user = auth()->user();

        // 1. Check if user is logged in
        // 2. Try to grab user's specific setting
        // 3. Fallback to global setting if user setting is empty

        $this->provider = ($user && $user->default_ai_provider)
            ? $user->default_ai_provider
            : env('AI_DEFAULT_PROVIDER', 'openai');

        $this->openaiKey = ($user && $user->openai_api_key)
            ? $user->openai_api_key
            : env('OPENAI_API_KEY');

        $this->anthropicKey = ($user && $user->anthropic_api_key)
            ? $user->anthropic_api_key
            : env('ANTHROPIC_API_KEY');
    }

    public function generateText(string $prompt, ?string $imagePath = null): string
    {
        try {
            if ($this->provider === 'anthropic') {
                return $this->callAnthropic($prompt, $imagePath);
            }

            return $this->callOpenAi($prompt, $imagePath);
        } catch (Exception $e) {
            Log::error('AI Service Error: '.$e->getMessage());
            throw $e;
        }
    }

    public function translateJson(array $englishJson, string $targetLanguageName): array
    {
        $jsonString = json_encode($englishJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $prompt = "You are a professional software localization translator. Translate the following JSON object values from English to {$targetLanguageName}. Keep the original JSON keys exactly as they are. Do not add any markdown formatting, backticks, or extra text. RETURN ONLY VALID JSON.\n\n".$jsonString;

        try {
            // Force a slightly higher timeout for translation as it might take longer
            $response = $this->generateText($prompt);

            // Clean up backticks in case the AI ignores instructions
            $cleaned = str_replace(['```json', '```'], '', $response);
            $cleaned = trim($cleaned);

            $translatedArray = json_decode($cleaned, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($translatedArray)) {
                throw new Exception('AI returned invalid JSON: '.json_last_error_msg());
            }

            // Ensure all original keys exist
            foreach ($englishJson as $key => $value) {
                if (! isset($translatedArray[$key])) {
                    $translatedArray[$key] = $value; // Fallback to english if missing
                }
            }

            return $translatedArray;

        } catch (Exception $e) {
            Log::error('AI Translation Error: '.$e->getMessage());
            throw $e;
        }
    }

    protected function callOpenAi(string $prompt, ?string $imagePath = null): string
    {
        if (! $this->openaiKey) {
            throw new Exception('OpenAI API key is not configured.');
        }

        $messages = [];

        if ($imagePath && file_exists($imagePath)) {
            $mimeType = mime_content_type($imagePath);
            $base64 = base64_encode(file_get_contents($imagePath));

            $messages[] = [
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => $prompt],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => "data:{$mimeType};base64,{$base64}",
                        ],
                    ],
                ],
            ];
        } else {
            $messages[] = [
                'role' => 'user',
                'content' => $prompt,
            ];
        }

        $response = Http::timeout(60)->withToken($this->openaiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => $messages,
                'temperature' => 0.7,
            ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content') ?? '';
        }

        throw new Exception('OpenAI API Error: '.$response->body());
    }

    protected function callAnthropic(string $prompt, ?string $imagePath = null): string
    {
        if (! $this->anthropicKey) {
            throw new Exception('Anthropic API key is not configured.');
        }

        $content = [];
        if ($imagePath && file_exists($imagePath)) {
            $mimeType = mime_content_type($imagePath);
            $base64 = base64_encode(file_get_contents($imagePath));
            $content[] = [
                'type' => 'image',
                'source' => [
                    'type' => 'base64',
                    'media_type' => $mimeType,
                    'data' => $base64,
                ],
            ];
        }

        $content[] = [
            'type' => 'text',
            'text' => $prompt,
        ];

        $response = Http::timeout(60)->withHeaders([
            'x-api-key' => $this->anthropicKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-3-haiku-20240307',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $content],
            ],
        ]);

        if ($response->successful()) {
            return $response->json('content.0.text') ?? '';
        }

        throw new Exception('Anthropic API Error: '.$response->body());
    }
}
