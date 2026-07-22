<?php

namespace App\Services;

use App\Models\User;

class ModuleGate
{
    public function missingFor(User $user, array $moduleKeys): array
    {
        return array_values(array_filter($moduleKeys, fn (string $key) => ! $user->hasModule($key)));
    }

    public function hasAll(User $user, array $moduleKeys): bool
    {
        return empty($this->missingFor($user, $moduleKeys));
    }

    public function requiredForAnalysis(string $analysis): array
    {
        return match ($analysis) {
            'deep-kpis' => ['invoice-maker', 'lead-quality', 'audit'],
            'financial-health' => ['invoice-maker', 'financial-platform'],
            'lead-conversion' => ['lead-quality', 'invoice-maker'],
            'customer-lifetime' => ['invoice-maker'],
            default => [],
        };
    }
}
