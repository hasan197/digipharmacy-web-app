<?php

namespace App\Domain\Auth\Models;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;

class User
{
    private UserId $id;
    private string $name;
    private string $email;
    private string $password;
    private ?string $emailVerifiedAt;
    private bool $isActive = true;
    private bool $isBlocked = false;
    private Collection $roles;
    private array $permissions = [];

    public function __construct(
        UserId $id,
        string $name,
        string $email,
        string $password
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->roles = new Collection();
    }

    // Identity methods
    public function getId(): UserId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    // Email verification
    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function verifyEmail(): void
    {
        $this->emailVerifiedAt = date('Y-m-d H:i:s');
    }

    // Role management
    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains(fn($role) => $role->getName() === $roleName);
    }

    public function assignRole(Role $role): void
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    public function removeRole(string $roleName): void
    {
        $this->roles = $this->roles->reject(fn($role) => $role->getName() === $roleName);
    }

    public function getRoles(): array
    {
        return $this->roles->toArray();
    }

    // Permission management
    public function hasPermission(string $permission): bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function getPermissions(): array
    {
        $permissions = [];
        foreach ($this->roles as $role) {
            $permissions = array_merge($permissions, $role->getPermissions());
        }
        return array_unique($permissions);
    }

    // Password management
    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isBlocked(): bool
    {
        return $this->isBlocked;
    }

    public function setActive(bool $active): void
    {
        $this->isActive = $active;
    }

    public function setBlocked(bool $blocked): void
    {
        $this->isBlocked = $blocked;
    }

    public function verifyPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    public function updatePassword(string $newPassword): void
    {
        $this->password = Hash::make($newPassword);
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
