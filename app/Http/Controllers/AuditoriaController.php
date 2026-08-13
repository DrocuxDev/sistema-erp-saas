<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $query = Auditoria::query()->with('usuario');

        // Filtro por tabla
        if ($request->filled('tabla')) {
            $query->where('tabla', 'like', '%' . $request->tabla . '%');
        }

        // Filtro por acción
        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }

        // Filtro por usuario
        if ($request->filled('usuario')) {
            $query->whereHas('usuario', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->usuario . '%');
            });
        }

        $registros = $query->latest('created_at')->paginate(20)->withQueryString();

        return view('auditoria.index', compact('registros'));
    }
}
