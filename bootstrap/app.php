<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {
            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            if ($status === 404) {
                return Inertia::render('NotFound')
                    ->toResponse($request)
                    ->setStatusCode(404);
            }

            if ($status === 403) {
                return Inertia::render('NotFound', [
                    'message' => 'У вас нет прав для просмотра этой страницы'
                ])
                ->toResponse($request)
                ->setStatusCode(403);
            }

            return null;
        });
    })->create();
