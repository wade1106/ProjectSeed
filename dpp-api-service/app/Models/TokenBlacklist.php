<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TokenBlacklist extends Model
{
    use HasUuids;

    protected $fillable = [
        'token',
        'user_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Check if a token is blacklisted
     */
    public static function isBlacklisted(string $tokenHash): bool
    {
        return self::where('token', $tokenHash)
            ->where('expires_at', '>', now())
            ->exists();
    }
}
