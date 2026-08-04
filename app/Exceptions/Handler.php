<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (\Illuminate\Validation\ValidationException $e, $request) {
            // Always return JSON for /api/* requests, regardless of Accept header
            if ($request->is('api/*') || str_contains($request->getPathInfo(), '/api/')) {
                Log::error('Validation exception on API', [
                    'errors' => $e->errors(),
                    'path' => $request->getPathInfo(),
                    'expects_json' => $request->expectsJson()
                ]);
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return null;
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
