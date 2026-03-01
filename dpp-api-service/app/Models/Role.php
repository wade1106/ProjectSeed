<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'roles';

    protected $fillable = [
        'customer_id', 'name', 'description', 'enable', 
        'isDefault', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'enable' => 'boolean',
        'isDefault' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    /**
     * Get all permissions for the role, grouped by module
     */
    public function getPermissions()
    {
        $permissions = [];

        foreach ($this->permissions as $permission) {
            $moduleCode = $permission->module->code;

            if (!isset($permissions[$moduleCode])) {
                $permissions[$moduleCode] = [];
            }

            $permissions[$moduleCode][] = $permission->code;
        }

        return $permissions;
    }
}