<?php

namespace App\Infrastructure\Events;

use Illuminate\Support\Str;

abstract class DomainEvent
{
    public readonly string $id;
    public readonly \DateTime $occurredOn;

    public function __construct()
    {
        $this->id = Str::uuid();
        $this->occurredOn = new \DateTime();
    }

    /**
     * Get event name for logging and tracking
     */
    abstract public function getEventName(): string;
}
