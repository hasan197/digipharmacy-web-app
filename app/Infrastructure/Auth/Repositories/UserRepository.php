<?php

namespace App\Infrastructure\Auth\Repositories;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\ValueObjects\UserId;
use App\Models\User as EloquentUser;

class UserRepository implements UserRepositoryInterface
{
    public function findById(UserId $id): ?User
    {
        $eloquentUser = EloquentUser::find($id->getValue());
        
        if (!$eloquentUser) {
            return null;
        }

        return $this->toDomainModel($eloquentUser);
    }

    public function findByEmail(string $email): ?User
    {
        $eloquentUser = EloquentUser::where('email', $email)->first();
        
        if (!$eloquentUser) {
            return null;
        }

        return $this->toDomainModel($eloquentUser);
    }

    public function save(User $user): void
    {
        $eloquentUser = EloquentUser::find($user->getId()->getValue());
        
        if (!$eloquentUser) {
            $eloquentUser = new EloquentUser();
        }

        // Map domain model to eloquent
        $eloquentUser->name = $user->getName();
        $eloquentUser->email = $user->getEmail();
        // ... map other properties

        $eloquentUser->save();
    }

    private function toDomainModel(EloquentUser $eloquentUser): User
    {
        $user = new User(
            $eloquentUser->name,
            $eloquentUser->email
            // ... other properties
        );
        $user->setId(new UserId($eloquentUser->id));
        
        return $user;
    }
}
