<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\DocumentoFiscalCalculator;
use App\Services\DevolucionCompraService;
use App\Services\InventarioService;
use App\Services\SaldoFinancieroService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        $query = Compra::with('proveedor', 'ordenCompra', 'usuario')->latest('fecha');

        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', $request->proveedor_id);
        }

        $compras = $query->paginate(15)->withQueryString();
        $proveedores = Proveedor::where('estado', 'activo')->orderBy('nombre')->get();

        return view('compras.index', compact('compras', 'proveedores'));
    }

    public function create(Request $request)
    {
        $proveedores = Proveedor::where('estado', 'activo')->orderBy('nombre')->get();
        $productos = Producto::where('puede_comprarse', true)->orderBy('nombre')->get();

        $ordenCompra = null;
        $lineasPrecargadas = collect();
        $proveedorPreseleccionado = old('proveedor_id');

        if ($request->filled('orden_compra_id')) {
            $ordenCompra = OrdenCompra::with('proveedor')->findOrFail($request->orden_compra_id);

            if ($ordenCompra->estado !== 'aprobada') {
                return redirect()->route('ordenes-compra.show', $ordenCompra)
                    ->with('error', 'Solo se puede recepcionar una orden de compra aprobada.');
            }

            $proveedorPreseleccionado = $ordenCompra->proveedor_id;
            $recibidas = DB::table('compra_detalle')->join('compras', 'compras.id', '=', 'compra_detalle.compra_id')
                ->where('compras.orden_compra_id', $ordenCompra->id)->select('producto_id', DB::raw('SUM(cantidad) as cantidad'))
                ->groupBy('producto_id')->pluck('cantidad', 'producto_id');
            $lineasPrecargadas = DB::table('orden_compra_detalle')
                ->join('productos', 'productos.id', '=', 'orden_compra_detalle.producto_id')
                ->where('orden_compra_id', $ordenCompra->id)
                ->select('orden_compra_detalle.producto_id', 'orden_compra_detalle.cantidad', 'orden_compra_detalle.precio_compra_unit', 'productos.nombre as producto_nombre')
                ->get()->map(function ($linea) use ($recibidas) {
                    $linea->cantidad = max(0, (float) $linea->cantidad - (float) ($recibidas[$linea->producto_id] ?? 0));

                    return $linea;
                })->filter(fn ($linea) => $linea->cantidad > 0);
        }

        $ordenesAprobadas = OrdenCompra::with('proveedor')
            ->where('estado', 'aprobada')
            ->latest('fecha')
            ->take(100)
            ->get();

        return view('compras.create', compact('proveedores', 'productos', 'ordenCompra', 'ordenesAprobadas', 'lineasPrecargadas', 'proveedorPreseleccionado'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'orden_compra_id' => 'nullable|exists:ordenes_compra,id',
            'fecha' => 'required|date',
            'lineas' => 'required|array|min:1',
            'lineas.*.producto_id' => 'required|exists:productos,id',
            'lineas.*.cantidad' => 'required|numeric|min:0.01|max:1000000|decimal:0,2',
            'lineas.*.precio_compra_unit' => 'required|numeric|min:0|max:1000000|decimal:0,2',
        ]);

        foreach ($data['lineas'] as $linea) {
            $producto = Producto::findOrFail($linea['producto_id']);
            if (! $producto->puede_comprarse) {
                throw ValidationException::withMessages([
                    'lineas' => "El producto \"{$producto->nombre}\" no está habilitado para compra.",
                ]);
            }
        }

        $total = DocumentoFiscalCalculator::calcular($data['lineas'], [
            'precio_incluye_igv' => false, 'pct_igv' => 0, 'pct_retencion' => 0,
        ], 'precio_compra_unit')['total'];

        $compra = DB::transaction(function () use ($data, $total) {
            $oc = null;
            if (! empty($data['orden_compra_id'])) {
                $oc = OrdenCompra::with('detalles')->whereKey($data['orden_compra_id'])->lockForUpdate()->firstOrFail();
                if ($oc->estado !== 'aprobada' || (int) $oc->proveedor_id !== (int) $data['proveedor_id']) {
                    throw ValidationException::withMessages(['orden_compra_id' => 'La orden debe estar aprobada y pertenecer al proveedor.']);
                }
                $ordenadas = $oc->detalles->groupBy('producto_id');
                $recibidas = DB::table('compra_detalle')->join('compras', 'compras.id', '=', 'compra_detalle.compra_id')
                    ->where('compras.orden_compra_id', $oc->id)->select('producto_id', DB::raw('SUM(cantidad) as cantidad'))
                    ->groupBy('producto_id')->pluck('cantidad', 'producto_id');
                foreach (collect($data['lineas'])->groupBy('producto_id') as $productoId => $lineas) {
                    if (! $ordenadas->has($productoId) || $ordenadas[$productoId]->count() !== 1 || $lineas->count() !== 1) {
                        throw ValidationException::withMessages(['lineas' => 'Las líneas deben corresponder a los productos de la orden.']);
                    }
                    $ordenada = $ordenadas[$productoId]->first();
                    if ($lineas->sum('cantidad') > (float) $ordenada->cantidad - (float) ($recibidas[$productoId] ?? 0) + 0.00001 ||
                        abs((float) $lineas->first()['precio_compra_unit'] - (float) $ordenada->precio_compra_unit) > 0.001) {
                        throw ValidationException::withMessages(['lineas' => 'La cantidad o el precio supera lo pendiente en la orden.']);
                    }
                }
            }
            $compra = Compra::create([
                'proveedor_id' => $data['proveedor_id'],
                'orden_compra_id' => $data['orden_compra_id'] ?? null,
                'usuario_id' => auth()->id(),
                'fecha' => $data['fecha'],
                'total' => $total,
            ]);

            foreach ($data['lineas'] as $linea) {
                DB::table('compra_detalle')->insert([
                    'compra_id' => $compra->id,
                    'producto_id' => $linea['producto_id'],
                    'presentacion_compra_id' => null,
                    'cantidad_presentacion' => null,
                    'cantidad' => $linea['cantidad'],
                    'precio_compra_unit' => $linea['precio_compra_unit'],
                ]);

                app(InventarioService::class)->registrar($linea['producto_id'], 'entrada', (string) $linea['cantidad'], 'compra', $compra->id, $data['fecha']);
            }

            if ($oc) {
                $cantidadTotal = $oc->detalles->sum('cantidad');
                $cantidadRecibida = DB::table('compra_detalle')->join('compras', 'compras.id', '=', 'compra_detalle.compra_id')
                    ->where('compras.orden_compra_id', $oc->id)->sum('compra_detalle.cantidad');
                if ($cantidadRecibida >= $cantidadTotal - 0.00001) {
                    $oc->update(['estado' => 'recibida']);
                }
            }

            return $compra;
        });

        return redirect()->route('compras.show', $compra)
            ->with('success', "Compra #{$compra->id} recepcionada correctamente. Existencias incrementadas.");
    }

    public function show(Compra $compra, DevolucionCompraService $devolucionService, SaldoFinancieroService $saldoService)
    {
        $compra->load('proveedor', 'ordenCompra', 'usuario', 'detalles', 'devoluciones.usuario', 'devoluciones.detalles.compraDetalle.producto', 'pagos.usuario', 'pagos.anulador');
        $saldosDevolucion = $devolucionService->saldos($compra);
        $saldoFinanciero = $saldoService->compra($compra);

        $detalles = DB::table('compra_detalle')
            ->join('productos', 'productos.id', '=', 'compra_detalle.producto_id')
            ->where('compra_id', $compra->id)
            ->select('compra_detalle.*', 'productos.nombre as producto_nombre', 'productos.sku as producto_sku', 'productos.unidad_medida')
            ->get();

        return view('compras.show', compact('compra', 'detalles', 'saldosDevolucion', 'saldoFinanciero'));
    }
}
