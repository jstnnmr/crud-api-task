<?php

namespace App\Services\Ai;

use App\Contracts\AiProvider;

class AiProviderFactory
{
    public function make(?string $name = null): AiProvider
    {
        $name ??= config('ai.default');
        $config = config("ai.providers.{$name}");

        if (!$config || blank($config['key'] ?? null)) {
            $name = 'groq';
            $config = config('ai.providers.groq');
        }

        return match ($name) {
            'opencodego' => new OpenCodeGoProvider($config),
            'groq'       => new GroqProvider($config),
            default      => throw new \RuntimeException("Unknown AI provider: {$name}"),
        };
    }
}
