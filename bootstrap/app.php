<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);

        // 301 редиректы из БД — до маршрутизации
        $middleware->prepend(\App\Http\Middleware\RedirectFromDatabase::class);

        // Проверка бана для всех авторизованных пользователей
        $middleware->web(append: [
            \App\Http\Middleware\CheckBanned::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpException $e, Request $request) {
            $status = $e->getStatusCode();
            if ($status === 403) {
                return response()->view('errors.403', ['exception' => $e], 403);
            }
            if ($status === 404) {
                return response()->view('errors.404', ['exception' => $e], 404);
            }
            return null;
        });
    })->create();
