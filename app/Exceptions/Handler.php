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
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    public function render($request, Throwable $e)
    {
        if ($e instanceof FileNotFoundException) {
            return response()->json([
                'message' => 'File not found',
                'status' => 'FileNotFoundException',
                'code' => 404,
            ], 404);
        }

        if ($e instanceof AccessDeniedHttpException) {
            return response()->json([
                'message' => 'Access Denied',
                'status' => 'Unauthorized',
                'code' => 403,
            ], 403);
        }

        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'message' => last(explode('\\', $e->getModel())) . ' not found',
                'status' => 'Not Found',
                'code' => 404,
            ], 404);
        }

        // if ($e instanceof \Illuminate\Database\QueryException) {
        //     return response()->json([
        //         'message' => 'Database error',
        //         'code' => $e->getCode(),
        //     ], 500);
        // }

        if ($e instanceof MissingScopeException) {
            return response()->json([
                'error' => 'Unauthenticated',
            ], 403);
        }

        if ($e instanceof AuthorizationException) {
            return response()->json([
                'error' => 'Unauthenticated',
            ], 403);
        }

        return parent::render($request, $e);
    }
}
