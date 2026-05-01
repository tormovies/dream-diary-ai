<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectFromDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = Redirect::normalizePath($request->path() ?: '/');

        $redirect = Redirect::findActiveCached($path);

        if ($redirect) {
            $to = $redirect['to_url'];
            if (!str_starts_with($to, 'http://') && !str_starts_with($to, 'https://')) {
                $to = '/' . ltrim($to, '/');
            }
            return redirect()->to($to, $redirect['status_code']);
        }

        return $next($request);
    }
}
