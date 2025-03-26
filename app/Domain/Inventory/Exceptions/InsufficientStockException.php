<?php

namespace App\Domain\Inventory\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    private int $productId;
    private int $requestedQuantity;
    private int $availableQuantity;

    public function __construct(int $productId, int $requestedQuantity, int $availableQuantity)
    {
        $this->productId = $productId;
        $this->requestedQuantity = $requestedQuantity;
        $this->availableQuantity = $availableQuantity;

        $message = "Insufficient stock for product ID {$productId}. Requested: {$requestedQuantity}, Available: {$availableQuantity}";
        parent::__construct($message);
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getRequestedQuantity(): int
    {
        return $this->requestedQuantity;
    }

    public function getAvailableQuantity(): int
    {
        return $this->availableQuantity;
    }
} 