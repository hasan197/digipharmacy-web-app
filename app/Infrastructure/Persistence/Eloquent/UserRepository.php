<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Auth\Models\User as DomainUser;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\ValueObjects\UserId;
use App\Models\User as EloquentUser;

class UserRepository implements UserRepositoryInterface
{
    public function findById(UserId $id): ?DomainUser
    {
        $eloquentUser = EloquentUser::find($id->getValue());
        
        if (!$eloquentUser) {
            return null;
        }

        return $this->toDomainModel($eloquentUser);
    }

    private function toDomainModel(EloquentUser $eloquentUser): DomainUser
    {
        return new DomainUser(
            new UserId($eloquentUser->id),
            $eloquentUser->email
        );
    }
} 