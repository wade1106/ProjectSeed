<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Module;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RoleService
{
    /**
     * 獲取角色列表（支援搜尋、篩選、分頁）
     *
     * @param int $customerId 客戶ID
     * @param string|null $keyword 關鍵字搜尋（名稱、描述）
     * @param bool|null $enable 狀態篩選
     * @param int $page 頁碼
     * @param int $perPage 每頁筆數
     * @return array
     */
    public function getRoles(
        ?string $customerId,
        ?string $keyword = null,
        ?bool $enable = null,
        int $page = 1,
        int $perPage = 15
    ): array {
        try {
            $query = Role::with(['permissions' => function($query) {
                $query->select('permissions.id', 'permissions.code', 'permissions.name', 'permissions.module_id')
                      ->with(['module' => function($q) {
                          $q->select('id', 'code', 'name');
                      }]);
            }])
            ->where('customer_id', $customerId);

            // 關鍵字搜尋
            if ($keyword) {
                $query->where(function (Builder $subQuery) use ($keyword) {
                    $subQuery->where('name', 'like', '%' . $keyword . '%')
                              ->orWhere('description', 'like', '%' . $keyword . '%');
                });
            }

            // 狀態篩選
            if ($enable !== null) {
                $query->where('enable', $enable);
            }

            // 分頁查詢
            $roles = $query->orderBy('name')
                          ->orderBy('created_at', 'desc')
                          ->paginate($perPage, ['*'], 'page', $page);

            return [
                'success' => true,
                'data' => $roles->items(),
                'pagination' => [
                    'current_page' => $roles->currentPage(),
                    'last_page' => $roles->lastPage(),
                    'per_page' => $roles->perPage(),
                    'total' => $roles->total(),
                ]
            ];

        } catch (\Exception $e) {
            Log::error('RoleService getRoles error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 獲取單一角色
     *
     * @param int $customerId 客戶ID
     * @param int $roleId 角色ID
     * @return Role
     * @throws NotFoundHttpException
     */
    public function getRoleById(int $customerId, int $roleId): Role
    {
        try {
            $role = Role::with(['permissions' => function($query) {
                $query->select('permissions.id', 'permissions.code', 'permissions.name', 'permissions.description', 'permissions.module_id')
                      ->with(['module' => function($q) {
                          $q->select('id', 'code', 'name');
                      }]);
            }])
            ->where('customer_id', $customerId)
            ->find($roleId);

            if (!$role) {
                throw new NotFoundHttpException('Role not found');
            }

            return $role;

        } catch (\Exception $e) {
            Log::error('RoleService getRoleById error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 建立新角色
     *
     * @param int $customerId 客戶ID
     * @param int $userId 使用者ID
     * @param array $data 角色資料
     * @return Role
     */
    public function createRole(string $userId, array $data): Role
    {
        try {
            DB::beginTransaction();

            // Create role
            $role = Role::create([
                'customer_id' => $data['customer_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'enable' => $data['enable'],
                'isDefault' => false,
                'created_by' => $userId,
                'updated_by' => $userId
            ]);

            // Attach permissions
            /*
            if (isset($data['permission_ids']) && !empty($data['permission_ids'])) {
                $role->permissions()->attach($data['permission_ids']);
            }
            */

            DB::commit();

            // Load permissions with modules
            $role->load(['permissions' => function($query) {
                $query->select('permissions.id', 'permissions.code', 'permissions.name', 'permissions.module_id')
                      ->with(['module' => function($q) {
                          $q->select('id', 'code', 'name');
                      }]);
            }]);

            return $role;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RoleService createRole error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 更新角色
     *
     * @param int $customerId 客戶ID
     * @param int $userId 使用者ID
     * @param int $roleId 角色ID
     * @param array $data 更新資料
     * @return Role
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     */
    public function updateRole(string $userId, string $roleId, array $data): Role
    {
        try {
            $role = Role::findOrFail($roleId);

            if (!$role) {
                throw new NotFoundHttpException('Role not found');
            }

            // Prevent updating default roles
            /*
            if ($role->isDefault) {
                throw new AccessDeniedHttpException('Cannot modify default role');
            }
            */

            DB::beginTransaction();

            // Update role data
            $updateData = array_intersect_key($data, array_flip(['customer_id', 'name', 'description', 'enable']));
            $updateData['updated_by'] = $userId;

            $role->update($updateData);

            DB::commit();

            // Load updated permissions
            $role->load(['permissions' => function($query) {
                $query->select('permissions.id', 'permissions.code', 'permissions.name', 'permissions.module_id')
                      ->with(['module' => function($q) {
                          $q->select('id', 'code', 'name');
                      }]);
            }]);

            return $role;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RoleService updateRole error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 刪除角色
     *
     * @param int $customerId 客戶ID
     * @param int $roleId 角色ID
     * @return bool
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     */
    public function deleteRole(string $roleId): bool
    {
        try {
            // findOrFail 找不到會自動丟出 404，無需手動判斷 ! $role
            $role = Role::findOrFail($roleId);

            // 1. 禁止刪除預設角色
            if ($role->isDefault) {
                throw new AccessDeniedHttpException('Cannot delete default role');
            }

            // 2. 核心檢查：若角色尚有關聯權限，禁止刪除
            // 使用 exists() 效能比 count() 更好，只要找到一筆就回傳 true
            if ($role->permissions()->exists()) {
                throw new \Exception('此角色尚有配置權限，無法刪除。請先移除所有權限設定。');
                // 或者使用自定義的 HttpException
                // throw new ConflictHttpException('Cannot delete role with assigned permissions');
            }

            DB::beginTransaction();

            // 雖然前面檢查過 exists()，但保險起見仍執行 detach (視業務需求而定)
            // 如果你希望「有資料就報錯」，這裡其實已經不會被執行到了
            $role->permissions()->detach();

            $result = $role->delete();

            DB::commit();

            return $result;

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error("RoleService deleteRole error [ID: {$roleId}]: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 獲取所有可用的權限（按模組分類）
     */
    public function getPermissionsByModules(): array
    {
        try {
            $modules = Module::where('enable', true)
                ->with(['permissions' => function($query) {
                    $query->where('enable', true)
                          ->select('id', 'module_id', 'code', 'name', 'description');
                }])
                ->orderBy('idx')
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'description']);

            return [
                'success' => true,
                'data' => $modules
            ];

        } catch (\Exception $e) {
            Log::error('RoleService getPermissionsByModules error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 更新角色權限
     *
     * @param int $customerId 客戶ID
     * @param int $roleId 角色ID
     * @param array $permissionIds 權限ID陣列
     * @return Role
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     */
    public function updateRolePermissions(string $roleId, array $permissionIds): Role
    {
        try {
            $role = Role::findOrFail($roleId);

            if (!$role) {
                throw new NotFoundHttpException('Role not found');
            }

            // Prevent updating default role permissions
            /*
            if ($role->isDefault) {
                throw new AccessDeniedHttpException('Cannot modify default role permissions');
            }
            */

            $role->permissions()->sync($permissionIds);

            // Load updated permissions
            $role->load(['permissions' => function($query) {
                $query->select('permissions.id', 'permissions.code', 'permissions.name', 'permissions.module_id')
                      ->with(['module' => function($q) {
                          $q->select('id', 'code', 'name');
                      }]);
            }]);

            return $role;

        } catch (\Exception $e) {
            Log::error('RoleService updateRolePermissions error: ' . $e->getMessage());
            throw $e;
        }
    }
}