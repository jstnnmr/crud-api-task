<?php

namespace App\Support;

class ServiceReturn
{
    public function __construct(
        public bool $success,
        public mixed $data = null,
        public ?string $message = null,
        public int $status = 200
    ) {}

    // Ensure the parameter names here match your AuthService call
    public static function success(mixed $data = null, ?string $message = null, int $status = 200): self
    {
        return new self(success: true, data: $data, message: $message, status: $status);
    }

    public static function error(string $message, int $status = 400): self
    {
        return new self(success: false, message: $message, status: $status);
    }
}