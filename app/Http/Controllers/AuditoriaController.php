<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $query = Auditoria::query()->with('usuario');

        if ($request->filled('tabla')) {
            $query->where('tabla', 'like', '%'.$request->tabla.'%');
        }

        if ($request->filled('accion') && in_array($request->accion, ['creacion', 'edicion', 'anulacion'], true)) {
            $query->where('accion', $request->accion);
        }

        if ($request->filled('usuario')) {
            $query->whereHas('usuario', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->usuario.'%');
            });
        }

        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        }

        $registros = $query->latest('id')->paginate(20)->withQueryString();

        return view('auditoria.index', compact('registros'));
    }
}
