<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtService
{
    private readonly string $secret;
    private readonly string $algorithm;
    private readonly int $expiryMinutes;

    public function __construct()
    {
        $this->secret = config('app.jwt_secret', env('JWT_SECRET', 'your-default-secret-key'));
        $this->algorithm = 'HS256';
        $this->expiryMinutes = 30;
    }

    public function generateToken(array $payload): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + ($this->expiryMinutes * 60);

        $payload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
        ]);

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function verifyToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return (array) $decoded;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getExpiryMinutes(): int
    {
        return $this->expiryMinutes;
    }
}