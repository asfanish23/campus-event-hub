<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCacheMiddleware
{
    /**
     * Handle an incoming request.
     * Prevents browser caching of protected pages to avoid bfcache/back button cache issues
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Disable caching for authenticated users (protected pages)
        if (auth()->check()) {
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
            
            // Prevent back/forward cache (bfcache)
            $response->header('X-UA-Compatible', 'IE=edge');
        }

        return $response;
    }
}
