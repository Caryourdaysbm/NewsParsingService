<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $key = $request->ip(); // Use the IP address as a unique identifier
    
        $maxRequests = 10; // Maximum allowed requests
        $decayMinutes = 1; // Time window (in minutes)
    
        $limiter = app(RateLimiter::class);
        if ($limiter->tooManyAttempts($key, $maxRequests, $decayMinutes)) {
            return response('Too Many Attempts.', 429); // Return an error response
        }
    
        $limiter->hit($key, $decayMinutes);
    
        return $next($request);
    }
    
}
