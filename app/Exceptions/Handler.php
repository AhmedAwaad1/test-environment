<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleJsonResponse($e);
        }

        return parent::render($request, $e);
    }

    /**
     * Custom JSON error response handling using ResponseMixins
     */
    protected function handleJsonResponse(Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return Response::errorResponse(
                $e->getMessage(),
                $e->errors(),
                422
            );
        }

        if ($e instanceof ModelNotFoundException) {
            $model = str_replace('App\\Models\\', '', $e->getModel());
            return Response::errorResponse(
                "{$model} not found",
                [],
                404
            );
        }

        if ($e instanceof AuthenticationException) {
            return Response::errorResponse(
                'Unauthenticated',
                [],
                401
            );
        }

        if ($e instanceof HttpException) {
            return Response::errorResponse(
                $e->getMessage(),
                [],
                $e->getStatusCode()
            );
        }

        // Generic Exception handling using handleException mixin for logging and debug info
        return Response::handleException($e, 'process request');
    }
}
