<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<string|mixed>|string>
     */
    public function rules(): array
    {
        return match ($this->method()) {
            'POST' => $this->storeRules(),
            'PUT', 'PATCH' => $this->updateRules(),
            default => [],
        };
    }

    /**
     * 獲取自定義錯誤訊息
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => '客戶ID為必填項目',
            'customer_id.exists' => '指定的客戶不存在',
            'name.required' => '姓名為必填項目',
            'name.max' => '姓名不能超過50個字元',
            'email.required' => '電子郵件為必填項目',
            'email.email' => '請輸入有效的電子郵件格式',
            'email.max' => '電子郵件不能超過255個字元',
            //'email.unique' => '此電子郵件已被使用',
            'account.required' => '登入帳號為必填項目',
            'account.max' => '登入帳號不能超過50個字元',
            'account.unique' => '此登入帳號已被使用',
            'password.required' => '密碼為必填項目',
            'password.min' => '密碼至少需要8個字元',
            'password.confirmed' => '兩次輸入的密碼不一致',
            'enable.boolean' => '啟用狀態必須是布林值',
            'avatar.max' => '頭像連結不能超過255個字元',
        ];
    }

    /**
     * 獲取自定義屬性名稱
     */
    public function attributes(): array
    {
        return [
            'customer_id' => '客戶ID',
            'name' => '姓名',
            'email' => '電子郵件',
            'account' => '登入帳號',
            'password' => '密碼',
            'password_confirmation' => '確認密碼',
            'enable' => '啟用狀態',
            'avatar' => '頭像',
        ];
    }

    /**
     * 創建時的驗證規則
     */
    private function storeRules(): array
    {
        return [
            'customer_id' => 'required|string|size:36|exists:customers,id',
            'name' => 'required|string|max:50',
            'email' => [
                'required',
                'email',
                'max:255'
                //Rule::unique('users')->where('isDeleted', false)
            ],
            'account' => [
                'required',
                'string',
                'max:50'
                //Rule::unique('users')->where('isDeleted', false)
            ],
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required_with:password',
            'enable' => 'boolean',
            'avatar' => 'nullable|string|max:255',
        ];
    }

    /**
     * 更新時的驗證規則
     */
    private function updateRules(): array
    {
        $userId = $this->route('user'); // 從路由參數獲取用戶ID

        return [
            'customer_id' => 'sometimes|required|string|size:36|exists:customers,id',
            'name' => 'sometimes|required|string|max:50',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255'
                //Rule::unique('users')->where('isDeleted', false)->ignore($userId)
            ],
            'account' => [
                'sometimes',
                'required',
                'string',
                'max:50'
                //Rule::unique('users')->where('isDeleted', false)->ignore($userId)
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'password_confirmation' => 'nullable|required_with:password',
            'enable' => 'boolean',
            'avatar' => 'nullable|string|max:255',
        ];
    }
}