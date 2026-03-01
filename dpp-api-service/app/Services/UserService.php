<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Utils\MailUtil;
use App\Services\CustomerService;

class UserService
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * 獲取用戶列表，支持分頁和過濾
     * 修正：加入 with(['roles']) 確保列表資料包含角色
     */
    public function getUsers(array $filters = [])
    {
        // 這裡必須加上 roles 關聯，否則前端列表傳入 Modal 的 userData 就沒有 roles 欄位
        $query = User::with(['customer:id,name', 'roles:id,name']) 
                     ->notDeleted()
                     ->select([
                         'users.id',
                         'users.customer_id',
                         'users.name',
                         'users.email',
                         'users.account',
                         'users.enable',
                         'users.avatar',
                         'users.isDeleted',
                         'users.created_at',
                         'users.updated_at',
                         'users.created_by',
                         'users.updated_by'
                     ]);

        if (isset($filters['customer_id']) && !empty($filters['customer_id'])) {
            $query->where('users.customer_id', $filters['customer_id']);
        }

        if (isset($filters['keyword']) && !empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('users.name', 'like', "%{$keyword}%")
                ->orWhere('users.account', 'like', "%{$keyword}%")
                ->orWhere('users.email', 'like', "%{$keyword}%");
            });
        }

        if (isset($filters['enable'])) {
            $query->where('users.enable', (bool) $filters['enable']);
        }

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    /**
     * 根據 ID 獲取單個用戶
     * 修正：加入 with(['roles'])
     */
    public function getUserById(string $id): User
    {
        // 確保單筆查詢也包含角色
        return User::with(['customer:id,name', 'roles:id,name'])
                    ->notDeleted()
                    ->findOrFail($id);
    }

    /**
     * 新增用戶與角色關聯
     */
    public function createUser(array $data): User
    {
        $validated = $this->validateUserData($data);
        
        return DB::transaction(function () use ($validated, $data) {
            $password = $validated['password'];

            $user = User::create([
                'customer_id' => $validated['customer_id'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'account' => $validated['account'],
                'password' => $password, 
                'enable' => false,
                'avatar' => $validated['avatar'] ?? null,
                'isDeleted' => false,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            if (isset($data['role_ids']) && is_array($data['role_ids'])) {
                $user->roles()->sync($data['role_ids']);
            }

            try {
                $customer = $this->customerService->getCustomerById($user->customer_id);
                MailUtil::sendHtml(
                    [$user->email],
                    ['service@carbontact.com', 'wade.chung@carbontact.com', 'lili.liao@carbontact.com'],
                    '【Digital Product Passport】帳號啟用： '.$user->name.'您好',
                    MailUtil::prepareActivationEmail($customer->code, $user->account, $password)
                );
            } catch (\Exception $e) {
                Log::error("Email sending failed for user {$user->id}: " . $e->getMessage());
            }

            // 確保回傳包含角色 ID
            return $user->load('roles:id,name');
        });
    }

    /**
     * 更新用戶與角色關聯
     */
    public function updateUser(string $id, array $data): User
    {
        $user = User::notDeleted()->findOrFail($id);
        $validated = $this->validateUserData($data, $user->id, false);

        return DB::transaction(function () use ($user, $validated, $data) {
            $updateData = [
                'customer_id' => $validated['customer_id'] ?? $user->customer_id,
                'name' => $validated['name'] ?? $user->name,
                'email' => $validated['email'] ?? $user->email,
                'account' => $validated['account'] ?? $user->account,
                'enable' => $validated['enable'] ?? $user->enable,
                'avatar' => $validated['avatar'] ?? $user->avatar,
                'updated_by' => Auth::id(),
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = $validated['password'];
            }

            $user->update($updateData);

            if (isset($data['role_ids']) && is_array($data['role_ids'])) {
                $user->roles()->sync($data['role_ids']);
            }

            // fresh 必須包含 roles，前端才會更新視覺
            return $user->fresh(['roles:id,name', 'customer:id,name']);
        });
    }

    /**
     * 軟刪除用戶
     */
    public function deleteUser(string $id){
        $user = User::notDeleted()->findOrFail($id);
        $user->update([
            'isDeleted' => true,
            'updated_by' => Auth::id(),
        ]);
    }

    /**
     * 驗證用戶資料
     */
    private function validateUserData(array $data, string $userId = null, bool $isCreate = true): array
    {
        $rules = [
            'customer_id' => 'required|string|size:36|exists:customers,id',
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'account' => 'required|string|max:50',
            'enable' => 'boolean',
            'avatar' => 'nullable|string|max:255',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'string|exists:roles,id',
        ];

        if ($isCreate) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        return $validator->validated();
    }
}