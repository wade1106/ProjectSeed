<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Customer;

class UserProfileController extends Controller
{
    
    /**
     * 免登入變更密碼
     */
    public function changePassword(Request $request)
    {
        try {
            // 1. 驗證前端傳入參數
            $validator = Validator::make($request->all(), [
                'companyCode' => 'required|string',
                'username'    => 'required|string',
                'old_password' => 'required|string',
                'password'     => [
                    'required',
                    'string',
                    'min:6',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
                ],
            ], [
                'password.regex' => '新密碼長度至少8位且需包含大小寫英文字母、數字及至少一個特殊符號(@$!%*?&)',
                'password.confirmed' => '兩次輸入的新密碼不符',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '驗證失敗',
                    'errors'  => $validator->errors()
                ], 422);
            }

            // 2. 在 customers 表找到對應的公司 (code 且 status 為 1)
            $customer = Customer::where('code', $request->companyCode)
                          ->where('enable', 1)
                          ->first();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => '找不到對應的公司資料或該公司已被停用'
                ], 404);
            }

            // 3. 在 users 表找到該公司的使用者
            // 注意：密碼驗證不能直接寫在 where 條件，因為資料庫存的是 Hash
            $user = User::where('account', $request->username)
                        ->where('customer_id', $customer->id)
                        ->where('enable', 0)
                        //->where('isDefault', 0)
                        ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => '待啟用的帳號不存在，可能原因:<br/>1.帳號或公司代碼不正確<br/>2.帳號已啟用，無需再次啟用'
                ], 404);
            }

            // 4. 比對舊密碼是否正確
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => '目前密碼不正確'
                ], 400);
            }

            // 5. 更新密碼與狀態
            //$user->password = Hash::make($request->password);
            $user->password = $request->password; // 直接賦予明文，由 Model 的 casts 自動處理雜湊
            $user->enable = 1;

            $user->save();

            return response()->json([
                'success' => true,
                'message' => '密碼變更成功，帳號已啟用',
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Public Change Password Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '變更過程中發生錯誤，請聯繫系統管理員',
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

}