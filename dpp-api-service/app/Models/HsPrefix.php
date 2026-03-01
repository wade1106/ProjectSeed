<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HsPrefix extends Model
{
    use HasFactory, HasUuids;

    /**
     * 資料表名稱
     *
     * @var string
     */
    protected $table = 'hs_prefixs';

    public $timestamps = false;

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
     * 可以批量分配的屬性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'prefix',
        'description',
    ];

    /**
     * 關聯：所屬產品
     * * @return BelongsTo
     */
    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id', 'id');
    }
}