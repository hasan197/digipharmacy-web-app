<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Infrastructure\Auth\Mappers\UserMapper;

class AdminRoleMiddleware
{
    private UserMapper $userMapper;

    public function __construct(UserMapper $userMapper)
    {
        $this->userMapper = $userMapper;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = auth()->payload();
        $roles = $token->get('roles');
        
        if (!in_array('admin', $roles)) {
            return response()->json([
                'message' => 'Unauthorized. Admin role required.',
                'user_id' => $token->get('id'),
                'roles' => $roles
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
