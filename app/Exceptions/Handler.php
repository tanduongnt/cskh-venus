<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;

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
        $this->renderable(function (Throwable $ex, Request $request) {
            if ($request->is('api/*')) {
                if (!$ex instanceof ValidationException) {
                    if ($ex instanceof AuthenticationException) {
                        return response()->json([
                            'error' => true,
                            'message' => __('auth.token'),
                        ], Response::HTTP_UNAUTHORIZED);
                    } else {
                        return response()->json([
                            'error' => true,
                            'name' => get_class($ex),
                            'code' => $ex->getCode(),
                            'message' => $ex->getMessage(),
                            // 'trace' => $ex->getTrace()
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
            }
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
