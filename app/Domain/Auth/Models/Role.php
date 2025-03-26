<?php

namespace App\Domain\Auth\Models;

use Illuminate\Support\Collection;

class Role
{
    private ?int $id;
    private string $name;
    private string $description;
    private array $permissions = [];

    // Empty constructor for Laravel model binding
    public function __construct()
    {
        // Default values can be set here if needed
    }

    // Factory method
    public static function create(string $name, string $description = '', array $permissions = [], ?int $id = null): self
    {
        $role = new self();
        $role->id = $id;
        $role->name = $name;
        $role->description = $description;
        $role->permissions = $permissions;

        return $role;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }

    public function addPermission(string $permission): void
    {
        if (!$this->hasPermission($permission)) {
            $this->permissions[] = $permission;
        }
    }

    public function removePermission(string $permission): void
    {
        $this->permissions = array_filter(
            $this->permissions,
            fn($p) => $p !== $permission
        );
    }
}
