<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
            // Return JSON for API requests with validation errors
            if ($request->expectsJson() || $request->is('api/*')) {
                \Log::error('Validation exception on API', [
                    'errors' => $e->errors(),
                    'path' => $request->getPathInfo()
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
