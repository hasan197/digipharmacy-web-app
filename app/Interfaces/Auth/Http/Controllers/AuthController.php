<?php

namespace App\Interfaces\Auth\Http\Controllers;

use App\Application\Auth\Commands\LoginCommand;
use App\Application\Auth\Commands\LoginCommandHandler;
use App\Application\Auth\DTOs\LoginRequestDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private LoginCommandHandler $loginHandler
    ) {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $loginDTO = LoginRequestDTO::fromRequest($validated, $request->ip());
        $command = new LoginCommand($loginDTO);
        $result = $this->loginHandler->handle($command);

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
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh(): JsonResponse
    {
        return response()->json([
            'access_token' => auth()->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
