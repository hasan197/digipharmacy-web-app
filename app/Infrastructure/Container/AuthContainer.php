<?php

namespace App\Infrastructure\Container;

use App\Domain\Auth\Models\Role;
use App\Domain\Auth\Services\RoleManagementService;
use App\Domain\Auth\Services\AccessControlService;
use App\Domain\Auth\Repositories\RoleRepositoryInterface;
use App\Domain\Auth\Repositories\PermissionRepositoryInterface;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Infrastructure\Auth\Repositories\RoleRepository;
use App\Infrastructure\Auth\Repositories\PermissionRepository;
use App\Infrastructure\Auth\Repositories\UserRepository;
use App\Infrastructure\Auth\Mappers\RoleMapper;
use App\Infrastructure\Auth\Mappers\PermissionMapper;
use App\Infrastructure\Auth\Mappers\UserMapper;
use Illuminate\Support\ServiceProvider;

class AuthContainer extends ServiceProvider
{
    public function register(): void
    {
        // Register repositories
        $this->app->bind(RoleRepositoryInterface::class, function ($app) {
            return new RoleRepository(new RoleMapper());
        });

        $this->app->bind(PermissionRepositoryInterface::class, function ($app) {
            return new PermissionRepository(new PermissionMapper());
        });

        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            return new UserRepository(new UserMapper());
        });

        // Register services
        $this->app->bind(RoleManagementService::class, function ($app) {
            return new RoleManagementService(
                $app->make(RoleRepositoryInterface::class),
                $app->make(PermissionRepositoryInterface::class),
                $app->make(AccessControlService::class),
                $app->make(UserRepositoryInterface::class)
            );
        });

        $this->app->bind(AccessControlService::class, function ($app) {
            return new AccessControlService(
                $app->make(UserRepositoryInterface::class)
            );
        });
    }
}
