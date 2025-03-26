<?php

namespace App\Domain\Auth\Exceptions;

class UnauthorizedException extends \Exception
{
    public function __construct(string $message = "Unauthorized")
    {
        parent::__construct($message, 403);
    }

    public function render()
    {
        return response()->json([
            'error' => 'Unauthorized',
            'message' => $this->getMessage()
        ], $this->getCode());
    }
}
