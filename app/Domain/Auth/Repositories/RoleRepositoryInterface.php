<?php

namespace App\Domain\Auth\Repositories;

use App\Domain\Auth\Models\Role;
use Illuminate\Support\Collection;

interface RoleRepositoryInterface
{
    public function findById(int $id): ?Role;
    public function findByName(string $name): ?Role;
    public function save(Role $role): void;
    public function delete(int $roleId): void;
    public function getAll(): Collection;
    
    /**
     * Get all roles with their associated permissions
     * @return Collection<Role>
     */
    public function getAllWithPermissions(): Collection;
}
