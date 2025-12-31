<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isBanned()) {
            $banReason = auth()->user()->ban_reason ?? 'Не указана';
            auth()->logout();
            
            return redirect()->route('login')
                ->with('error', 'Ваш аккаунт заблокирован. Причина: ' . $banReason);
        }

        return $next($request);
    }
}
