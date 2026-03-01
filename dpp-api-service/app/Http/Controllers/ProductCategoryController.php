<?php

namespace App\Http\Controllers;

use App\Services\ProductCategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Exception;

class ProductCategoryController extends Controller
{
    protected ProductCategoryService $productCategoryService;

    public function __construct(ProductCategoryService $productCategoryService)
    {
        $this->productCategoryService = $productCategoryService;
    }

    /**
     * Get all product categories with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $keyword = $request->input('keyword');
            $enable = $request->has('enable') ? $request->boolean('enable') : null;
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 15);

            $productCategories = $this->productCategoryService->getProductCategories(
                $keyword,
                $enable,
                $page,
                $perPage
            );

            return response()->json($productCategories);

        } catch (Exception $e) {
            Log::error('Get product categories error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product categories'
            ], 500);
        }
    }

    /**
     * Get specific product category with HS prefixes
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $productCategory = $this->productCategoryService->getProductCategoryById($id);

            return response()->json([
                'success' => true,
                'data' => $productCategory
            ]);

        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product category not found'
            ], 404);
        } catch (Exception $e) {
            Log::error('Get product category error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product category'
            ], 500);
        }
    }

    /**
     * Create new product category
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:50',
            'path' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
            'enable' => 'boolean',
            'idx' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $productCategory = $this->productCategoryService->createProductCategory($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Product category created successfully',
                'data' => $productCategory
            ], 201);

        } catch (Exception $e) {
            Log::error('Create product category error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product category'
            ], 500);
        }
    }

    /**
     * Update product category
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:50',
            'path' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
            'enable' => 'boolean',
            'idx' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $productCategory = $this->productCategoryService->updateProductCategory($id, $validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Product category updated successfully',
                'data' => $productCategory
            ]);

        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product category not found'
            ], 404);
        } catch (Exception $e) {
            Log::error('Update product category error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product category'
            ], 500);
        }
    }

    /**
     * Delete product category
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->productCategoryService->deleteProductCategory($id);

            return response()->json([
                'success' => true,
                'message' => 'Product category deleted successfully'
            ]);

        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product category not found'
            ], 404);
        } catch (Exception $e) {
            Log::error('Delete product category error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to delete product category'
            ], 500);
        }
    }

    /**
     * Get HS prefixes for specific product
     */
    public function getHsPrefixes(Request $request, string $id): JsonResponse
    {
        try {
            $prefixes = $this->productCategoryService->getHsPrefixesByProductCategory($id);

            return response()->json([
                'success' => true,
                'data' => $prefixes
            ]);

        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product category not found'
            ], 404);
        } catch (Exception $e) {
            Log::error('Get HS prefixes error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve HS prefixes'
            ], 500);
        }
    }

    /**
     * Update HS prefixes for specific product
     */
    public function updateHsPrefixes(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prefixes' => 'required|array',
            'prefixes.*.prefix' => 'required|string|max:50',
            'prefixes.*.description' => 'nullable|string|max:500',
            'prefixes.*.enable' => 'sometimes|boolean',
            'prefixes.*.idx' => 'sometimes|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $prefixes = $this->productCategoryService->updateHsPrefixes($id, $request->prefixes);

            return response()->json([
                'success' => true,
                'message' => 'HS prefixes updated successfully',
                'data' => $prefixes
            ]);

        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product category not found'
            ], 404);
        } catch (Exception $e) {
            Log::error('Update HS prefixes error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update HS prefixes'
            ], 500);
        }
    }

    /**
     * Get customer product categories
     */
    public function getCustomerProductCategories(Request $request): JsonResponse
    {
        try {
            // 從 Request 中取得前端傳來的 customer_id
            $customerId = $request->query('customerId');
            $customerCategories = $this->productCategoryService->getCustomerProductCategories($customerId);

            return response()->json([
                'success' => true,
                'data' => $customerCategories
            ]);

        } catch (Exception $e) {
            Log::error('Get customer product categories error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customer product categories'
            ], 500);
        }
    }

    /**
     * 更新客戶與產品類別的關聯
     * PUT /api/v1/productCategory/customerProductCategories
     */
    public function updateCustomerProductCategories(Request $request): JsonResponse
    {
        // 1. 驗證 Payload
        $validated = $request->validate([
            'customerId'    => 'required|string|exists:customers,id',
            'categoryIds'   => 'present|array',
            'categoryIds.*' => 'string|exists:product_categories,id',
        ]);

        try {
            // 2. 調用 Service 處理商業邏輯
            // Service 內部已處理 Transaction 與 Log，這裡直接執行
            $this->productCategoryService->updateCustomerProductCategories(
                $validated['customerId'],
                $validated['categoryIds']
            );

            return response()->json([
                'success' => true,
                'message' => '客戶產品類別權限更新成功',
            ]);

        } catch (NotFoundHttpException $e) {
            // 捕捉 Service 丟出的 404 錯誤
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            // 捕捉其他未預期的系統錯誤 (500)
            return response()->json([
                'success' => false,
                'message' => '更新失敗，請聯繫系統管理員',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}