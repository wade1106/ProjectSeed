<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Locale extends Model
{
    use HasFactory;

    /**
     * 與此模型關聯的資料表
     * * @var string
     */
    protected $table = 'locales';

    /**
     * 指示模型主鍵是否遞增
     * 由於 Id 是手動指定的特定數值 (1, 2, 3)，故設為 false
     * * @var bool
     */
    public $incrementing = false;

    /**
     * 指示是否自動處理時間戳
     * 原本 SQL Server 結構中沒有 createdAt/updatedAt，若不需要請設為 false
     * * @var bool
     */
    public $timestamps = false;

    /**
     * 可以被批量賦值的屬性
     * * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'code',
        'name',
        'isEnable',
        'isDefault',
    ];

    /**
     * 屬性類型轉換
     * * @var array<string, string>
     */
    protected $casts = [
        'isEnable' => 'boolean',
        'isDefault' => 'boolean',
    ];

    /**
     * 取得預設語系的靜態方法
     * * @return self|null
     */
    public static function getDefault(): ?self
    {
        return self::where('isDefault', true)->first();
    }
}