<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRole extends Model
{
    /**
     * 與此模型關聯的資料表
     *
     * @var string
     */
    protected $table = 'user_roles';

    /**
     * 指示模型主鍵是否遞增
     * 由於使用 UUID，必須設為 false
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * 自動遞增 ID 的資料類型
     * 由於使用 UUID，需設為 string
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 可以被批量賦值的屬性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'role_id',
    ];

    /**
     * 取得擁有此角色關聯的使用者
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 取得此關聯對應的角色
     *
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}