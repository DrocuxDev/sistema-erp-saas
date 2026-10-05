<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\CuentaBancaria;
use App\Services\CotizacionDocumento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    public function edit(): View
    {
        $configuracion = Configuracion::query()->where('activo', true)->firstOrFail();
        $cuentas = CuentaBancaria::query()->orderBy('orden')->orderBy('id')->get();

        return view('configuracion.edit', compact('configuracion', 'cuentas'));
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
            'pct_igv' => ['required', 'numeric', 'min:0', 'max:100'],
            'pct_retencion' => ['required', 'numeric', 'min:0', 'max:100'],
            'pct_comision_venta' => ['required', 'numeric', 'min:0', 'max:100'],
            'pct_comision_ganancia' => ['required', 'numeric', 'min:0', 'max:100'],
            // Apartado "Cotización"
            'incluir_portada' => ['nullable', 'boolean'],
            'color_principal' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_tabla_encabezado' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'texto_saludo' => ['nullable', 'string', 'max:2000'],
            'condiciones_adicionales_default' => ['nullable', 'string', 'max:2000'],
            'texto_firma' => ['nullable', 'string', 'max:1000'],
            'titular_cuenta' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'portada' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('portada')) {
            $this->validarProporcionA4($request->file('portada'));
        }

        $logoAnterior = $configuracion->logo_path;
        $portadaAnterior = $configuracion->portada_imagen_path;

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('documentos', 'public');
        }

        if ($request->hasFile('portada')) {
            $validated['portada_imagen_path'] = $request->file('portada')->store('documentos', 'public');
        }

        $validated['incluir_portada'] = $request->boolean('incluir_portada');

        unset($validated['logo'], $validated['portada']);

        $configuracion->update($validated);

        if ($request->hasFile('logo')) {
            CotizacionDocumento::eliminarImagenSinReferencias($logoAnterior);
        }
        if ($request->hasFile('portada')) {
            CotizacionDocumento::eliminarImagenSinReferencias($portadaAnterior);
        }

        return redirect()->route('configuracion.edit')
            ->with('success', 'Configuración del sistema actualizada correctamente.');
    }

    /**
     * La portada debe tener proporción A4 vertical (alto/ancho ≈ 1.4142).
     */
    private function validarProporcionA4($archivo): void
    {
        $info = @getimagesize($archivo->getRealPath());
        if ($info === false || empty($info[0])) {
            throw ValidationException::withMessages([
                'portada' => 'No se pudo leer la imagen de portada. Sube un JPG o PNG válido.',
            ]);
        }

        $proporcion = $info[1] / $info[0];

        if ($proporcion < 1.30 || $proporcion > 1.55) {
            throw ValidationException::withMessages([
                'portada' => 'La imagen de portada debe tener proporción A4 vertical (más alta que ancha, como una hoja A4). Recorta o ajusta la imagen e inténtalo de nuevo.',
            ]);
        }
    }

    /**
     * Quita el logo de la configuración y elimina el archivo si no tiene referencias.
     * Las cotizaciones nuevas salen sin logo; las emitidas conservan el suyo.
     */
    public function destroyLogo(): RedirectResponse
    {
        $configuracion = Configuracion::query()->where('activo', true)->firstOrFail();

        if ($configuracion->logo_path) {
            $ruta = $configuracion->logo_path;
            $configuracion->update(['logo_path' => null]);
            CotizacionDocumento::eliminarImagenSinReferencias($ruta);
        }

        return redirect()->route('configuracion.edit')
            ->with('success', 'Logo borrado correctamente.');
    }

    /**
     * Borra la imagen de portada y apaga el interruptor (sin imagen no hay portada).
     */
    public function destroyPortada(): RedirectResponse
    {
        $configuracion = Configuracion::query()->where('activo', true)->firstOrFail();

        $ruta = $configuracion->portada_imagen_path;
        $configuracion->update(['portada_imagen_path' => null, 'incluir_portada' => false]);
        CotizacionDocumento::eliminarImagenSinReferencias($ruta);

        return redirect()->route('configuracion.edit')
            ->with('success', 'Portada borrada correctamente.');
    }

    public function storeCuenta(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'banco' => ['required', 'string', 'max:100'],
            'moneda' => ['required', 'string', 'max:10'],
            'numero_cuenta' => ['required', 'string', 'max:100'],
            'cci' => ['nullable', 'string', 'max:100'],
            'mostrar_en_pdf' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $validated['mostrar_en_pdf'] = $request->boolean('mostrar_en_pdf', true);
        $validated['orden'] ??= CuentaBancaria::query()->max('orden') + 1;

        CuentaBancaria::create($validated);

        return redirect()->route('configuracion.edit')
            ->with('success', 'Cuenta bancaria agregada correctamente.');
    }

    public function updateCuenta(Request $request, CuentaBancaria $cuenta): RedirectResponse
    {
        $validated = $request->validate([
            'banco' => ['required', 'string', 'max:100'],
            'moneda' => ['required', 'string', 'max:10'],
            'numero_cuenta' => ['required', 'string', 'max:100'],
            'cci' => ['nullable', 'string', 'max:100'],
            'mostrar_en_pdf' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $validated['mostrar_en_pdf'] = $request->boolean('mostrar_en_pdf');

        $cuenta->update($validated);

        return redirect()->route('configuracion.edit')
            ->with('success', 'Cuenta bancaria actualizada correctamente.');
    }

    public function destroyCuenta(CuentaBancaria $cuenta): RedirectResponse
    {
        $cuenta->delete();

        return redirect()->route('configuracion.edit')
            ->with('success', 'Cuenta bancaria eliminada correctamente.');
    }

    /**
     * Vista previa: PDF de ejemplo con datos ficticios y la configuración actual.
     */
    public function pdfEjemplo(Request $request)
    {
        $configuracion = Configuracion::query()->where('activo', true)->firstOrFail();
        $cotizacion = CotizacionDocumento::ejemplo();
        $doc = CotizacionDocumento::snapshot($configuracion);
        $datos = CotizacionDocumento::datosPdf($cotizacion, $doc);

        if ($request->has('html')) {
            return response()->view('cotizaciones.pdf', $datos);
        }

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('cotizaciones.pdf', $datos)
                ->setPaper('a4', 'portrait')
                ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);

            return $pdf->stream('ejemplo-cotizacion.pdf');
        }

        return response()->view('cotizaciones.pdf', $datos);
    }
}
