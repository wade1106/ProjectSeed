<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use App\Utils\MailUtil;

class CustomerService
{
    public function getAllCustomers($filters = [])
    {
        $query = Customer::query();

        if (isset($filters['keyword']) && !empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('taxId', 'like', "%{$keyword}%")
                  ->orWhere('contact', 'like', "%{$keyword}%");
            });
        }

        if (isset($filters['status'])) {
            $now = Carbon::now();

            switch ($filters['status']) {
                case 'active':
                    $query->where('enable', true)
                          ->where('isDeleted', false)
                          ->where('start_date', '<=', $now)
                          ->where('end_date', '>=', $now);
                    break;

                case 'disable':
                    $query->where('enable', false)
                          ->where('isDeleted', false)
                          ->where('start_date', '<=', $now)
                          ->where('end_date', '>=', $now);
                    break;

                case 'expired':
                    $query->where('end_date', '<', $now)
                          ->where('isDeleted', false);
                    break;

                case 'all':
                default:
                    break;
            }
        }

        $query->where('isDeleted', false);

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    public function getCustomerById($id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer->isDeleted) {
            throw new \Exception('Customer not found');
        }

        return $customer;
    }

    public function createCustomer(array $data): Customer
    {
        $validated = $this->validateCustomerData($data);
        $currentUserId = Auth::id();

        // 使用 Transaction 確保三張表寫入的原子性
        return DB::transaction(function () use ($validated, $currentUserId) {
            
            // 1. 寫入 Customer 資料
            $customer = Customer::create([
                'name'       => $validated['name'] ?? null,
                'code'       => $validated['code'] ?? null,
                'taxId'      => $validated['taxId'] ?? null,
                'contact'    => $validated['contact'] ?? null,
                'email'      => $validated['email'] ?? null,
                'tel'        => $validated['tel'] ?? null,
                'address'    => $validated['address'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date'   => $validated['end_date'] ?? null,
                'enable'     => $validated['enable'] ?? true,
                'notes'      => $validated['notes'] ?? null,
                'isDeleted'  => false,
                'created_by' => $currentUserId,
                'updated_by' => $currentUserId,
            ]);

            // 2. 寫入 Role 資料 (預設最高權限角色)
            Role::create([
                'customer_id' => $customer->id,
                'name'        => 'admin',
                'description' => '公司預設最高權限不可刪除。',
                'enable'      => 1,
                'isDefault'   => 1,
                'created_by'  => $currentUserId,
                'updated_by'  => $currentUserId,
            ]);

            // 3. TODO: 寫入角色權限對應資料 (可選，視需求而定)

            // 4. 寫入預設 使用者 資料 (admin 帳號)
            // 產生 12 碼隨機英數字密碼
            $randomPassword = Str::random(12);

            $user = User::create([
                'customer_id' => $customer->id,
                'name'        => 'admin',
                'email'       => $customer->email, // 繼承公司的主要 Email
                'account'     => 'admin',
                'password'    => $randomPassword, // 密碼將在 User 模型中自動加密
                'enable'      => 0, // 預設停用，需客戶自行啟用
                'isDeleted'   => 0,
                'created_by'  => $currentUserId,
                'updated_by'  => $currentUserId,
            ]);

            // 5. 寫入帳號角色對應資料 (將 admin 使用者指派到 admin 角色)
                    
            // 6. 透過 Email 通知客戶 $randomPassword
            if($customer){
                $result = MailUtil::sendHtml(
                    [$customer->email],
                    ['service@carbontact.com', 'wade.chung@carbontact.com', 'lili.liao@carbontact.com'],
                    '【Digital Product Passport】帳號啟用： '.$customer->name.'的管理員，請變更密碼並啟用您的管理帳號',
                    MailUtil::prepareActivationEmail($customer->code, 'admin', $randomPassword)
                );
            }
            Log::info('Customer created successfully -----------------------------> ', ['customerId' => $customer->id, 'created_by' => $currentUserId]);
            return $customer;
        });
    }

    public function updateCustomer($id, $data)
    {
        $validated = $this->validateCustomerData($data, $id, false);

        $customer = Customer::findOrFail($id);

        if ($customer->isDeleted) {
            throw new \Exception('Customer not found');
        }

        $currentUserId = Auth::id();

        $customer->update([
            'name' => $validated['name'] ?? $customer->name,
            'code' => $validated['code'] ?? $customer->code,
            'taxId' => $validated['taxId'] ?? $customer->taxId,
            'contact' => $validated['contact'] ?? $customer->contact,
            'email' => $validated['email'] ?? $customer->email,
            'tel' => $validated['tel'] ?? $customer->tel,
            'address' => $validated['address'] ?? $customer->address,
            'start_date' => $validated['start_date'] ?? $customer->start_date,
            'end_date' => $validated['end_date'] ?? $customer->end_date,
            'enable' => $validated['enable'] ?? $customer->enable,
            'notes' => $validated['notes'] ?? $customer->notes,
            'updated_by' => $currentUserId,
        ]);

        return $customer;
    }

    public function deleteCustomer($id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer->isDeleted) {
            throw new \Exception('Customer not found');
        }

        $currentUserId = Auth::id();

        $customer->update([
            'isDeleted' => true,
            'updated_by' => $currentUserId,
        ]);

        return true;
    }

    private function validateCustomerData($data, $customerId = null, $isCreate = true)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'taxId' => 'nullable|string|max:20',
            'contact' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'tel' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'enable' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ];

        if ($isCreate) {
            $rules['name'] .= '|unique:customers,name,NULL,id,isDeleted,0';
        } elseif ($customerId) {
            // 更新時跳過自己的姓名檢查
            $rules['name'] .= '|unique:customers,name,' . $customerId . ',id,isDeleted,0';
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        return $validator->validated();
    }
}