<?php

namespace App\Domain\Auth\Services;

use App\Application\Contracts\Auth\RoleManagementServiceInterface;
use App\Domain\Auth\Models\Role;
use App\Domain\Auth\Repositories\RoleRepositoryInterface;
use App\Domain\Auth\Repositories\PermissionRepositoryInterface;
use App\Domain\Auth\ValueObjects\Permission;
use App\Infrastructure\Events\Auth\RoleCreated;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\ValueObjects\UserId;
use App\Domain\Auth\Exceptions\UserNotFoundException;
use App\Domain\Auth\Exceptions\RoleNotFoundException;

class RoleManagementService implements RoleManagementServiceInterface
{
    private RoleRepositoryInterface $roleRepository;
    private PermissionRepositoryInterface $permissionRepository;
    private AccessControlService $accessControl;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        RoleRepositoryInterface $roleRepository,
        PermissionRepositoryInterface $permissionRepository,
        AccessControlService $accessControl,
        UserRepositoryInterface $userRepository
    ) {
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
        $this->accessControl = $accessControl;
        $this->userRepository = $userRepository;
    }

    public function getRolesAndPermissions(): array
    {
        $roles = $this->roleRepository->getAllWithPermissions();
        $mappedRoles = $roles->map(function ($role) {

            $permissions = collect($role->getPermissions())
                ->map(fn($permission) => [
                    'id' => $permission->getId(),
                    'name' => $permission->getName()
                ])
                ->values()
                ->toArray();

            return [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'description' => $role->getDescription(),
                'permissions' => $permissions
            ];
        })->toArray();

        $permissions = $this->getAllPermissions();

        return [
            'roles' => $mappedRoles,
            'permissions' => $permissions
        ];
    }

    public function createRole(string $name, string $description, array $permissionIds): array
    {
        $this->accessControl->validateFeatureAccess('role', 'create', [
            'role_name' => $name,
            'permissions' => $permissionIds
        ]);

        $permissions = collect($permissionIds)
            ->map(fn($id) => $this->permissionRepository->findById($id))
            ->filter()
            ->toArray();

        $role = Role::create($name, $description, $permissions);

        $this->roleRepository->save($role);
        // Dispatch domain event
        Event::dispatch(new RoleCreated($role));

        return [
            'id' => $role->getId(),
            'name' => $role->getName(),
            'description' => $role->getDescription(),
            'permissions' => $permissions
        ];
    }

    public function updateRole(int $roleId, array $data): array
    {
        // Validate access with context
        $this->accessControl->validateFeatureAccess('role', 'update', [
            'role_id' => $roleId,
            'role_name' => $data['name'] ?? null,
            'permissions' => $data['permissions'] ?? []
        ]);

        $updatedRole = $this->roleRepository->update($roleId, $data);
        return [
            'id' => $updatedRole->getId(),
            'name' => $updatedRole->getName(),
            'description' => $updatedRole->getDescription(),
            'permissions' => $updatedRole->getPermissions()
        ];
    }

    public function updateRolePermissions(int $roleId, array $permissions): array
    {
        // Validate access with context
        $this->accessControl->validateFeatureAccess('role', 'update', [
            'role_id' => $roleId,
            'permissions' => $permissions
        ]);

        $role = $this->roleRepository->findById($roleId);
        $updatedRole = $this->roleRepository->updatePermissions($role, $permissions);
        
        return [
            'id' => $updatedRole->getId(),
            'name' => $updatedRole->getName(),
            'description' => $updatedRole->getDescription(),
            'permissions' => $updatedRole->getPermissions()
        ];
    }

    public function deleteRole(int $roleId): void
    {
        // Validate access with context
        $this->accessControl->validateFeatureAccess('role', 'delete', [
            'role_id' => $roleId,
        ]);

        // Prevent deletion of admin role
        $role = $this->getRole($roleId);
        if ($role['name'] === 'admin') {
            throw new \DomainException('The admin role cannot be deleted');
        }

        $this->roleRepository->delete($roleId);
    }

    public function getAllPermissions(): array
    {
        // Validate access
        $this->accessControl->validateFeatureAccess('role', 'view');

        $permissions = $this->permissionRepository->getAll();
        return $permissions->groupBy('module')
            ->map(function ($modulePermissions) {
                return $modulePermissions->map(function ($permission) {
                    return [
                        'id' => $permission->getId(),
                        'name' => $permission->getName(),
                        'description' => $permission->getDescription(),
                        'action' => $permission->getAction()
                    ];
                })->values();
            })->toArray();
    }

    public function assignRole(UserId $userId, int $roleId): void
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new UserNotFoundException($userId);
        }

        $role = $this->roleRepository->findById($roleId);
        if (!$role) {
            throw new RoleNotFoundException($roleId);
        }

        $user->assignRole($role);
        $this->userRepository->save($user);
    }

    /**
     * Get specific role details
     */
    public function getRole(int $roleId): array
    {
        // Validate access
        $this->accessControl->validateFeatureAccess('role', 'view', [
            'role_id' => $roleId
        ]);

        // Get role
        $role = $this->roleRepository->findById($roleId);
        if (!$role) {
            throw new RoleNotFoundException($roleId);
        }

        // Get permissions
        $permissions = collect($role->getPermissions())
            ->map(fn($permission) => [
                'id' => $permission->getId(),
                'name' => $permission->getName()
            ])
            ->values()
            ->toArray();

        return [
            'id' => $role->getId(),
            'name' => $role->getName(),
            'description' => $role->getDescription(),
            'permissions' => $permissions
        ];
    }
}