<?php

namespace App\Http\Middleware;

use App\Services\LogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Start timing the request
        $startTime = microtime(true);
        
        // Process the request
        $response = $next($request);
        
        // Calculate request duration
        $duration = microtime(true) - $startTime;
        
        // Get response status code
        $statusCode = $response->getStatusCode();
        
        // Prepare log context
        $context = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'duration' => round($duration * 1000, 2), // Convert to milliseconds
            'status_code' => $statusCode,
            'user_agent' => $request->userAgent()
        ];
        
        // Add request data for non-GET requests
        if ($request->method() !== 'GET') {
            $context['request_data'] = $request->except(['password', 'password_confirmation']);
        }
        
        // Log based on response status
        if ($statusCode >= 500) {
            LogService::error('API request failed', $context);
        } elseif ($statusCode >= 400) {
            LogService::error('API request error', $context);
        } else {
            LogService::pos('API request completed', $context);
        }
        
        return $response;
    }
}
