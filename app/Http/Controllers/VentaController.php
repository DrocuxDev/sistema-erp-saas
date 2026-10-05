<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Cotizacion;
use App\Models\OrdenCompraCliente;
use App\Models\Producto;
use App\Models\SerieDocumento;
use App\Models\Venta;
use App\Services\DocumentoFiscalCalculator;
use App\Services\DevolucionVentaService;
use App\Services\InventarioService;
use App\Services\SaldoFinancieroService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $query = Venta::with('cliente', 'usuario')->latest('fecha');

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        $ventas = $query->paginate(15)->withQueryString();
        $clientes = Cliente::where('estado', 'activo')->orderBy('nombre')->get();

        return view('ventas.index', compact('ventas', 'clientes'));
    }

    public function create(Request $request)
    {
        $clientes = Cliente::where('estado', 'activo')->orderBy('nombre')->get();
        $productos = Producto::where('puede_venderse', true)
            ->with(['productoProveedores.proveedor'])
            ->orderBy('nombre')
            ->get();

        $cotizacion = null;
        $ordenCompra = null;
        $clienteIdPreseleccionado = old('cliente_id');
        $lineasPrecargadas = collect();

        if ($request->filled('cotizacion_id')) {
            $cotizacion = Cotizacion::with('detalles.producto', 'detalles.proveedor', 'cliente', 'resumen', 'ordenCompraCliente')
                ->findOrFail($request->cotizacion_id);

            if ($cotizacion->estado !== 'aprobada') {
                return redirect()->route('cotizaciones.show', $cotizacion)
                    ->with('error', 'Solo se puede generar una venta desde una cotización aprobada.');
            }

            $clienteIdPreseleccionado = $cotizacion->cliente_id;
            $ordenCompra = $cotizacion->ordenCompraCliente;
            $vendidas = DB::table('venta_detalle')->join('ventas', 'ventas.id', '=', 'venta_detalle.venta_id')
                ->where('ventas.cotizacion_id', $cotizacion->id)
                ->select('producto_id', DB::raw('SUM(cantidad) as cantidad'))
                ->groupBy('producto_id')->pluck('cantidad', 'producto_id');
            $lineasPrecargadas = $cotizacion->detalles->map(function ($detalle) use ($vendidas) {
                $detalle->cantidad = max(0, (float) $detalle->cantidad - (float) ($vendidas[$detalle->producto_id] ?? 0));

                return $detalle;
            })->filter(fn ($detalle) => $detalle->cantidad > 0);
        } elseif ($request->filled('orden_compra_cliente_id')) {
            $ordenCompra = OrdenCompraCliente::with('cliente')->findOrFail($request->orden_compra_cliente_id);
            $clienteIdPreseleccionado = $ordenCompra->cliente_id;
        }

        $serieDoc = SerieDocumento::where('tipo_documento', 'venta')
            ->where('activo', true)
            ->orderByDesc('es_predeterminado')
            ->first();

        $proximoCodigo = $serieDoc ? $serieDoc->proximo_codigo : 'V001-000001';

        return view('ventas.create', compact(
            'clientes', 'productos', 'cotizacion', 'ordenCompra', 'clienteIdPreseleccionado', 'lineasPrecargadas', 'proximoCodigo'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'cotizacion_id' => 'nullable|exists:cotizaciones,id',
            'orden_compra_cliente_id' => 'nullable|exists:ordenes_compra_cliente,id',
            'fecha' => 'required|date',
            'lineas' => 'required|array|min:1',
            'lineas.*.producto_id' => 'required|exists:productos,id',
            'lineas.*.cantidad' => 'required|numeric|min:0.01|max:1000000|decimal:0,2',
            'lineas.*.precio_venta_unit' => 'required|numeric|min:0|max:1000000|decimal:0,2',
            'lineas.*.precio_compra_unit' => 'required|numeric|min:0|max:1000000|decimal:0,2',
            'precio_incluye_igv' => 'nullable|boolean',
            'pct_igv' => 'nullable|numeric|min:0|max:100|decimal:0,2',
            'pct_retencion' => 'nullable|numeric|min:0|max:100|decimal:0,2',
            'motivo_excepcion_tributaria' => 'nullable|string|max:1000',
        ]);
        $venta = DB::transaction(function () use ($data) {
            $cliente = Cliente::findOrFail($data['cliente_id']);
            $cotizacion = null;
            if (! empty($data['cotizacion_id'])) {
                $cotizacion = Cotizacion::with('detalles', 'resumen')->whereKey($data['cotizacion_id'])->lockForUpdate()->firstOrFail();
                if ($cotizacion->estado !== 'aprobada' || (int) $cotizacion->cliente_id !== (int) $cliente->id) {
                    throw ValidationException::withMessages(['cotizacion_id' => 'La cotización debe estar aprobada y pertenecer al cliente.']);
                }
                $esperadas = $cotizacion->detalles->groupBy('producto_id');
                $vendidas = DB::table('venta_detalle')->join('ventas', 'ventas.id', '=', 'venta_detalle.venta_id')
                    ->where('ventas.cotizacion_id', $cotizacion->id)->select('producto_id', DB::raw('SUM(cantidad) as cantidad'))
                    ->groupBy('producto_id')->pluck('cantidad', 'producto_id');
                $solicitadas = collect($data['lineas'])->groupBy('producto_id');
                foreach ($solicitadas as $productoId => $lineas) {
                    if (! $esperadas->has($productoId)) {
                        throw ValidationException::withMessages(['lineas' => 'La venta contiene un producto ajeno a la cotización.']);
                    }
                    $cantidadOriginal = $esperadas[$productoId]->sum('cantidad');
                    $cantidadNueva = $lineas->sum('cantidad');
                    if ($cantidadNueva > $cantidadOriginal - (float) ($vendidas[$productoId] ?? 0) + 0.00001) {
                        throw ValidationException::withMessages(['lineas' => 'La cantidad supera el saldo de la cotización.']);
                    }
                    if ($lineas->count() !== 1 || $esperadas[$productoId]->count() !== 1 ||
                        abs((float) $lineas->first()['precio_venta_unit'] - (float) $esperadas[$productoId]->first()->precio_venta_unit) > 0.001) {
                        throw ValidationException::withMessages(['lineas' => 'El precio o las líneas no coinciden con la cotización.']);
                    }
                }
            }
            if ($cotizacion?->orden_compra_cliente_id) {
                if (! empty($data['orden_compra_cliente_id']) && (int) $data['orden_compra_cliente_id'] !== (int) $cotizacion->orden_compra_cliente_id) {
                    throw ValidationException::withMessages(['orden_compra_cliente_id' => 'La orden de cliente no coincide con la cotización.']);
                }
                $data['orden_compra_cliente_id'] = $cotizacion->orden_compra_cliente_id;
            }
            $orden = null;
            if (! empty($data['orden_compra_cliente_id'])) {
                $orden = OrdenCompraCliente::whereKey($data['orden_compra_cliente_id'])->lockForUpdate()->firstOrFail();
                $estadoValido = $cotizacion && (int) $orden->cotizacion_id === (int) $cotizacion->id
                    ? $orden->estado === 'procesada' : $orden->estado === 'pendiente';
                if (! $estadoValido || (int) $orden->cliente_id !== (int) $cliente->id ||
                    ($orden->cotizacion_id && (int) $orden->cotizacion_id !== (int) ($cotizacion?->id ?? 0))) {
                    throw ValidationException::withMessages(['orden_compra_cliente_id' => 'La orden de cliente no está pendiente o no coincide con el cliente y la cotización.']);
                }
            }
            $origenFiscal = $cotizacion ? [
                'precio_incluye_igv' => (bool) $cotizacion->precio_incluye_igv,
                'pct_igv' => $cotizacion->resumen->pct_igv,
                'pct_retencion' => $cotizacion->resumen->pct_retencion,
            ] : null;
            $perfil = DocumentoFiscalCalculator::perfil($cliente, $data, $origenFiscal);
            $importes = DocumentoFiscalCalculator::calcular($data['lineas'], $perfil);
            $correlativoData = SerieDocumento::siguienteCodigo('venta');

            $venta = Venta::create([
                'cotizacion_id' => $data['cotizacion_id'] ?? null,
                'cliente_id' => $data['cliente_id'],
                'usuario_id' => auth()->id(),
                'orden_compra_cliente_id' => $data['orden_compra_cliente_id'] ?? null,
                'serie' => $correlativoData['serie'],
                'correlativo' => $correlativoData['correlativo'],
                'codigo' => $correlativoData['codigo'],
                'fecha' => $data['fecha'],
                'subtotal' => $importes['subtotal'],
                'igv' => $importes['igv'],
                'total' => $importes['total'],
                'retencion' => $importes['retencion'],
                ...$perfil,
            ]);

            foreach ($data['lineas'] as $linea) {
                $producto = Producto::findOrFail($linea['producto_id']);
                if (! $producto->puede_venderse) {
                    throw ValidationException::withMessages(['lineas' => "El producto {$producto->nombre} no está habilitado para venta."]);
                }
                DB::table('venta_detalle')->insert([
                    'venta_id' => $venta->id,
                    'producto_id' => $linea['producto_id'],
                    'cantidad' => $linea['cantidad'],
                    'precio_venta_unit' => $linea['precio_venta_unit'],
                    'precio_compra_unit' => $linea['precio_compra_unit'],
                ]);

                app(InventarioService::class)->registrar($linea['producto_id'], 'salida', (string) $linea['cantidad'], 'venta', $venta->id, $data['fecha']);
            }

            // Si nace de una cotización, marcarla como facturada
            if ($cotizacion) {
                $cantidadTotal = $cotizacion->detalles->sum('cantidad');
                $cantidadVendida = DB::table('venta_detalle')->join('ventas', 'ventas.id', '=', 'venta_detalle.venta_id')
                    ->where('ventas.cotizacion_id', $cotizacion->id)->sum('venta_detalle.cantidad');
                if ($cantidadVendida >= $cantidadTotal - 0.00001) {
                    $cotizacion->update(['estado' => 'facturada']);
                }
            }

            // Si nace de una orden de compra del cliente, actualizar su estado y FK
            if ($orden) {
                $orden->update(['estado' => 'procesada', 'venta_id' => $orden->venta_id ?: $venta->id]);
            }

            return $venta;
        });

        return redirect()->route('ventas.show', $venta)
            ->with('success', "Venta {$venta->codigo_formateado} registrada correctamente.");
    }

    public function show(Venta $venta, DevolucionVentaService $devolucionService, SaldoFinancieroService $saldoService)
    {
        $venta->load('cliente', 'usuario', 'cotizacion', 'ordenCompraCliente', 'detalles', 'devoluciones.usuario', 'devoluciones.detalles.ventaDetalle.producto', 'cobros.usuario', 'cobros.anulador');
        $saldosDevolucion = $devolucionService->saldos($venta);
        $saldoFinanciero = $saldoService->venta($venta);

        $detalle = DB::table('venta_detalle')
            ->join('productos', 'productos.id', '=', 'venta_detalle.producto_id')
            ->where('venta_id', $venta->id)
            ->select('venta_detalle.*', 'productos.nombre as producto_nombre', 'productos.sku as producto_sku', 'productos.unidad_medida')
            ->get();

        $configuracion = Configuracion::where('activo', true)->first();

        return view('ventas.show', compact('venta', 'detalle', 'configuracion', 'saldosDevolucion', 'saldoFinanciero'));
    }

    public function pdf(Venta $venta)
    {
        $venta->load('cliente', 'usuario', 'cotizacion', 'ordenCompraCliente');

        $detalle = DB::table('venta_detalle')
            ->join('productos', 'productos.id', '=', 'venta_detalle.producto_id')
            ->where('venta_id', $venta->id)
            ->select('venta_detalle.*', 'productos.nombre as producto_nombre', 'productos.sku as producto_sku', 'productos.unidad_medida')
            ->get();

        $configuracion = Configuracion::where('activo', true)->first();

        $pdf = Pdf::loadView('ventas.pdf', compact('venta', 'detalle', 'configuracion'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        return $pdf->stream("Venta_{$venta->codigo_formateado}.pdf");
    }

    public function descargarPdf(Venta $venta)
    {
        $venta->load('cliente', 'usuario', 'cotizacion', 'ordenCompraCliente');

        $detalle = DB::table('venta_detalle')
            ->join('productos', 'productos.id', '=', 'venta_detalle.producto_id')
            ->where('venta_id', $venta->id)
            ->select('venta_detalle.*', 'productos.nombre as producto_nombre', 'productos.sku as producto_sku', 'productos.unidad_medida')
            ->get();

        $configuracion = Configuracion::where('activo', true)->first();

        $pdf = Pdf::loadView('ventas.pdf', compact('venta', 'detalle', 'configuracion'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        return $pdf->download("Venta_{$venta->codigo_formateado}.pdf");
    }
}
