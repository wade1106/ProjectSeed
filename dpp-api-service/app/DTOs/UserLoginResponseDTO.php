<?php

namespace App\DTOs;

readonly class UserLoginResponseDTO extends LoginResponseDTO
{
    public function __construct(
        bool $success,
        string $token,
        string $uid,
        string $account,
        array $features = []
    ) {
        parent::__construct(
            $success,
            $token,
            [
                'uid' => $uid,
                'account' => $account,
                'role' => 'user',
                'features' => $features,
            ]
        );
    }
}