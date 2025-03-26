<?php

namespace App\Domain\Inventory\ValueObjects;

class StockLevel
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException("Stock level cannot be negative");
        }
        
        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function isLow(int $threshold = 10): bool
    {
        return $this->value <= $threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->value === 0;
    }

    public function add(int $quantity): self
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException("Quantity to add cannot be negative");
        }
        
        return new self($this->value + $quantity);
    }

    public function subtract(int $quantity): self
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException("Quantity to subtract cannot be negative");
        }
        
        if ($this->value < $quantity) {
            throw new \InvalidArgumentException("Cannot subtract more than available stock");
        }
        
        return new self($this->value - $quantity);
    }
} 