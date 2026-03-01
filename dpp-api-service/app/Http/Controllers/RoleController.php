<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Module;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Get all roles for the authenticated user's company with pagination and filters
     * Query parameters:
     * - keyword: Search in role name and description
     * - enable: Filter by status (true/false/null)
     * - customer_id: Filter by company (optional, will be overridden by auth user)
     * - page: Page number (default: 1)
     * - per_page: Items per page (default: 15)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // 獲取查詢參數
            $customerId = $request->input('customer_id');
            $keyword = $request->input('keyword');
            $enable = $request->has('enable') ? $request->boolean('enable') : null;
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 15);

            // 使用RoleService獲取角色列表
            $result = $this->roleService->getRoles(
                $customerId,
                $keyword,
                $enable,
                $page,
                $perPage
            );

            return response()->json($result);

        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (BadRequestHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        } catch (\Exception $e) {
            Log::error('Get roles error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve roles'
            ], 500);
        }
    }

    /**
     * Get a specific role
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->get('jwt_user');
            $customerId = $user->customer_id ?? null;

            if (!$customerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer ID not found'
                ], 400);
            }

            $role = $this->roleService->getRoleById($customerId, $id);

            return response()->json([
                'success' => true,
                'data' => $role
            ]);

        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            Log::error('Get role error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve role'
            ], 500);
        }
    }

    /**
     * Create a new role
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|string|size:36|exists:customers,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'enable' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->get('jwt_user');
            
            // 調用 Service 建立角色
            $role = $this->roleService->createRole($user->id, $validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'data' => $role
            ], 201);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to create role');
        }
    }

    /**
     * Update a role
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|string|size:36|exists:customers,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'enable' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->get('jwt_user');
            
            // 調用 Service 更新角色 (Service 內已包含 findOrFail 與 isDefault 檢查)
            $role = $this->roleService->updateRole($user->id, $id, $validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'data' => $role
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to update role');
        }
    }

    /**
     * Delete a role
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            // 將 ID 轉為整數並調用 Service
            // Service 內部已實作：
            // 1. findOrFail ($id)
            // 2. isDefault 檢查
            // 3. permissions->exists() 檢查 (有資料不能刪除)
            $this->roleService->deleteRole($id);

            return response()->json([
                'success' => true,
                'message' => '角色已成功刪除'
            ]);

        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => '找不到該角色'
            ], 404);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() // 例如：Cannot delete default role
            ], 403);
        } catch (\Exception $e) {
            // 這裡會捕獲到 Service 拋出的「有配置權限，無法刪除」錯誤
            Log::error('Delete role error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: '刪除角色失敗'
            ], 400); // 這裡通常建議回傳 400 (Bad Request) 或 409 (Conflict)
        }
    }

    /**
     * Get role permissions - return array of permission IDs for a specific role
     */
    public function getRolePermissions(Request $request, string $roleId): JsonResponse
    {
        try {
            $user = $request->get('jwt_user');
            /*
            $customerId = $user->customer_id ?? null;

            if (!$customerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer ID not found'
                ], 400);
            }
            */

            // 驗證角色是否存在且屬於該公司
            $role = Role::where('id', $roleId)
                //->where('customer_id', $customerId)
                ->first();

            if (!$role) {
                throw new NotFoundHttpException('角色不存在或無存取權限');
            }

            // 獲取該角色的所有權限 ID
            $permissionIds = $role->permissions()
                ->pluck('permissions.id')
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => $permissionIds
            ]);

        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch(\Exception $e) {
            Log::error('Get role permissions error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve role permissions'
            ], 500);
        }
    }

    /**
     * Get all available permissions organized by modules
     */
    public function getPermissionsByModules(Request $request): JsonResponse
    {
        try {
            // 直接調用 Service 獲取模組與權限清單
            $result = $this->roleService->getPermissionsByModules();

            return response()->json($result);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to retrieve permissions');
        }
    }

    /**
     * Update role permissions
     */
    public function updateRolePermissions(Request $request, string $roleId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'permission_ids'   => 'required|array',
            'permission_ids.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            //$user = $request->get('jwt_user');
            // 調用 Service 更新權限 (ID 轉為整數以符合 Service 定義)
            $role = $this->roleService->updateRolePermissions(
                $roleId,
                $request->permission_ids
            );

            return response()->json([
                'success' => true,
                'message' => 'Role permissions updated successfully',
                'data'    => $role
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to update role permissions');
        }
    }

    /**
     * 統一處理異常回傳
     * * @param \Exception $e
     * @param string $defaultMessage
     * @return JsonResponse
     */
    private function handleException(\Exception $e, string $defaultMessage): JsonResponse
    {
        // 1. 處理 Symfony 的 HttpExceptions (NotFound, AccessDenied, BadRequest 等)
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }

        // 2. 處理 Laravel 的 ModelNotFoundException (由 findOrFail 拋出)
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => '找不到該筆資料'
            ], 404);
        }

        // 3. 處理一般的 Exception (例如 Service 拋出的「有資料不能刪除」)
        Log::error($defaultMessage . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage() ?: $defaultMessage
        ], 500);
    }
}