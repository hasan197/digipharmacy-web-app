<?php

namespace App\Domain\Product\ValueObjects;

class ProductStatus
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';
    public const DISCONTINUED = 'discontinued';

    private string $value;

    public function __construct(string $value)
    {
        $this->validateStatus($value);
        $this->value = $value;
    }

    private function validateStatus(string $value): void
    {
        $validStatuses = [self::ACTIVE, self::INACTIVE, self::DISCONTINUED];
        
        if (!in_array($value, $validStatuses)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid product status: %s. Valid statuses are: %s', 
                    $value, 
                    implode(', ', $validStatuses)
                )
            );
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->value === self::INACTIVE;
    }

    public function isDiscontinued(): bool
    {
        return $this->value === self::DISCONTINUED;
    }

    public function equals(ProductStatus $other): bool
    {
        return $this->value === $other->getValue();
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function inactive(): self
    {
        return new self(self::INACTIVE);
    }

    public static function discontinued(): self
    {
        return new self(self::DISCONTINUED);
    }
} 