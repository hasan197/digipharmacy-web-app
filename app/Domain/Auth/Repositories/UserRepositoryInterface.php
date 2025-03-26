<?php

namespace App\Domain\Auth\Repositories;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\ValueObjects\UserId;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findById(UserId $id): ?User;
    public function save(User $user): void;
}
