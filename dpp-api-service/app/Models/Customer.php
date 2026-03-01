<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'customers';

    protected $fillable = [
        'name', 'code', 'taxId', 'contact', 'email',
        'tel', 'address', 'start_date', 'end_date', 'enable',
        'notes', 'isDeleted', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'enable' => 'boolean',
        'isDeleted' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * 客戶與產品類別的多對多關係 (無時間戳記)
     */
    public function productCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductCategory::class,
            'customer_product_categories', // 中繼表名
            'customer_id',                 // 本表在中間表的外鍵
            'category_id'                  // 目標表在中間表的外鍵
        );
    }
}