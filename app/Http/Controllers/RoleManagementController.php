<?php

namespace App\Http\Controllers;

use App\Domain\Auth\Models\Role;
use App\Application\Contracts\Auth\RoleManagementServiceInterface;
use App\Domain\Auth\Exceptions\UnauthorizedException;
use App\Domain\Auth\Exceptions\DomainException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Role as EloquentRole;
use App\Infrastructure\Auth\Mappers\RoleMapper;
use App\Services\LogService as Log;

class RoleManagementController extends Controller
{
    public function __construct(
        private readonly RoleManagementServiceInterface $roleService
    ) {}

    /**
     * Handle permission errors and other exceptions
     */
    protected function handleException(\Exception $e): JsonResponse
    {
        if ($e instanceof UnauthorizedException) {
            return response()->json([
                'error' => 'unauthorized',
                'message' => $e->getMessage()
            ], 403);
        }

        if ($e instanceof DomainException) {
            return response()->json([
                'error' => 'validation_error',
                'message' => $e->getMessage()
            ], 422);
        }

        // Log unexpected errors
        \Log::error('Role management error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'error' => 'server_error',
            'message' => 'An unexpected error occurred'
        ], 500);
    }

    public function index(): JsonResponse
    {
        try {
            $data = $this->roleService->getRolesAndPermissions();
            return response()->json($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:roles,name',
                'description' => 'required|string',
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,id'
            ]);

            $role = $this->roleService->createRole(
                $validated['name'],
                $validated['description'],
                $validated['permissions']
            );

            return response()->json([
                'message' => 'Role created successfully',
                'role' => $role
            ], 201);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(Role $role): JsonResponse
    {
        try {
            $data = $this->roleService->getRole($role);
            return response()->json($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, int $roleId)
    {
        Log::pos('kirim data role untuk update 4', [
            'roleId' => $roleId,
        ]);

        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:roles,name,' . $roleId,
                'description' => 'required|string',
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,id'
            ]);

            $updatedRole = $this->roleService->updateRole(
                $roleId,
                [
                    'name' => $validated['name'],
                    'description' => $validated['description'],
                    'permissions' => $validated['permissions']
                ]
            );

            return response()->json([
                'message' => 'Role updated successfully',
                'role' => $updatedRole
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(int $roleId)
    {
        try {
            // Validasi user dan admin role akan dilakukan di service
            $this->roleService->deleteRole($roleId);
            return response()->json(['message' => 'Role deleted successfully']);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function assignRole(Request $request)
    {
        try {
            $this->roleService->assignRole(
                $request->input('user_id'),
                $request->input('role_id')
            );
            
            return response()->json(['message' => 'Role berhasil ditambahkan']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
