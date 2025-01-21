<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // Check if the exception is a validation exception
        if ($exception instanceof ValidationException) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $exception->errors(),
            ], 422);
        } else if ($exception instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Unauthorized Request',
            ], 401);
        }  else if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Resource Not Found',
            ], 404);
        } else {
            return response()->json([
                'message' => 'Server Error',
                'errors' => $exception->getMessage(),
            ], 500);
        }

        // Call the parent render method for other exceptions
        // return parent::render($request, $exception);
    }
}
