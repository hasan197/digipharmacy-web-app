<?php

namespace App\Domain\Customer\ValueObjects;

class CustomerId
{
    private int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(CustomerId $other): bool
    {
        return $this->value === $other->getValue();
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }
}
