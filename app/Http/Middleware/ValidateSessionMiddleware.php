<?php

namespace App\Http\Middleware;

use App\Models\Club;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSessionMiddleware
{
    /**
     * Handle an incoming request.
     * Validates that the user's session is still active and valid.
     * Prevents access to protected pages if session has expired or is invalid.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (auth()->check()) {
            // Verify the user still exists in database (in case they were deleted/disabled)
            $user = auth()->user();
            
            if (!$user || !$user->exists) {
                // User no longer exists - logout immediately
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')->with('error', 'Your session is no longer valid. Please login again.');
            }

            // Check if session token is still valid (regenerate if needed)
            if (!$request->session()->has('_token')) {
                $request->session()->regenerateToken();
            }

            if ($user->role === 'admin') {
                $club = Club::find($user->club_id);

                if (! $club || $club->status !== Club::STATUS_ACTIVE) {
                    auth()->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->with('error', 'Your club is currently inactive. Please contact HEP.');
                }
            }
        }

        return $next($request);
    }
}
