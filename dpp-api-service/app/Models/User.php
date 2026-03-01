<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'name',
        'email',
        'account',
        'password',
        'enable',
        'avatar',
        'isDeleted',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'enable' => 'boolean',
        'isDeleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 啟用自定義軟刪除的查詢作用域
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('isDeleted', false);
    }

    /**
     * 只查詢 active 的用戶
     */
    public function scopeActive($query)
    {
        return $query->where('enable', true)
                   ->where('isDeleted', false);
    }

    /**
     * 客戶關聯
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * 建立者關聯
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * 更新者關聯
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * 自定義刪除方法（使用 isDeleted 標記）
     */
    public function markAsDeleted(): bool
    {
        $this->isDeleted = true;
        $this->updated_by = Auth::id() ?? null;
        return $this->save();
    }

    /**
     * 恢復刪除的用戶
     */
    public function restore(): bool
    {
        $this->isDeleted = false;
        $this->updated_by = Auth::id() ?? null;
        return $this->save();
    }

    /**
     * 取得該用戶擁有的角色
     * 一個用戶可以有多個角色 (Many-to-Many)
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,            // 目標模型
            'user_roles',           // 中繼表名稱
            'user_id',              // 本模型在中間表的外鍵
            'role_id'               // 目標模型在中間表的外鍵
        )->withTimestamps();        // 因為你的 user_roles 有 created_at 和 updated_at
    }

    /**
     * Get all permissions for the user
     */
    public function getAllPermissions()
    {
        if ($this->roles->isEmpty()) {
            return [];
        }

        $allPermissions = [];
        foreach ($this->roles as $role) {
            $permissions = $role->getPermissions();
            foreach ($permissions as $module => $perms) {
                if (!isset($allPermissions[$module])) {
                    $allPermissions[$module] = [];
                }
                $allPermissions[$module] = array_unique(array_merge($allPermissions[$module], $perms));
            }
        }

        return $allPermissions;
    }
}