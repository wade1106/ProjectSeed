<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    // 此表為自增 ID (BIGINT UNSIGNED)，不需 HasUuids
    protected $table = 'modules';
    public $timestamps = false; // 根據 SQL 定義，此表無時間戳欄位

    protected $fillable = [
        'code', 'name', 'idx', 'description', 'enable'
    ];

    protected $casts = [
        'enable' => 'boolean',
        'idx' => 'integer',
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }
}