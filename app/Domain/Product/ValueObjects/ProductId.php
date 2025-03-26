<?php

namespace App\Domain\Product\ValueObjects;

class ProductId
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

    public function equals(ProductId $other): bool
    {
        return $this->value === $other->getValue();
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }
} 