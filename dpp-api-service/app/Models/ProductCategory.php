<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductCategory extends Model
{
    use HasFactory, HasUuids;

    /**
     * 資料表名稱
     *
     * @var string
     */
    protected $table = 'product_categories';

    /**
     * 指示 ID 是否為自動遞增
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * 主鍵的類型
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 是否啟用時間戳記
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可以批量分配的屬性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'code', 'path', 'description', 'enable', 'idx'
    ];

    /**
     * 型別轉換
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enable' => 'boolean',
        'idx' => 'integer',
    ];

    /**
     * Get the HS prefixes for the product.
     *
     * @return HasMany
     */
    public function hsPrefixes()
    {
        return $this->hasMany(HsPrefix::class, 'category_id');
    }
}