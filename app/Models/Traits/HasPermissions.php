<?php

namespace App\Models\Traits;

use App\Models\Permission;
use App\Models\Role;

trait HasPermissions
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions()
    {
        return $this->roles->map->permissions->flatten()->unique('id');
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains('name', $roleName);
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->contains('name', $permissionName);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return $this->permissions()->whereIn('name', $permissions)->isNotEmpty();
    }

    public function hasAllPermissions(array $permissions): bool
    {
        $userPermissions = $this->permissions()->pluck('name');
        return collect($permissions)->every(fn($permission) => $userPermissions->contains($permission));
    }
}
