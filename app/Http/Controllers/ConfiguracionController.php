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

        // Load the default template from the blade file for the editor
        $defaultTemplate = file_get_contents(resource_path('views/cotizaciones/pdf.blade.php'));

        return view('configuracion.edit', compact('configuracion', 'defaultTemplate'));
    }

    public function update(Request $request): RedirectResponse
    {
        $configuracion = Configuracion::query()->where('activo', true)->firstOrFail();

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'ruc' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'string', 'max:150'],
            'sitio_web' => ['nullable', 'string', 'max:150'],
            'cuentas_bancarias' => ['nullable', 'string'],
            'plazo_entrega_default' => ['nullable', 'string', 'max:150'],
            'forma_pago_default' => ['nullable', 'string', 'max:150'],
            'validez_oferta_default' => ['nullable', 'string', 'max:100'],
            'logistica_default' => ['nullable', 'string', 'max:255'],
            'pdf_template_cotizacion' => ['nullable', 'string'],
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
