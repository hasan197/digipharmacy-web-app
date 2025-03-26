<?php

namespace App\Domain\Inventory\ValueObjects;

class InventoryTransactionType
{
    public const STOCK_IN = 'stock_in';
    public const STOCK_OUT = 'stock_out';
    public const ADJUSTMENT = 'adjustment';
    public const SALES = 'sales';
    public const RETURN = 'return';
    public const EXPIRED = 'expired';

    private string $value;

    public function __construct(string $value)
    {
        $allowedValues = [
            self::STOCK_IN,
            self::STOCK_OUT,
            self::ADJUSTMENT,
            self::SALES,
            self::RETURN,
            self::EXPIRED
        ];

        if (!in_array($value, $allowedValues)) {
            throw new \InvalidArgumentException("Invalid transaction type: {$value}");
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isStockIn(): bool
    {
        return $this->value === self::STOCK_IN;
    }

    public function isStockOut(): bool
    {
        return $this->value === self::STOCK_OUT;
    }

    public function isAdjustment(): bool
    {
        return $this->value === self::ADJUSTMENT;
    }

    public function isSales(): bool
    {
        return $this->value === self::SALES;
    }

    public function isReturn(): bool
    {
        return $this->value === self::RETURN;
    }

    public function isExpired(): bool
    {
        return $this->value === self::EXPIRED;
    }

    public function affectsStockPositively(): bool
    {
        return in_array($this->value, [self::STOCK_IN, self::RETURN]);
    }

    public function affectsStockNegatively(): bool
    {
        return in_array($this->value, [self::STOCK_OUT, self::SALES, self::EXPIRED]);
    }
} 