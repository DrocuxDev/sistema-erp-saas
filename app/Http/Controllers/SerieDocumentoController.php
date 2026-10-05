<?php

namespace App\Http\Controllers;

use App\Models\SerieDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SerieDocumentoController extends Controller
{
    public function index(Request $request)
    {
        $query = SerieDocumento::query()->orderBy('tipo_documento')->orderBy('serie');

        if ($request->filled('tipo_documento') && $request->tipo_documento !== 'TODOS') {
            $query->where('tipo_documento', $request->tipo_documento);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('serie', 'like', "%{$q}%")
                    ->orWhere('descripcion', 'like', "%{$q}%");
            });
        }

        if ($request->has('solo_activos') && $request->solo_activos == '1') {
            $query->where('activo', true);
        }

        $series = $query->paginate(15)->withQueryString();
        $tipos = SerieDocumento::tiposDisponibles();

        $totalesPorTipo = SerieDocumento::selectRaw('tipo_documento, COUNT(*) as total')
            ->groupBy('tipo_documento')
            ->pluck('total', 'tipo_documento');

        return view('series-documentos.index', compact('series', 'tipos', 'totalesPorTipo'));
    }

    public function create()
    {
        $tipos = SerieDocumento::tiposDisponibles();

        return view('series-documentos.create', compact('tipos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_documento' => ['required', 'string', 'max:50'],
            'serie' => [
                'required',
                'string',
                'max:10',
                Rule::unique('series_documentos')->where('tipo_documento', $request->tipo_documento),
            ],
            'descripcion' => ['nullable', 'string', 'max:150'],
            'correlativo_actual' => ['required', 'integer', 'min:0'],
            'longitud_correlativo' => ['required', 'integer', 'min:1', 'max:12'],
            'es_predeterminado' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $esPredeterminado = $request->has('es_predeterminado') && (bool) $request->es_predeterminado;

        if ($esPredeterminado) {
            SerieDocumento::where('tipo_documento', $validated['tipo_documento'])
                ->update(['es_predeterminado' => false]);
        }

        $serie = SerieDocumento::create([
            'tipo_documento' => $validated['tipo_documento'],
            'serie' => strtoupper(trim($validated['serie'])),
            'descripcion' => $validated['descripcion'] ?? null,
            'correlativo_actual' => (int) $validated['correlativo_actual'],
            'longitud_correlativo' => (int) $validated['longitud_correlativo'],
            'es_predeterminado' => $esPredeterminado,
            'activo' => $request->has('activo') ? (bool) $request->activo : true,
        ]);

        return redirect()->route('series-documentos.index')
            ->with('success', "Serie '{$serie->serie}' registrada exitosamente.");
    }

    public function edit(SerieDocumento $series_documento)
    {
        $serie = $series_documento;
        $tipos = SerieDocumento::tiposDisponibles();

        return view('series-documentos.edit', compact('serie', 'tipos'));
    }

    public function update(Request $request, SerieDocumento $series_documento)
    {
        $serie = $series_documento;

        $validated = $request->validate([
            'tipo_documento' => ['required', 'string', 'max:50'],
            'serie' => [
                'required',
                'string',
                'max:10',
                Rule::unique('series_documentos')->where('tipo_documento', $request->tipo_documento)->ignore($serie->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:150'],
            'correlativo_actual' => ['required', 'integer', 'min:0'],
            'longitud_correlativo' => ['required', 'integer', 'min:1', 'max:12'],
            'es_predeterminado' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($serie, $validated, $request) {
            $serie = SerieDocumento::whereKey($serie->id)->lockForUpdate()->firstOrFail();
            $origenes = [
                'cotizacion' => ['cotizaciones', 'correlativo'],
                'venta' => ['ventas', 'correlativo'],
                'orden_compra' => ['ordenes_compra', 'correlativo'],
                'factura' => ['comprobantes_venta', 'numero'],
                'boleta' => ['comprobantes_venta', 'numero'],
                'nota_venta' => ['comprobantes_venta', 'numero'],
                'guia_remision' => ['guias_remision', 'numero'],
            ];
            $origen = $origenes[$serie->tipo_documento] ?? null;
            $maximo = 0;
            if ($origen) {
                $query = DB::table($origen[0])->where('serie', $serie->serie);
                if (in_array($serie->tipo_documento, ['factura', 'boleta', 'nota_venta'], true)) {
                    $query->where('tipo', $serie->tipo_documento);
                }
                $maximo = (int) $query->max($origen[1]);
            }
            if ((int) $validated['correlativo_actual'] < max($maximo, (int) $serie->correlativo_actual)) {
                throw ValidationException::withMessages(['correlativo_actual' => 'El correlativo no puede retroceder.']);
            }
            if ($maximo > 0 && ($validated['tipo_documento'] !== $serie->tipo_documento || strtoupper(trim($validated['serie'])) !== $serie->serie)) {
                throw ValidationException::withMessages(['serie' => 'Una serie que ya emitió documentos no puede cambiar de tipo o identificador.']);
            }
            $esPredeterminado = $request->has('es_predeterminado') && (bool) $request->es_predeterminado;
            if ($esPredeterminado && ! $serie->es_predeterminado) {
                SerieDocumento::where('tipo_documento', $validated['tipo_documento'])->where('id', '!=', $serie->id)
                    ->update(['es_predeterminado' => false]);
            }
            $serie->update([
                'tipo_documento' => $validated['tipo_documento'],
                'serie' => strtoupper(trim($validated['serie'])),
                'descripcion' => $validated['descripcion'] ?? null,
                'correlativo_actual' => (int) $validated['correlativo_actual'],
                'longitud_correlativo' => (int) $validated['longitud_correlativo'],
                'es_predeterminado' => $esPredeterminado,
                'activo' => $request->has('activo') ? (bool) $request->activo : false,
            ]);
        });

        return redirect()->route('series-documentos.index')
            ->with('success', "Serie '{$serie->serie}' actualizada correctamente.");
    }

    public function destroy(SerieDocumento $series_documento)
    {
        $serie = $series_documento;

        if ($serie->es_predeterminado) {
            return redirect()->route('series-documentos.index')
                ->with('error', 'No se puede eliminar la serie predeterminada activa. Asigne otra serie como predeterminada primero.');
        }

        $nombre = $serie->serie;
        $serie->delete();

        return redirect()->route('series-documentos.index')
            ->with('success', "Serie '{$nombre}' eliminada correctamente.");
    }

    public function toggleActivo(SerieDocumento $series_documento)
    {
        $series_documento->update(['activo' => ! $series_documento->activo]);

        return back()->with('success', "Estado de la serie {$series_documento->serie} actualizado.");
    }
}
