<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use App\Models\SeoGoneUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ответ 410 Gone для URL, удалённых из проекта (SEO).
 * Выполняется до редиректов из БД: удалённая страница не должна уходить 301.
 */
class RespondGoneUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $path = Redirect::normalizePath($request->path() ?: '/');

        if ($path !== '/' && SeoGoneUrl::query()->where('path', $path)->exists()) {
            return response()->view('errors.410', [], 410);
        }

        return $next($request);
    }
}
