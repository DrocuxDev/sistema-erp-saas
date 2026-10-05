<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckBusinessAction
{
    public function handle(Request $request, Closure $next, string $accion): mixed
    {
        $usuario = $request->user();
        abort_unless($usuario?->puedeAccion($accion), 403);

        return $next($request);
    }
}
