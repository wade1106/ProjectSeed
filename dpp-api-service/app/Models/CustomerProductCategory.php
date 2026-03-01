<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerProductCategory extends Model
{
    use HasFactory, HasUuids;

    /**
     * 資料表名稱
     * * @var string
     */
    protected $table = 'customer_product_categories';

    /**
     * 指示 ID 是否為自動遞增
     * 由於使用 UUID，必須設為 false
     * * @var bool
     */
    public $incrementing = false;

    /**
     * 主鍵類型
     * * @var string
     */
    protected $keyType = 'string';

    /**
     * 可以批量分配的屬性
     * * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'category_id',
    ];

    /**
     * 是否自動維護時間戳記
     * 如果你的資料表沒有 created_at 和 updated_at，請設為 false
     * * @var bool
     */
    public $timestamps = true;

    /**
     * 關聯：所屬客戶
     * * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * 關聯：所屬產品類別
     * * @return BelongsTo
     */
    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id', 'id');
    }
}