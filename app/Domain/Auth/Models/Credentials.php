<?php

namespace App\Domain\Auth\Models;

class Credentials
{
    public function __construct(
        private string $email,
        private string $password,
        private string $ipAddress
    ) {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }
}
