<?php

namespace App\Http\Middleware;

use App\Support\MaintenanceMode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->getMethod(), ['GET', 'HEAD'])) {
            return $next($request);
        }

        if (!MaintenanceMode::enabled()) {
            return $next($request);
        }

        if (auth()->check() && auth()->user()->isAdmin()) {
            return $next($request);
        }

        if ($request->is('login') || $request->is('logout') || $request->is('theme/toggle')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Идут сервисные работы. Сохранение данных временно недоступно. Попробуйте позже.',
            ], 503);
        }

        return redirect()->back(302, [], '/')->with(
            'error',
            'Идут сервисные работы. Сохранение данных временно недоступно. Попробуйте позже.'
        );
    }
}
