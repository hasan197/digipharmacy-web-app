<?php

namespace App\Infrastructure\Auth\Repositories;

use App\Domain\Auth\Models\Permission as DomainPermission;
use App\Domain\Auth\Repositories\PermissionRepositoryInterface;
use App\Infrastructure\Auth\Mappers\PermissionMapper;
use App\Models\Permission as EloquentPermission;
use Illuminate\Support\Collection;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function __construct(
        private PermissionMapper $mapper
    ) {}

    public function findById(int $id): ?DomainPermission
    {
        $permission = EloquentPermission::find($id);
        return $permission ? $this->mapper->toDomain($permission) : null;
    }

    public function findByName(string $name): ?DomainPermission
    {
        $permission = EloquentPermission::where('name', $name)->first();
        return $permission ? $this->mapper->toDomain($permission) : null;
    }

    public function getAll(): Collection
    {
        return EloquentPermission::all()
            ->map(fn($permission) => $this->mapper->toDomain($permission));
    }
}
