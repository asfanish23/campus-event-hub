<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugSessionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionId = $request->session()->getId();
        $isAuthenticated = auth()->check();
        
        \Log::info('DEBUG: Incoming Request', [
            'path' => $request->path(),
            'session_id' => $sessionId,
            'is_authenticated' => $isAuthenticated,
            'user_id' => $isAuthenticated ? auth()->user()->id : null,
            'session_cookie_sent' => $request->cookie(config('session.cookie')),
        ]);

        $response = $next($request);

        \Log::info('DEBUG: Outgoing Response', [
            'path' => $request->path(),
            'session_id' => $sessionId,
            'status_code' => $response->getStatusCode(),
        ]);

        return $response;
    }
}
