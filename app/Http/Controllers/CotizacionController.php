<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Cotizacion;
use App\Models\CotizacionDetalle;
use App\Models\CotizacionResumen;
use App\Models\OrdenCompraCliente;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\SerieDocumento;
use App\Services\CotizacionDocumento;
use App\Services\CotizacionResumenCalculator;
use App\Services\DocumentoFiscalCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CotizacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Cotizacion::with('cliente', 'usuario', 'resumen')
            ->latest('created_at');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('tipo_cotizacion')) {
            $query->where('tipo_cotizacion', $request->tipo_cotizacion);
        }

        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);
            $query->where(function ($q) use ($buscar) {
                $q->where('codigo', 'like', "%{$buscar}%")
                    ->orWhere('asunto', 'like', "%{$buscar}%")
                    ->orWhereHas('cliente', function ($qc) use ($buscar) {
                        $qc->where('nombre', 'like', "%{$buscar}%")
                            ->orWhere('ruc', 'like', "%{$buscar}%");
                    });
            });
        }

        $cotizaciones = $query->paginate(15)->withQueryString();
        $clientes = Cliente::where('estado', 'activo')->orderBy('nombre')->get();

        return view('cotizaciones.index', compact('cotizaciones', 'clientes'));
    }

    public function create(Request $request)
    {
        $proveedores = Proveedor::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']);
        $clientes = Cliente::where('estado', 'activo')->orderBy('nombre')->get();
        $productos = Producto::where('puede_venderse', true)
            ->with(['productoProveedores.proveedor'])
            ->orderBy('nombre')
            ->limit(500)
            ->get();

        $ordenCompra = null;
        $clienteIdPreseleccionado = old('cliente_id');
        $clienteSeleccionado = null;

        if ($request->filled('orden_compra_cliente_id')) {
            $ordenCompra = OrdenCompraCliente::with('cliente')->findOrFail($request->orden_compra_cliente_id);
            $clienteIdPreseleccionado = $ordenCompra->cliente_id;
            $clienteSeleccionado = $ordenCompra->cliente;
        }

        $serieCotizacion = SerieDocumento::where('tipo_documento', 'cotizacion')->where('activo', true)
            ->orderByDesc('es_predeterminado')->first();
        $proximoCodigo = $serieCotizacion?->proximo_codigo ?? 'C001-000001';

        // Tasas predeterminadas desde configuración global
        $configuracion = Configuracion::where('activo', true)->first();
        $tasasDefault = [
            'pct_igv' => $clienteSeleccionado?->pct_igv ?? $configuracion->pct_igv ?? 18,
            'pct_retencion' => $clienteSeleccionado?->pct_retencion ?? $configuracion->pct_retencion ?? 0,
            'pct_comision_venta' => $configuracion->pct_comision_venta ?? 5,
            'pct_comision_ganancia' => $configuracion->pct_comision_ganancia ?? 10,
        ];
        $precioIncluyeIgvDefault = $clienteSeleccionado?->precio_incluye_igv ?? false;

        $condicionesDefault = $configuracion->condiciones_adicionales_default ?? '';

        return view('cotizaciones.create', compact(
            'clientes', 'proveedores',
            'productos',
            'ordenCompra',
            'clienteIdPreseleccionado',
            'clienteSeleccionado',
            'proximoCodigo',
            'tasasDefault', 'precioIncluyeIgvDefault', 'condicionesDefault'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validarCotizacion($request);
        $configuracion = Configuracion::where('activo', true)->firstOrFail();
        $lineas = collect($data['lineas']);

        $cliente = Cliente::findOrFail($data['cliente_id']);
        $perfil = DocumentoFiscalCalculator::perfil($cliente, $data);
        $tasas = [
            ...$perfil,
            'pct_comision_venta' => $data['pct_comision_venta'] ?? $configuracion->pct_comision_venta,
            'pct_comision_ganancia' => $data['pct_comision_ganancia'] ?? $configuracion->pct_comision_ganancia,
        ];

        $cotizacion = DB::transaction(function () use ($data, $configuracion, $lineas, $tasas, $perfil, $cliente) {
            $ordenCliente = null;
            if (! empty($data['orden_compra_cliente_id'])) {
                $ordenCliente = OrdenCompraCliente::whereKey($data['orden_compra_cliente_id'])->lockForUpdate()->firstOrFail();
                if ($ordenCliente->estado !== 'pendiente' || (int) $ordenCliente->cliente_id !== (int) $data['cliente_id']) {
                    throw ValidationException::withMessages(['orden_compra_cliente_id' => 'La orden de cliente debe estar pendiente y pertenecer al cliente.']);
                }
            }
            $codigoData = SerieDocumento::siguienteCodigo('cotizacion');
            $serie = $codigoData['serie'];
            $correlativo = $codigoData['correlativo'];
            $codigo = $codigoData['codigo'];

            $cotizacion = Cotizacion::create([
                'cliente_id' => $data['cliente_id'],
                'usuario_id' => auth()->id(),
                'orden_compra_cliente_id' => $data['orden_compra_cliente_id'] ?? null,
                'serie' => $serie,
                'correlativo' => $correlativo,
                'codigo' => $codigo,
                'version' => 1,
                'tipo_cotizacion' => $data['tipo_cotizacion'] ?? 'producto',
                'asunto' => $data['asunto'] ?? 'COTIZACIÓN DE EQUIPOS Y SERVICIOS TI',
                'representante' => $data['representante'] ?? null,
                'fecha' => $data['fecha'],
                'estado' => 'pendiente',
                'observaciones' => $data['observaciones'] ?? null,
                'plazo_entrega' => $data['plazo_entrega'] ?? '2 a 5 días hábiles tras recepción de O/C',
                'forma_pago' => $data['forma_pago'] ?? $cliente->condicion_pago ?? '30 días calendario previa conformidad y factura',
                'logistica' => $data['logistica'] ?? 'Entrega en instalaciones del cliente / Lima Metropolitana',
                'validez_oferta' => $data['validez_oferta'] ?? '15 días calendario',
                'precio_incluye_igv' => $perfil['precio_incluye_igv'],
                'motivo_excepcion_tributaria' => $perfil['motivo_excepcion_tributaria'],
                'condiciones_adicionales' => $data['condiciones_adicionales'] ?? null,
                // Copia versionada de la configuración de documentos: reimprimir
                // no cambia aunque la configuración cambie después.
                'documento_snapshot' => CotizacionDocumento::snapshot($configuracion),
            ]);

            $this->guardarDetalleYResumen($cotizacion, $lineas, $configuracion, $tasas, $data['no_utilizable'] ?? '0');

            if ($ordenCliente) {
                $ordenCliente->update([
                    'estado' => 'procesada',
                    'cotizacion_id' => $cotizacion->id,
                ]);
            }

            return $cotizacion;
        });

        return redirect()->route('cotizaciones.show', $cotizacion)
            ->with('success', 'Cotización '.$cotizacion->codigo_formateado.' (Versión '.$cotizacion->version_formateada.') registrada correctamente.');
    }

    public function show(Cotizacion $cotizacion)
    {
        $cotizacion->load([
            'cliente',
            'usuario',
            'ordenCompraCliente',
            'detalles.producto',
            'detalles.proveedor',
            'resumen.configuracion',
        ]);

        $cantidadVendida = DB::table('venta_detalle')->join('ventas', 'ventas.id', '=', 'venta_detalle.venta_id')
            ->where('ventas.cotizacion_id', $cotizacion->id)->sum('venta_detalle.cantidad');
        $cantidadCotizada = $cotizacion->detalles->sum('cantidad');

        return view('cotizaciones.show', compact('cotizacion', 'cantidadVendida', 'cantidadCotizada'));
    }

    public function edit(Cotizacion $cotizacion)
    {
        if ($cotizacion->estado !== 'pendiente') {
            return redirect()->route('cotizaciones.show', $cotizacion)
                ->with('error', 'Solo se pueden editar cotizaciones en estado pendiente.');
        }

        $cotizacion->load('detalles.producto', 'detalles.proveedor', 'resumen', 'cliente');
        $proveedores = Proveedor::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']);
        $clientes = Cliente::where('estado', 'activo')->orderBy('nombre')->get();
        $productos = Producto::where('puede_venderse', true)
            ->with(['productoProveedores.proveedor'])
            ->orderBy('nombre')
            ->limit(500)
            ->get();

        // Tasas: usar las del resumen existente, o las de configuración global como fallback
        $configuracion = Configuracion::where('activo', true)->first();
        $tasasDefault = [
            'pct_igv' => $cotizacion->resumen->pct_igv ?? $configuracion->pct_igv ?? 18,
            'pct_retencion' => $cotizacion->resumen->pct_retencion ?? $configuracion->pct_retencion ?? 3,
            'pct_comision_venta' => $cotizacion->resumen->pct_comision_venta ?? $configuracion->pct_comision_venta ?? 5,
            'pct_comision_ganancia' => $cotizacion->resumen->pct_comision_ganancia ?? $configuracion->pct_comision_ganancia ?? 10,
        ];
        $precioIncluyeIgvDefault = (bool) $cotizacion->precio_incluye_igv;

        return view('cotizaciones.edit', compact('cotizacion', 'clientes', 'productos', 'proveedores', 'tasasDefault', 'precioIncluyeIgvDefault'));
    }

    public function update(Request $request, Cotizacion $cotizacion)
    {
        if ($cotizacion->estado !== 'pendiente') {
            return redirect()->route('cotizaciones.show', $cotizacion)
                ->with('error', 'Solo se pueden editar cotizaciones en estado pendiente.');
        }

        $data = $this->validarCotizacion($request);
        if ((int) ($data['orden_compra_cliente_id'] ?? 0) !== (int) ($cotizacion->orden_compra_cliente_id ?? 0) ||
            ($cotizacion->orden_compra_cliente_id && (int) $data['cliente_id'] !== (int) $cotizacion->ordenCompraCliente->cliente_id)) {
            throw ValidationException::withMessages(['orden_compra_cliente_id' => 'La cotización no puede cambiar la orden de cliente de origen.']);
        }
        $configuracion = Configuracion::where('activo', true)->firstOrFail();
        $lineas = collect($data['lineas']);

        $cliente = Cliente::findOrFail($data['cliente_id']);
        $perfil = DocumentoFiscalCalculator::perfil($cliente, $data);
        $tasas = [
            ...$perfil,
            'pct_comision_venta' => $data['pct_comision_venta'] ?? $configuracion->pct_comision_venta,
            'pct_comision_ganancia' => $data['pct_comision_ganancia'] ?? $configuracion->pct_comision_ganancia,
        ];

        DB::transaction(function () use ($cotizacion, $data, $configuracion, $lineas, $tasas, $perfil) {
            $nuevaVersion = ($cotizacion->version ?: 1) + 1;

            $cotizacion->update([
                'cliente_id' => $data['cliente_id'],
                'version' => $nuevaVersion,
                'tipo_cotizacion' => $data['tipo_cotizacion'] ?? 'producto',
                'asunto' => $data['asunto'] ?? $cotizacion->asunto,
                'representante' => $data['representante'] ?? $cotizacion->representante,
                'fecha' => $data['fecha'],
                'observaciones' => $data['observaciones'] ?? null,
                'plazo_entrega' => $data['plazo_entrega'] ?? $cotizacion->plazo_entrega,
                'forma_pago' => $data['forma_pago'] ?? $cotizacion->forma_pago,
                'logistica' => $data['logistica'] ?? $cotizacion->logistica,
                'validez_oferta' => $data['validez_oferta'] ?? $cotizacion->validez_oferta,
                'precio_incluye_igv' => $perfil['precio_incluye_igv'],
                'motivo_excepcion_tributaria' => $perfil['motivo_excepcion_tributaria'],
                'condiciones_adicionales' => $data['condiciones_adicionales'] ?? $cotizacion->condiciones_adicionales,
                // Nueva versión = nueva emisión: se refresca la copia de configuración.
                'documento_snapshot' => CotizacionDocumento::snapshot($configuracion),
            ]);

            $cotizacion->detalles()->delete();
            $cotizacion->resumen()?->delete();

            $this->guardarDetalleYResumen($cotizacion, $lineas, $configuracion, $tasas, $data['no_utilizable'] ?? '0');
        });

        return redirect()->route('cotizaciones.show', $cotizacion)
            ->with('success', 'Cotización actualizada a Versión '.$cotizacion->fresh()->version_formateada.' correctamente.');
    }

    public function destroy(Cotizacion $cotizacion)
    {
        if (! in_array($cotizacion->estado, ['pendiente', 'rechazada'], true)) {
            return redirect()->route('cotizaciones.show', $cotizacion)
                ->with('error', 'No se puede eliminar una cotización aprobada o facturada.');
        }

        $cotizacion->delete();

        return redirect()->route('cotizaciones.index')
            ->with('success', 'Cotización eliminada.');
    }

    public function aprobar(Cotizacion $cotizacion)
    {
        if ($cotizacion->estado !== 'pendiente') {
            return redirect()->route('cotizaciones.show', $cotizacion)
                ->with('error', 'Solo se pueden aprobar cotizaciones pendientes.');
        }

        $cotizacion->update(['estado' => 'aprobada']);

        return redirect()->route('cotizaciones.show', $cotizacion)
            ->with('success', 'Cotización aprobada. Ya puede generarse la venta.');
    }

    public function pdf(Request $request, Cotizacion $cotizacion)
    {
        $cotizacion->load([
            'cliente',
            'usuario',
            'detalles.producto',
            'detalles.proveedor',
            'resumen.configuracion',
        ]);

        $datos = CotizacionDocumento::datosPdf($cotizacion);
        $nombreArchivo = "cotizacion-{$datos['codigoCompleto']}.pdf";

        if ($request->has('html')) {
            return response()->view('cotizaciones.pdf', $datos);
        }

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('cotizaciones.pdf', $datos)
                ->setPaper('a4', 'portrait')
                ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);

            return $pdf->stream($nombreArchivo);
        }

        return response()->view('cotizaciones.pdf', $datos);
    }

    public function pdfDescargar(Cotizacion $cotizacion)
    {
        $cotizacion->load([
            'cliente',
            'usuario',
            'detalles.producto',
            'detalles.proveedor',
            'resumen.configuracion',
        ]);

        $nombreArchivo = sprintf(
            'cotizacion-%s-v%s.pdf',
            $cotizacion->codigo_formateado,
            $cotizacion->version_formateada
        );

        $datos = CotizacionDocumento::datosPdf($cotizacion);
        $html = view('cotizaciones.pdf', $datos)->render();

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadHTML($html)
                ->setPaper('a4', 'portrait')
                ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);

            return $pdf->download($nombreArchivo);
        }

        return response($html);
    }

    private function renderPdfHtml(Cotizacion $cotizacion): string
    {
        return view('cotizaciones.pdf', CotizacionDocumento::datosPdf($cotizacion))->render();
    }

    private function validarCotizacion(Request $request): array
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'orden_compra_cliente_id' => 'nullable|exists:ordenes_compra_cliente,id',
            'tipo_cotizacion' => 'required|in:producto,servicio',
            'asunto' => 'nullable|string|max:255',
            'representante' => 'nullable|string|max:150',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string|max:255',
            'plazo_entrega' => 'nullable|string|max:150',
            'forma_pago' => 'nullable|string|max:150',
            'logistica' => 'nullable|string|max:255',
            'validez_oferta' => 'nullable|string|max:100',
            'condiciones_adicionales' => 'nullable|string|max:2000',
            'no_utilizable' => 'nullable|numeric|min:0|max:1000000|decimal:0,2',
            'pct_igv' => 'nullable|numeric|min:0|max:100|decimal:0,2',
            'pct_retencion' => 'nullable|numeric|min:0|max:100|decimal:0,2',
            'precio_incluye_igv' => 'nullable|boolean',
            'motivo_excepcion_tributaria' => 'nullable|string|max:1000',
            'pct_comision_venta' => 'nullable|numeric|min:0|max:100|decimal:0,2',
            'pct_comision_ganancia' => 'nullable|numeric|min:0|max:100|decimal:0,2',
            'lineas' => 'required|array|min:1',
            'lineas.*.producto_id' => 'required|integer|distinct|exists:productos,id',
            'lineas.*.proveedor_id' => 'required|exists:proveedores,id',
            'lineas.*.descripcion' => 'nullable|string|max:255',
            'lineas.*.unidad_medida' => 'nullable|string|max:20',
            'lineas.*.cantidad' => 'required|numeric|min:0.01|max:1000000|decimal:0,2',
            'lineas.*.precio_venta_unit' => 'required|numeric|min:0|max:1000000|decimal:0,2',
            'lineas.*.precio_compra_unit' => 'required|numeric|min:0|max:1000000|decimal:0,2',
            'lineas.*.igv_compra' => 'nullable|numeric|min:0|max:1000000|decimal:0,2',
        ], [
            'lineas.*.producto_id.distinct' => 'Producto duplicado en otra fila.',
        ]);

        $lineas = collect($data['lineas'])->map(fn (array $linea) => [
            ...$linea,
            'igv_compra' => $linea['igv_compra'] ?? '0',
        ]);

        $configuracion = Configuracion::where('activo', true)->firstOrFail();
        $cliente = Cliente::findOrFail($data['cliente_id']);
        $perfil = DocumentoFiscalCalculator::perfil($cliente, $data);
        $tasas = [
            ...$perfil,
            'pct_comision_venta' => $data['pct_comision_venta'] ?? $configuracion->pct_comision_venta,
            'pct_comision_ganancia' => $data['pct_comision_ganancia'] ?? $configuracion->pct_comision_ganancia,
        ];
        $resumen = CotizacionResumenCalculator::calcular($lineas, $tasas, $data['no_utilizable'] ?? '0');

        if (! CotizacionResumenCalculator::validarSubtotal($lineas, $resumen['subtotal'], $tasas)) {
            throw ValidationException::withMessages([
                'lineas' => 'El subtotal calculado no coincide con la suma de las líneas.',
            ]);
        }

        $data['lineas'] = $lineas->all();

        return $data;
    }

    private function guardarDetalleYResumen(
        Cotizacion $cotizacion,
        Collection $lineas,
        Configuracion $configuracion,
        array $tasas,
        string|int|float $noUtilizable = 0
    ): void {
        foreach ($lineas as $linea) {
            $producto = Producto::findOrFail($linea['producto_id']);

            CotizacionDetalle::create([
                'cotizacion_id' => $cotizacion->id,
                'producto_id' => $linea['producto_id'],
                'proveedor_id' => $linea['proveedor_id'],
                'descripcion' => $linea['descripcion'] ?? $producto->nombre,
                'unidad_medida' => $linea['unidad_medida'] ?? $producto->unidad_medida,
                'cantidad' => $linea['cantidad'],
                'precio_venta_unit' => $linea['precio_venta_unit'],
                'precio_compra_unit' => $linea['precio_compra_unit'],
                'igv_compra' => $linea['igv_compra'] ?? 0,
            ]);
        }

        $resumen = CotizacionResumenCalculator::calcular($lineas, $tasas, $noUtilizable);

        CotizacionResumen::create([
            'cotizacion_id' => $cotizacion->id,
            'configuracion_id' => $configuracion->id,
            'pct_igv' => $resumen['pct_igv'],
            'pct_retencion' => $resumen['pct_retencion'],
            'pct_comision_venta' => $resumen['pct_comision_venta'],
            'pct_comision_ganancia' => $resumen['pct_comision_ganancia'],
            'subtotal' => $resumen['subtotal'],
            'igv' => $resumen['igv'],
            'retencion' => $resumen['retencion'],
            'compra_activa' => $resumen['compra_activa'],
            'compra_fija' => $resumen['compra_fija'],
            'no_utilizable' => $resumen['no_utilizable'],
            'comision_venta' => $resumen['comision_venta'],
            'comision_ganancia' => $resumen['comision_ganancia'],
        ]);
    }
}
