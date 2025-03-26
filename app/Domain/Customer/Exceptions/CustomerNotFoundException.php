<?php

namespace App\Domain\Customer\Exceptions;

use Exception;

class CustomerNotFoundException extends Exception
{
    public function __construct(int $customerId)
    {
        parent::__construct("Customer with ID {$customerId} not found");
    }
}
