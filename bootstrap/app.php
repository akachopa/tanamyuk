<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Bearer tokens only. Cookie/CSRF stateful SPA auth is intentionally off.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = 500;
            $message = $e->getMessage() !== '' ? $e->getMessage() : 'Terjadi kesalahan.';
            $errors = null;

            if ($e instanceof ValidationException) {
                $status = $e->status;
                $errors = $e->errors();
            } elseif ($e instanceof AuthenticationException) {
                $status = 401;
                $message = 'Unauthenticated.';
            } elseif ($e instanceof NotFoundHttpException) {
                $status = 404;
                $message = 'Data tidak ditemukan.';
            } elseif ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                if ($message === '') {
                    $message = 'Terjadi kesalahan.';
                }
            } elseif (! config('app.debug')) {
                $message = 'Terjadi kesalahan pada server.';
            }

            $payload = [
                'success' => false,
                'message' => $message,
            ];

            if ($errors !== null) {
                $payload['errors'] = $errors;
            }

            return response()->json($payload, $status);
        });
    })->create();
