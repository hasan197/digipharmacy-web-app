<?php

namespace App\Application\Contracts\Auth;

use App\Domain\Auth\Models\Role;

interface RoleManagementServiceInterface
{
    /**
     * Get all roles with their permissions
     */
    public function getRolesAndPermissions(): array;

    /**
     * Get specific role details
     */
    public function getRole(int $roleId): array;

    /**
     * Create new role with permissions
     */
    public function createRole(string $name, string $description, array $permissionIds): array;

    /**
     * Update existing role
     */
    public function updateRole(int $roleId, array $data): array;

    /**
     * Delete role
     */
    public function deleteRole(int $roleId): void;
}
