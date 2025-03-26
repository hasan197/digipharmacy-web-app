<?php

namespace App\Domain\Inventory\Exceptions;

use Exception;

class InventoryTransactionNotFoundException extends Exception
{
    public function __construct(int $transactionId)
    {
        parent::__construct("Inventory transaction with ID {$transactionId} not found");
    }
} 