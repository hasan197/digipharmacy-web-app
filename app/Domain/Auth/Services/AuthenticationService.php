<?php

namespace App\Domain\Auth\Services;

use App\Application\Contracts\Auth\AuthenticationServiceInterface;
use App\Domain\Auth\Models\Credentials;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationService implements AuthenticationServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function authenticate(Credentials $credentials): array
    {
        $user = $this->userRepository->findByEmail($credentials->getEmail());
        
        if (!$user || !Hash::check($credentials->getPassword(), $user->getPassword())) {
            return [
                'success' => false,
                'message' => 'Invalid credentials',
                'status' => 401
            ];
        }

        $token = auth()->login($user);

        return [
            'success' => true,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $user
        ];
    }

    public function logout(): void
    {
        auth()->logout();
    }

    public function refresh(): array
    {
        return [
            'token' => auth()->refresh(),
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60
        ]
    }

    public function getCurrentUser(): ?array
    {
        if (!$user = Auth::user()) {
            return null;
        }

        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()->map(fn($role) => [
                'id' => $role->getId(),
                'name' => $role->getName()
            ])->toArray()
        ];
}
