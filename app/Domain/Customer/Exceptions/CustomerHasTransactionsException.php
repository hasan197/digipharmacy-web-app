<?php

namespace App\Domain\Customer\Exceptions;

use Exception;

class CustomerHasTransactionsException extends Exception
{
    public function __construct(int $customerId)
    {
        parent::__construct("Cannot delete customer with ID {$customerId} because they have transaction history");
    }
}
