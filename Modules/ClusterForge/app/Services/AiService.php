<?php

namespace Modules\ClusterForge\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Log;
use Modules\ClusterForge\Services\Contracts\AiProvider;
use RuntimeException;

class AiService implements AiProvider
{
    protected string $provider;

    public function __construct()
    {
        $this->provider = (string) SiteSetting::value('ai_provider', config('services.ai.provider', 'gemini'));
    }

    public function name(): string
    {
        return $this->forProvider()->name();
    }

    public function isConfigured(): bool
    {
        if ($this->provider === 'auto') {
            return $this->gemini()->isConfigured()
                || $this->openai()->isConfigured()
                || $this->anthropic()->isConfigured();
        }

        return $this->forProvider()->isConfigured();
    }

    public function configuredProviderName(): ?string
    {
        if ($this->provider === 'auto') {
            foreach (['gemini', 'openai', 'anthropic'] as $name) {
                $provider = $this->providerInstance($name);
                if ($provider->isConfigured()) {
                    return $provider->name();
                }
            }

            return null;
        }

        $provider = $this->forProvider();

        return $provider->isConfigured() ? $provider->name() : null;
    }

    public function generateText(string $prompt, float $temperature = 0.7): string
    {
        return $this->callWithFallback(fn (AiProvider $provider) => $provider->generateText($prompt, $temperature));
    }

    /**
     * @return array<string, mixed>|array<int, mixed>
     */
    public function generateJson(string $prompt, float $temperature = 0.6): array
    {
        return $this->callWithFallback(fn (AiProvider $provider) => $provider->generateJson($prompt, $temperature));
    }

    /**
     * @return mixed
     */
    protected function callWithFallback(callable $callback)
    {
        $providers = $this->providerOrder();

        if (empty($providers)) {
            throw new RuntimeException('No AI provider is configured. Add an API key in Settings > AI.');
        }

        $lastException = null;

        foreach ($providers as $provider) {
            try {
                return $callback($provider);
            } catch (RuntimeException $e) {
                $lastException = $e;

                Log::warning('AI provider call failed, trying next', [
                    'provider' => $provider->name(),
                    'message' => $e->getMessage(),
                ]);
            }
        }

        throw new RuntimeException(
            'All configured AI providers failed. Last error: '.($lastException ? $lastException->getMessage() : 'unknown')
        );
    }

    /**
     * @return array<int, AiProvider>
     */
    protected function providerOrder(): array
    {
        $providers = match ($this->provider) {
            'openai' => [$this->openai()],
            'anthropic' => [$this->anthropic()],
            'gemini' => [$this->gemini()],
            'auto' => array_filter([
                $this->gemini()->isConfigured() ? $this->gemini() : null,
                $this->openai()->isConfigured() ? $this->openai() : null,
                $this->anthropic()->isConfigured() ? $this->anthropic() : null,
            ]),
            default => [$this->gemini()],
        };

        return array_values(array_filter($providers));
    }

    protected function forProvider(): AiProvider
    {
        return $this->providerInstance($this->provider);
    }

    protected function providerInstance(string $name): AiProvider
    {
        return match ($name) {
            'openai' => $this->openai(),
            'anthropic' => $this->anthropic(),
            default => $this->gemini(),
        };
    }

    protected function gemini(): GeminiService
    {
        return app(GeminiService::class);
    }

    protected function openai(): OpenAiService
    {
        return app(OpenAiService::class);
    }

    protected function anthropic(): AnthropicService
    {
        return app(AnthropicService::class);
    }
}
