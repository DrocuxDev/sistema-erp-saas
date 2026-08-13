<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRol
{
    public function handle(Request $request, Closure $next, string $rol): Response
    {
        if (! auth()->check() || auth()->user()->rol !== $rol) {
            abort(403);
        }

        return $next($request);
    }
}
