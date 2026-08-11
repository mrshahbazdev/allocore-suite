<?php

namespace Modules\ClusterForge\Services\Contracts;

interface AiProvider
{
    public function name(): string;

    public function isConfigured(): bool;

    public function generateText(string $prompt, float $temperature = 0.7): string;

    /**
     * @return array<string, mixed>|array<int, mixed>
     */
    public function generateJson(string $prompt, float $temperature = 0.6): array;
}
