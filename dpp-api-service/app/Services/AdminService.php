<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    public function getAdminsList(int $page, int $perPage, ?string $keyword, ?bool $enable): array
    {
        $query = Admin::where('isDeleted', false);

        if ($keyword !== null) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%")
                  ->orWhere('account', 'like', "%{$keyword}%");
            });
        }

        if ($enable !== null) {
            $query->where('enable', $enable);
        }

        $admins = $query->with(['creator:id,name', 'updater:id,name'])
            ->select([
                'id', 'name', 'email', 'account', 'enable',
                'avatar', 'isDefault', 'created_by', 'updated_by',
                'created_at', 'updated_at'
            ])
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $admins->items(),
            'pagination' => [
                'current_page' => $admins->currentPage(),
                'per_page' => $admins->perPage(),
                'total' => $admins->total(),
                'last_page' => $admins->lastPage(),
                'from' => $admins->firstItem(),
                'to' => $admins->lastItem(),
            ]
        ];
    }

    public function getAdminById(string $id): ?Admin
    {
        return Admin::where('isDeleted', false)
            ->where('id', $id)
            ->with(['creator:id,name', 'updater:id,name'])
            ->first();
    }

    public function createAdmin(array $data): Admin
    {
        $adminData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'account' => $data['account'],
            'password' => Hash::make($data['password']),
            'enable' => $data['enable'] ?? true,
            'isDefault' => false,
            'isDeleted' => false,
            'avatar' => $data['avatar'] ?? null,
            'created_by' => $data['created_by'],
            'updated_by' => $data['updated_by'],
        ];

        return Admin::create($adminData);
    }

    public function updateAdmin(string $id, array $data): ?Admin
    {
        $admin = Admin::where('isDeleted', false)
            ->where('id', $id)
            ->first();

        if (!$admin) {
            return null;
        }

        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }

        if (isset($data['account'])) {
            $updateData['account'] = $data['account'];
        }

        if (isset($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        if (isset($data['enable'])) {
            $updateData['enable'] = $data['enable'];
        }

        if (isset($data['avatar'])) {
            $updateData['avatar'] = $data['avatar'];
        }

        if (isset($data['updated_by'])) {
            $updateData['updated_by'] = $data['updated_by'];
        }

        $admin->update($updateData);

        return $admin->fresh(['creator:id,name', 'updater:id,name']);
    }

    public function deleteAdmin(string $id, string $deletedBy): bool
    {
        $admin = Admin::where('isDeleted', false)
            ->where('id', $id)
            ->first();

        if (!$admin) {
            return false;
        }

        if ($admin->isDefault) {
            return false;
        }

        $admin->update([
            'isDeleted' => true,
            'updated_by' => $deletedBy
        ]);

        return true;
    }
}