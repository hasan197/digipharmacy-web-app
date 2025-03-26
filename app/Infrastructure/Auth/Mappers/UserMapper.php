<?php

namespace App\Infrastructure\Auth\Mappers;

use App\Domain\Auth\Models\User as DomainUser;
use App\Models\User as EloquentUser;

class UserMapper
{
    public function toDomain(EloquentUser $eloquentUser): DomainUser
    {
        $user = new DomainUser(
            name: $eloquentUser->name,
            email: $eloquentUser->email,
            password: $eloquentUser->password
        );

        // Map roles and permissions
        foreach ($eloquentUser->roles as $role) {
            $user->addRole($role->name);
        }

        foreach ($eloquentUser->permissions as $permission) {
            $user->addPermission($permission->name);
        }

        return $user;
    }

    public function toEloquent(DomainUser $domainUser): EloquentUser
    {
        $user = new EloquentUser();
        $user->name = $domainUser->getName();
        $user->email = $domainUser->getEmail();
        
        // Only set password if it's a new user
        if (!$user->exists) {
            $user->password = $domainUser->getPassword();
        }

        return $user;
    }
}
