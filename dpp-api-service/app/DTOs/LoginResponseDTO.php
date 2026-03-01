<?php

namespace App\DTOs;

readonly class LoginResponseDTO
{
    public function __construct(
        public bool $success,
        public string $token,
        public array $data
    ) {}

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'token' => $this->token,
            'data' => $this->data,
        ];
    }
}