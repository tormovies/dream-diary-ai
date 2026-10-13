<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withSchedule(function (Schedule $schedule): void {
        $maintenanceOff = fn () => ! \App\Support\MaintenanceMode::enabled();

        $schedule->command('seo:yandex-sync')->everyFiveMinutes()->withoutOverlapping(15)->when($maintenanceOff);

        // Индексация новых толкований по символам/локациям/тегам (раз в день)
        $schedule->command('interpretations:index-entities', ['--only-new' => true])->daily()->when($maintenanceOff);
        // Агрегация сущностей по дням в dream_entity_daily (для статистики за день и сравнений)
        $schedule->command('interpretations:aggregate-entity-daily', ['--yesterday' => true])->dailyAt('01:00')->when($maintenanceOff);
        // Журнал cookie-согласий и условные HTTP-логи
        $schedule->command('consent:prune-logs', ['--days' => 730])->dailyAt('02:15')->when($maintenanceOff);
        $schedule->command('logs:prune-requests', ['--days' => 90])->dailyAt('02:30')->when($maintenanceOff);
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);

        // 301 редиректы из БД — до маршрутизации (prepend идёт в начало стека)
        $middleware->prepend(\App\Http\Middleware\RedirectFromDatabase::class);
        // 410 Gone — раньше редиректов: удалённые URL не должны уходить по 301
        $middleware->prepend(\App\Http\Middleware\RespondGoneUrl::class);

        // Проверка бана для всех авторизованных пользователей
        $middleware->web(append: [
            \App\Http\Middleware\CheckBanned::class,
            \App\Http\Middleware\CheckMaintenanceMode::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['current_password', 'password', 'password_confirmation', 'token']);
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
