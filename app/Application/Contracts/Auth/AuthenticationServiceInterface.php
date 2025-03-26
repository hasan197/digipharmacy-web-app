<?php

namespace App\Application\Contracts\Auth;

use App\Domain\Auth\Models\Credentials;

interface AuthenticationServiceInterface
{
    /**
     * Authenticate user with credentials
     */
    public function authenticate(Credentials $credentials): array;

    /**
     * Logout user
     */
    public function logout(): void;

    /**
     * Refresh JWT token
     */
    public function refresh(): array;

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?array;
}
