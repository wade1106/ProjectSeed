<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'role_permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id',
        'permission_id',
    ];

    /**
     * The role that belongs to the role permission.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * The permission that belongs to the role permission.
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}