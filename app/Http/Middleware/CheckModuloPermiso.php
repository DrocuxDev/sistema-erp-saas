<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuloPermiso
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $modulo): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // 1. Si el usuario está inactivo, cerrar sesión o bloquear
        if (! $user->activo) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Su usuario se encuentra inactivo. Contacte al Administrador.');
        }

        // 2. Verificar permiso sobre el módulo
        if (! $user->tieneModulo($modulo)) {
            abort(403, 'No tiene permisos asignados para acceder a este módulo (' . $modulo . ').');
        }

        return $next($request);
    }
}
