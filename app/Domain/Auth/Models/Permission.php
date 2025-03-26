<?php

namespace App\Domain\Auth\Models;

class Permission
{
    private ?int $id;
    private string $name;
    private string $description;
    private string $module;
    private string $action;

    public function __construct(
        string $name,
        string $description = '',
        string $module = '',
        string $action = '',
        ?int $id = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->module = $module;
        $this->action = $action;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
