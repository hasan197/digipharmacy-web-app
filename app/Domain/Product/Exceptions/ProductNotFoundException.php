<?php

namespace App\Domain\Product\Exceptions;

use Exception;

class ProductNotFoundException extends Exception
{
    private int $productId;

    public function __construct(int $productId)
    {
        $this->productId = $productId;
        parent::__construct("Product with ID {$productId} not found");
    }

    public function getProductId(): int
    {
        return $this->productId;
    }
} 