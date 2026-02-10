<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermissionsMatrix extends Model
{
    protected $table = 'role_permissions_matrix';

    protected $fillable = [
        'role_name',
        'module',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Vérifie si un rôle a une permission sur un module
     */
    public static function hasPermission(string $role, string $module, string $permission): bool
    {
        $matrix = static::where('role_name', $role)
            ->where('module', $module)
            ->where('is_active', true)
            ->first();
        
        if (!$matrix) {
            return false;
        }
        
        return in_array($permission, $matrix->permissions);
    }
}
