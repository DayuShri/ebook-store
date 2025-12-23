<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Daftarkan alias middleware kamu yang sudah ada
        $middleware->alias([
            'token.valid' => \App\Http\Middleware\EnsureTokenIsValid::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
<<<<<<< HEAD
=======

        $middleware->validateCsrfTokens(except: [
            'api/v1/payment/callback',
            'api/payment/callback'
        ]);
        
        // Enable CORS for API routes
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
>>>>>>> 2347f10c6476bdca24e206f24d6746d0805d9124
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Force JSON responses for API routes
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->is('hmvc/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                ], 404);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->is('hmvc/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->is('hmvc/*')) {
                // Don't override custom exception responses
                if ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                    return null;
                }

                // For other exceptions, return generic error
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'An error occurred',
                ], $statusCode);
            }
        });
<<<<<<< HEAD
    })->create();
=======
    })->create();
>>>>>>> 2347f10c6476bdca24e206f24d6746d0805d9124
