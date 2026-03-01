<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Admin extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $table = 'admins';

    protected $fillable = [
        'name', 'email', 'account', 'password', 
        'enable', 'avatar', 'isDefault', 'isDeleted',
        'created_by', 'updated_by'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'enable' => 'boolean',
        'isDefault' => 'boolean',
        'isDeleted' => 'boolean',
    ];

    /**
     * 建立者關聯：Admin 屬於一個 User
     */
    public function creator(): BelongsTo
    {
        // 第一個參數是目標模型，第二個是 Admin 表裡面的外鍵欄位
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 更新者關聯：Admin 屬於一個 User
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}