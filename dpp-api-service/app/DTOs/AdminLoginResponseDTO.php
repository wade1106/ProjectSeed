<?php

namespace App\DTOs;

readonly class AdminLoginResponseDTO extends LoginResponseDTO
{
    public function __construct(
        bool $success,
        string $token,
        string $uid,
        string $account,
        array $permissions = []
    ) {
        parent::__construct(
            $success,
            $token,
            [
                'uid' => $uid,
                'account' => $account,
                'role' => 'admin',
                'permissions' => $permissions,
            ]
        );
    }
}