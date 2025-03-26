<?php

namespace App\Infrastructure\Auth\Mappers;

use App\Domain\Auth\Models\Permission as DomainPermission;
use App\Models\Permission as EloquentPermission;

class PermissionMapper
{
    public function toDomain(EloquentPermission $eloquentPermission): DomainPermission
    {
        return new DomainPermission(
            name: $eloquentPermission->name,
            description: $eloquentPermission->description,
            module: $eloquentPermission->module,
            action: $eloquentPermission->action,
            id: $eloquentPermission->id
        );
    }

    public function toEloquent(DomainPermission $domainPermission): EloquentPermission
    {
        $permission = new EloquentPermission();
        
        if ($domainPermission->getId()) {
            $permission = EloquentPermission::find($domainPermission->getId()) ?? $permission;
        }

        $permission->name = $domainPermission->getName();
        $permission->description = $domainPermission->getDescription();
        $permission->module = $domainPermission->getModule();
        $permission->action = $domainPermission->getAction();

        return $permission;
    }
}
