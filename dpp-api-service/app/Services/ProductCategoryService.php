<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\ProductCategory;
use App\Models\CustomerProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductCategoryService
{
    /**
     * Get products categories with optional filters
     */
    public function getProductCategories($keyword = null, $enable = null, $page = 1, $perPage = 15)
    {
        $query = ProductCategory::query()
            ->with(['hsPrefixes' => function ($query) {
                $query->select('id', 'category_id', 'prefix', 'description')
                     ->orderBy('id', 'asc');
            }])
            ->select('id', 'name', 'code', 'path', 'description', 'enable', 'idx')
            ->orderBy('idx', 'asc');

        if ($keyword !== null) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('code', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if ($enable !== null) {
            $query->where('enable', $enable);
        }

        $productCategories = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'success' => true,
            'data' => $productCategories->items(),
            'meta' => [
                'current_page' => $productCategories->currentPage(),
                'last_page' => $productCategories->lastPage(),
                'per_page' => $productCategories->perPage(),
                'total' => $productCategories->total(),
                'from' => $productCategories->firstItem(),
                'to' => $productCategories->lastItem(),
            ]
        ];
    }

    /**
     * Get specific product by ID
     */
    public function getProductCategoryById(string $id)
    {
        $product = ProductCategory::where('id', $id)
            ->withCount('hsPrefixes')
            ->first();

        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        return $product;
    }

    /**
     * Create new product category
     */
    public function createProductCategory(array $data)
    {
        DB::beginTransaction();
        try {
            // Generate code if not provided
            if (empty($data['code'])) {
                $data['code'] = 'P' . str_pad(
                    ProductCategory::where('code', 'like', 'P%')->count() + 1,
                    4,
                    '0',
                    STR_PAD_LEFT
                );
            }

            // Set default index if not provided
            if (!isset($data['idx'])) {
                $data['idx'] = 0;
            }

            // Set default enabled if not provided
            if (!isset($data['enable'])) {
                $data['enable'] = true;
            }

            $productCategory = ProductCategory::create($data);

            /*
            // Create at least one default HS prefix if none exists
            if (!isset($data['prefixes'])) {
                HsPrefix::create([
                    'category_id' => $productCategory->id,
                    'prefix' => '',
                    'description' => '',
                    'enable' => true,
                    'idx' => 0
                ]);
            }
            */

            DB::commit();
            return $productCategory;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create product service error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update product category
     */
    public function updateProductCategory(string $id, array $data)
    {
        $productCategory = ProductCategory::findOrFail($id);
        if (!$productCategory) {
            throw new NotFoundHttpException('Product not found');
        }

        DB::beginTransaction();
        try {
            $productCategory->update($data);
            DB::commit();
            return $productCategory;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update product category service error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete product category
     */
    public function deleteProductCategory(string $id)
    {
        $productCategory = ProductCategory::findOrFail($id);
        if (!$productCategory) {
            throw new NotFoundHttpException('Product category not found');
        }

        // Check if product has any HS prefixes that might be in use
        /*
        if ($productCategory->hsPrefixes()->exists()) {
            throw new BadRequestHttpException('Cannot delete product category with active HS prefixes');
        }
        */

        DB::beginTransaction();
        try {
            // Delete all associated HS prefixes first
            $productCategory->hsPrefixes()->delete();

            // Delete the product category
            $productCategory->delete();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete product category service error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get HS prefixes for specific product
     */
    public function getHsPrefixesByProductCategory(string $id)
    {
        $productCategory = ProductCategory::findOrFail($id);
        if (!$productCategory) {
            throw new NotFoundHttpException('Product not found');
        }

        return $productCategory->hsPrefixes()
            ->orderBy('prefix', 'asc')->get();
    }

    /**
     * Update HS prefixes for specific product
     */
    public function updateHsPrefixes(string $categoryId, array $prefixes)
    {
        $productCategory = ProductCategory::findOrFail($categoryId);
        if (!$productCategory) {
            throw new NotFoundHttpException('Product category not found');
        }

        DB::beginTransaction();
        try {
            // Delete existing prefixes
            $productCategory->hsPrefixes()->delete();

            // Create new prefixes
            foreach ($prefixes as $index => $prefixData) {
                $productCategory->hsPrefixes()->create([
                    'category_id' => $categoryId,
                    'prefix' => trim($prefixData['prefix']),
                    'description' => $prefixData['description'] ?? ''
                ]);
            }

            DB::commit();
            return $this->getHsPrefixesByProductCategory($categoryId);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update HS prefixes service error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get customer product categories
     */
    public function getCustomerProductCategories(string $customerId)
    {
        $categories = CustomerProductCategory::where('customer_id', $customerId)
            ->with('productCategory:id,name,code')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'customer_id' => $item->customer_id,
                    'category_id' => $item->category_id,
                    'category_name' => $item->productCategory ? $item->productCategory->name : null,
                    'category_code' => $item->productCategory ? $item->productCategory->code : null,
                ];
            });

        return $categories;
    }

    /**
     * 更新客戶的產品類別清單
     * * @param string $customerId
     * @param array $categoryIds
     * @return bool
     * @throws \Exception
     */
    public function updateCustomerProductCategories(string $customerId, array $categoryIds): bool
    {
        // 1. 檢查客戶是否存在 (對應 Controller 的 exists 驗證邏輯)
        $customer = Customer::findOrFail($customerId);
        if (!$customer) {
            throw new NotFoundHttpException('Customer not found');
        }

        DB::beginTransaction();
        try {
            // 使用 Eloquent 關係進行同步
            // sync 會自動處理：多餘的刪除、缺失的新增、存在的保持不變
            $customer->productCategories()->sync($categoryIds);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update customer product categories service error: ' . $e->getMessage());
            throw $e; // 拋出給 Controller 捕捉
        }
    }

}