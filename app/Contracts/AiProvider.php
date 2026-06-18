<?php

namespace App\Contracts;

interface AiProvider
{
    public function chat(array $messages): string;
    public function stream(array $messages): \Generator;
}
