<?php

namespace App\Infrastructure\Events\Auth;

use App\Domain\Auth\Models\Role;
use App\Infrastructure\Events\DomainEvent;

class RoleCreated extends DomainEvent
{
    public function __construct(
        public readonly Role $role
    ) {
        parent::__construct();
    }

    public function getEventName(): string
    {
        return 'role.created';
    }
}
