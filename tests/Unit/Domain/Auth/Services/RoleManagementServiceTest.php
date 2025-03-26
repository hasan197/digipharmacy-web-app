<?php

namespace Tests\Unit\Domain\Auth\Services;

use App\Domain\Auth\Models\Role;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Repositories\RoleRepositoryInterface;
use App\Domain\Auth\Repositories\PermissionRepositoryInterface;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\Services\RoleManagementService;
use App\Domain\Auth\Services\AccessControlService;
use App\Domain\Auth\ValueObjects\RoleId;
use App\Infrastructure\Events\Auth\RoleCreated;
use App\Domain\Auth\Exceptions\UnauthorizedException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;
use App\Domain\Auth\Models\Permission;

class RoleManagementServiceTest extends TestCase
{
    private RoleRepositoryInterface $roleRepository;
    private PermissionRepositoryInterface $permissionRepository;
    private AccessControlService $accessControl;
    private UserRepositoryInterface $userRepository;
    private RoleManagementService $roleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roleRepository = Mockery::mock(RoleRepositoryInterface::class);
        $this->permissionRepository = Mockery::mock(PermissionRepositoryInterface::class);
        $this->accessControl = Mockery::mock(AccessControlService::class);
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        
        $this->roleService = new RoleManagementService(
            $this->roleRepository,
            $this->permissionRepository,
            $this->accessControl,
            $this->userRepository
        );
    }

    public function test_get_roles_and_permissions_returns_array()
    {
        // Arrange
        $role1 = new Role('editor', 'Editor role');
        $role1->setId(1);
        $role2 = new Role('viewer', 'Viewer role');
        $role2->setId(2);
        
        $roles = new Collection([$role1, $role2]);

        $this->accessControl
            ->shouldReceive('validateFeatureAccess')
            ->with('role', 'view')
            ->once();

        $this->roleRepository
            ->shouldReceive('getAllWithPermissions')
            ->once()
            ->andReturn($roles);

        $this->permissionRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn(new Collection([]));

        // Act
        $result = $this->roleService->getRolesAndPermissions();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('roles', $result);
        $this->assertArrayHasKey('permissions', $result);
        $this->assertCount(2, $result['roles']);
    }

    public function test_create_role_with_valid_data_returns_success()
    {
        // Arrange
        Event::fake();
        
        $name = 'editor';
        $description = 'Editor role';
        $permissionIds = [1, 2, 3];

        $this->accessControl
            ->shouldReceive('validateFeatureAccess')
            ->with('role', 'create', [
                'role_name' => $name,
                'permissions' => $permissionIds
            ])
            ->once();

        // Mock permissions dengan Permission model
        $permissions = collect($permissionIds)->map(function($id) {
            return new Permission(
                "permission_$id", 
                "Permission $id",
                "module_$id",
                "action_$id",
                $id
            );
        })->toArray();

        $this->permissionRepository
            ->shouldReceive('findById')
            ->times(3)
            ->andReturn(...$permissions);

        $role = new Role($name, $description, $permissions);
        
        $this->roleRepository
            ->shouldReceive('save')
            ->with(Mockery::type(Role::class))
            ->once()
            ->andReturn($role);

        // Act
        $result = $this->roleService->createRole($name, $description, $permissionIds);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($name, $result['name']);
        $this->assertEquals($description, $result['description']);
        
        Event::assertDispatched(RoleCreated::class);
    }

    public function test_create_role_without_admin_permission_throws_exception()
    {
        // Arrange
        $roleData = [
            'name' => 'editor',
            'description' => 'Editor role',
            'permissions' => [1, 2, 3]
        ];

        $this->accessControl
            ->shouldReceive('validateFeatureAccess')
            ->with('role', 'create', [
                'role_name' => $roleData['name'],
                'permissions' => $roleData['permissions']
            ])
            ->once()
            ->andThrow(new \DomainException('Only administrators can create roles'));

        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Only administrators can create roles');

        // Act
        $this->roleService->createRole(
            $roleData['name'],
            $roleData['description'],
            $roleData['permissions']
        );
    }

    public function test_update_role_permissions_success()
    {
        // Arrange
        $role = new Role(
            'editor',
            'Editor role'
        );
        $role->setId(1);
        $permissions = [1, 2, 3];

        $updatedRole = new Role(
            'editor',
            'Editor role',
            $permissions
        );
        $updatedRole->setId(1);

        // Mock access control
        $this->accessControl
            ->shouldReceive('validateFeatureAccess')
            ->with('role', 'update', [
                'role_id' => $role->getId(),
                'permissions' => $permissions
            ])
            ->once();

        $this->roleRepository
            ->shouldReceive('updatePermissions')
            ->with($role->getId(), $permissions)
            ->once()
            ->andReturn($updatedRole);

        // Act
        $result = $this->roleService->updateRolePermissions($role, $permissions);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals('editor', $result['name']);
        $this->assertEquals($permissions, $result['permissions']);
    }

    public function test_update_role_validates_access()
    {
        // Arrange
        $role = new Role('editor', 'Editor role');
        $role->setId(1);
        
        $data = [
            'name' => 'updated_editor',
            'description' => 'Updated description',
            'permissions' => [1, 2]
        ];

        $this->accessControl
            ->shouldReceive('validateFeatureAccess')
            ->with('role', 'update', [
                'role_id' => $role->getId(),
                'role_name' => $data['name'],
                'permissions' => $data['permissions']
            ])
            ->once()
            ->andThrow(new UnauthorizedException('Access denied'));

        // Assert
        $this->expectException(UnauthorizedException::class);

        // Act
        $this->roleService->updateRole($role, $data);
    }

    public function test_delete_role_prevents_admin_role_deletion()
    {
        // Arrange
        $adminRole = new Role('admin', 'Administrator');
        
        $this->accessControl
            ->shouldReceive('validateFeatureAccess')
            ->with('role', 'delete', Mockery::any())
            ->once();

        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('The admin role cannot be deleted');

        // Act
        $this->roleService->deleteRole($adminRole);
    }

    public function test_get_role_returns_role_details()
    {
        // Arrange
        $roleId = 1;
        $role = new Role('editor', 'Editor role');
        $role->setId($roleId);
        
        $this->accessControl
            ->shouldReceive('validateFeatureAccess')
            ->with('role', 'view', [
                'role_id' => $role->getId()
            ])
            ->once();

        // Act
        $result = $this->roleService->getRole($role);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($roleId, $result['id']);
        $this->assertEquals($role->getName(), $result['name']);
        $this->assertEquals($role->getDescription(), $result['description']);
        $this->assertArrayHasKey('permissions', $result);
    }

    public function test_get_role_validates_access()
    {
        // Arrange
        $role = new Role('editor', 'Editor role');
        $role->setId(1);
        
        $this->accessControl
            ->shouldReceive('validateFeatureAccess')
            ->with('role', 'view', [
                'role_id' => $role->getId()
            ])
            ->once()
            ->andThrow(new UnauthorizedException('Access denied'));

        // Assert
        $this->expectException(UnauthorizedException::class);

        // Act
        $this->roleService->getRole($role);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
