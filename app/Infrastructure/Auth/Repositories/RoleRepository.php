<?php

namespace App\Infrastructure\Auth\Repositories;

use App\Domain\Auth\Models\Role as DomainRole;
use App\Domain\Auth\Repositories\RoleRepositoryInterface;
use App\Infrastructure\Auth\Mappers\RoleMapper;
use App\Models\Role as EloquentRole;
use Illuminate\Support\Collection;

class RoleRepository implements RoleRepositoryInterface
{
    public function __construct(
        private RoleMapper $mapper
    ) {}

    public function findById(int $id): ?DomainRole
    {
        $role = EloquentRole::with('permissions')->find($id);
        return $role ? $this->mapper->toDomain($role) : null;
    }

    public function findByName(string $name): ?DomainRole
    {
        $role = EloquentRole::where('name', $name)->first();
        return $role ? $this->mapper->toDomain($role) : null;
    }

    public function save(DomainRole $role): void
    {
        $this->mapper->toEloquent($role)->save();
    }

    public function getAllWithPermissions(): Collection
    {
        $roles = EloquentRole::with(['permissions'])->get();
        return $roles->map(fn($role) => $this->mapper->toDomain($role));
    }

    public function delete(int $roleId): void
    {
        EloquentRole::destroy($roleId);
    }

    public function getAll(): Collection
    {
        return EloquentRole::all()
            ->map(fn ($role) => $this->mapper->toDomain($role));
    }

    
    public function update(int $roleId, array $data): ?DomainRole
    {
        
        $role = DomainRole::create($data['name'], $data['description'], $data['permissions'], $roleId);
        $eloRole = $this->mapper->toEloquent($role);
        $eloRole->save();
        $role = $this->mapper->toDomain($eloRole);
        
        return $role;
    }    

}
