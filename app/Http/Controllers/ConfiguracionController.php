<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    public function edit(): View
    {
        $configuracion = Configuracion::query()->where('activo', true)->firstOrFail();

        return view('configuracion.edit', compact('configuracion'));
    }

    public function update(Request $request): RedirectResponse
    {
        $configuracion = Configuracion::query()->where('activo', true)->firstOrFail();

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'pct_igv' => ['required', 'numeric', 'min:0', 'max:100'],
            'pct_retencion' => ['required', 'numeric', 'min:0', 'max:100'],
            'pct_comision_venta' => ['required', 'numeric', 'min:0', 'max:100'],
            'pct_comision_ganancia' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $configuracion->update($validated);

        return redirect()->route('configuracion.edit')
            ->with('success', 'Configuración del sistema actualizada correctamente.');
    }
}
