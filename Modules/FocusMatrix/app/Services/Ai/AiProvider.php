<?php

namespace Modules\FocusMatrix\Services\Ai;

interface AiProvider
{
    public function chat(string $system, string $user, array $options = []): string;

    public function ping(): bool;

    public function name(): string;
}
