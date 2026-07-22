<?php

namespace Modules\FocusMatrix\Services\Ai;

use App\Models\User;
use Modules\FocusMatrix\Models\AiSetting;
use RuntimeException;

class AiManager
{
    public function for(User $user): ?AiProvider
    {
        $setting = AiSetting::where('user_id', $user->id)->first();
        if (! $setting || ! $setting->enabled || ! $setting->hasKey()) {
            return null;
        }
        if ($setting->remainingQuota() <= 0) {
            return null;
        }

        return $this->makeProvider($setting->provider, $setting->getApiKey(), $setting->model ?? AiSetting::DEFAULT_MODELS[$setting->provider] ?? 'gemini-2.0-flash');
    }

    public function makeProvider(string $provider, string $apiKey, ?string $model = null): AiProvider
    {
        $model = $model ?? (AiSetting::DEFAULT_MODELS[$provider] ?? 'gemini-2.0-flash');

        return match ($provider) {
            'gemini' => new GeminiProvider($apiKey, $model),
            'openai' => new OpenAiProvider($apiKey, $model),
            'anthropic' => new AnthropicProvider($apiKey, $model),
            default => throw new RuntimeException("Unknown AI provider: {$provider}"),
        };
    }

    public function promptFor(User $user, string $system, string $user_msg, array $options = []): ?string
    {
        $setting = AiSetting::where('user_id', $user->id)->first();
        if (! $setting || ! $setting->enabled || ! $setting->hasKey()) {
            return null;
        }
        if ($setting->remainingQuota() <= 0) {
            return null;
        }

        $provider = $this->makeProvider($setting->provider, $setting->getApiKey(), $setting->model ?? AiSetting::DEFAULT_MODELS[$setting->provider] ?? 'gemini-2.0-flash');

        try {
            $out = $provider->chat($system, $user_msg, $options);
            $setting->registerCall();

            return $out;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    public function promptJsonFor(User $user, string $system, string $user_msg): ?array
    {
        $raw = $this->promptFor($user, $system, $user_msg, ['json' => true]);
        if (! $raw) {
            return null;
        }
        $raw = preg_replace('/^```json\s*|\s*```$/m', '', $raw);
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }
}
