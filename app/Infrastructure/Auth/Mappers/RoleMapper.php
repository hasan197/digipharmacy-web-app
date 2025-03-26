<?php

namespace App\Infrastructure\Auth\Mappers;

use App\Domain\Auth\Models\Role as DomainRole;
use App\Domain\Auth\Models\Permission as DomainPermission;
use App\Models\Role as EloquentRole;
use App\Models\Permission as EloquentPermission;

class RoleMapper
{
    public function toDomain(EloquentRole $eloquentRole): DomainRole
    {
        // Convert Eloquent Permission Collection to array of Permission domain objects
        $permissions = $eloquentRole->permissions
            ? $eloquentRole->permissions->map(function ($permission) {
                return new DomainPermission(
                    $permission->name,
                    $permission->description,
                    $permission->module,
                    $permission->action,
                    $permission->id
                );
            })->toArray()
            : [];

        $role = DomainRole::create(
            $eloquentRole->name,
            $eloquentRole->description,
            $permissions
        );

        if ($eloquentRole->id) {
            $role->setId($eloquentRole->id);
        }

        return $role;
    }

    public function toEloquent(DomainRole $domainRole): EloquentRole
    {
        $role = new EloquentRole();
        
        if ($domainRole->getId()) {
            $role = EloquentRole::find($domainRole->getId()) ?? $role;
        }

        $role->name = $domainRole->getName();
        $role->description = $domainRole->getDescription();

        // Save the role first to get an ID
        $role->save();

        // Get permission IDs from domain permissions
        $permissionIds = collect($domainRole->getPermissions())
            ->map(fn($permission) => $permission)
            ->filter()
            ->toArray();

        // Sync permissions
        $role->permissions()->sync($permissionIds);

        // Set ID back to domain role
        $domainRole->setId($role->id);

        return $role->fresh('permissions');
    }
}
