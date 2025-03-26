<?php

namespace App\Http\Controllers;

use App\Application\Contracts\Auth\AuthenticationServiceInterface;
use App\Domain\Auth\Models\Credentials;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthenticationServiceInterface $authService
    ) {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = new Credentials(
            email: $validated['email'],
            password: $validated['password'],
            ipAddress: $request->ip()
        );

        $result = $this->authService->authenticate($credentials);

        if (!$result['success']) {
            return response()->json(['error' => $result['message']], $result['status']);
        }

        return response()->json([
            'access_token' => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
            'user' => $result['user']
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh(): JsonResponse
    {
        $result = $this->authService->refresh();
        return response()->json($result);
    }

    public function me(): JsonResponse
    {
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return response()->json($user);
    }
}
