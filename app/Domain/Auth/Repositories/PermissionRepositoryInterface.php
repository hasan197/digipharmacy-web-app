<?php

namespace App\Domain\Auth\Repositories;

use App\Domain\Auth\Models\Permission;
use Illuminate\Support\Collection;

interface PermissionRepositoryInterface
{
    public function findById(int $id): ?Permission;
    public function findByName(string $name): ?Permission;
    public function getAll(): Collection;
}
