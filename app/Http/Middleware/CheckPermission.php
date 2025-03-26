<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        if (!auth()->user()->hasPermission($permission)) {
            return response()->json(['error' => 'Unauthorized. Missing permission: ' . $permission], 403);
        }

        return $next($request);
    }
}
