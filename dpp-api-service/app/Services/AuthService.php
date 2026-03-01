<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\User;
use App\Models\Customer;
use App\Models\TokenBlacklist;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly JwtService $jwtService
    ) {}

    /**
     * 檢查 token 是否在黑名單中
     */
    private function isTokenBlacklisted(?string $token): bool
    {
        if (!$token) {
            return false;
        }

        try {
            $tokenHash = hash('sha256', $token);
            return TokenBlacklist::where('token', $tokenHash)->exists();
        } catch (\Exception $e) {
            logger("Token blacklist check failed: " . $e->getMessage());
            return true; // 如果檢查失敗，保守起見視為已黑名單
        }
    }

    public function authenticateAdmin(string $account, string $password, ?string $currentToken = null): ?array
    {
        // 檢查目前 token 是否在黑名單中
        if ($this->isTokenBlacklisted($currentToken)) {
            logger("Login attempt with blacklisted token for account: {$account}");
            return null;
        }

        $admin = Admin::where('account', $account)
            ->where('enable', true)
            ->where('isDeleted', false)
            ->first();

        // 測試 1: 是否有抓到 admin 資料？
        if (!$admin) {
            // 如果這裡印出來，代表 SQL 沒抓到人 (帳號錯、enable 錯或 isDeleted 錯)
            logger("Admin not found with account: {$account}");
            return null;
        }

        // 2. 使用 Hash::check 驗證明文密碼 ($password) 與資料庫密碼 ($admin->password)
        if (!$admin || !Hash::check($password, $admin->password)) {
            return null;
        }
        /*
        if (!$admin || !hash_equals(md5($password), (string) $admin->password)) {
            return null;
        }
        */

        $adminRole = $admin->isDefault ? 'SysAdmin' : 'Admin'; // 定義角色名稱

        $tokenPayload = [
            'uid' => $admin->id,
            'name' => $admin->name,
            'account' => $admin->account,
            'role' => $adminRole, // 同步 Payload
            'isDefault' => $admin->isDefault,
        ];

        $token = $this->jwtService->generateToken($tokenPayload);

        return [
            'success' => true,
            'token' => $token,
            'data' => [
                'uid' => $admin->id,
                'name' => $admin->name,
                'account' => $admin->account,
                'role' => $adminRole,
                'isDefault' => $admin->isDefault,
            ],
        ];
    }

    public function authenticateUser(string $code, string $account, string $password, ?string $currentToken = null): ?array
    {
        // 檢查目前 token 是否在黑名單中
        if ($this->isTokenBlacklisted($currentToken)) {
            logger("Login attempt with blacklisted token for account: {$account}");
            return null;
        }

        $customer = Customer::where('code', $code)
            ->where('enable', true)
            ->where('isDeleted', false)
            ->first();

        if (!$customer) {
            return null;
        }

        $user = User::where('account', $account)
            ->where('customer_id', $customer?->id)
            ->where('enable', true)
            ->where('isDeleted', false)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        $tokenPayload = [
            'uid' => $user->id,
            'name' => $user->name,
            'account' => $user->account,
            'email' => $user->email,
            'customerId' => $user->customer_id,
            'customerName' => $user->customer?->name,
            'avatar' => $user->avatar,
            'role' => 'User',
            'roles' => $user->roles()->select('roles.id', 'roles.name', 'roles.isDefault')->get(),
            'permissions' => $user->getAllPermissions(),
        ];

        $token = $this->jwtService->generateToken($tokenPayload);

        return [
            'success' => true,
            'token' => $token,
            'data' => [
                'uid' => $user->id,
                'name' => $user->name,
                'account' => $user->account,
                'email' => $user->email,
                'customerId' => $user->customer_id,
                'customerName' => $user->customer?->name,
                'avatar' => $user->avatar,
                'role' => 'User',
                'roles' => $user->roles()->select('roles.id', 'roles.name', 'roles.isDefault')->get(),
                'permissions' => $user->getAllPermissions(),
            ],
        ];
    }
}