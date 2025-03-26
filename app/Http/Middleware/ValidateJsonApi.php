<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class ValidateJsonApi
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }

    public function terminate($request, $response)
    {
        // Convert validation exception responses to JSON API format
        if ($response->exception instanceof ValidationException) {
            return Response::json([
                'errors' => $response->exception->errors()
            ], 422);
        }

        return $response;
    }
}
