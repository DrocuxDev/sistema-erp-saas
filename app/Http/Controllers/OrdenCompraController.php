<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\SerieDocumento;
use App\Services\DocumentoFiscalCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrdenCompraController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdenCompra::with('proveedor', 'usuario')->latest('fecha');

        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', $request->proveedor_id);
        }

        if ($request->filled('estado') && $request->estado !== 'TODOS') {
            $query->where('estado', $request->estado);
        }

        $ordenes = $query->paginate(15)->withQueryString();
        $proveedores = Proveedor::where('estado', 'activo')->orderBy('nombre')->get();

        return view('ordenes-compra.index', compact('ordenes', 'proveedores'));
    }

    public function create()
    {
        $proveedores = Proveedor::where('estado', 'activo')->orderBy('nombre')->get();
        $productos = Producto::where('puede_comprarse', true)
            ->with(['productoProveedores.proveedor'])
            ->orderBy('nombre')
            ->get();

        $serieDoc = SerieDocumento::where('tipo_documento', 'orden_compra')
            ->where('activo', true)
            ->orderByDesc('es_predeterminado')
            ->first();

        $proximoCodigo = $serieDoc ? $serieDoc->proximo_codigo : 'OC01-000001';

        return view('ordenes-compra.create', compact('proveedores', 'productos', 'proximoCodigo'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha' => 'required|date',
            'forma_pago' => 'nullable|string|max:150',
            'plazo_entrega' => 'nullable|string|max:150',
            'lugar_entrega' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:255',
            'lineas' => 'required|array|min:1',
            'lineas.*.producto_id' => 'required|exists:productos,id',
            'lineas.*.cantidad' => 'required|numeric|min:0.01|max:1000000|decimal:0,2',
            'lineas.*.precio_compra' => 'nullable|numeric|min:0|max:1000000|decimal:0,2',
            'lineas.*.precio_compra_unit' => 'nullable|numeric|min:0|max:1000000|decimal:0,2',
        ]);

        foreach ($data['lineas'] as $l) {
            $prod = Producto::findOrFail($l['producto_id']);
            if (! $prod->puede_comprarse) {
                throw ValidationException::withMessages([
                    'lineas' => "El producto \"{$prod->nombre}\" no está habilitado para compras.",
                ]);
            }
        }

        $lineasFiscales = collect($data['lineas'])->map(fn ($linea) => [
            'cantidad' => $linea['cantidad'],
            'precio_compra_unit' => $linea['precio_compra_unit'] ?? $linea['precio_compra'] ?? 0,
        ])->all();
        $configuracion = Configuracion::where('activo', true)->first();
        $importes = DocumentoFiscalCalculator::calcular($lineasFiscales, [
            'precio_incluye_igv' => false,
            'pct_igv' => $configuracion?->pct_igv ?? 18,
            'pct_retencion' => 0,
        ], 'precio_compra_unit');
        $subtotal = $importes['subtotal'];
        $igv = $importes['igv'];
        $total = $importes['total'];

        $ordenCompra = DB::transaction(function () use ($data, $subtotal, $igv, $total) {
            $correlativoData = SerieDocumento::siguienteCodigo('orden_compra');

            $orden = OrdenCompra::create([
                'proveedor_id' => $data['proveedor_id'],
                'usuario_id' => auth()->id(),
                'serie' => $correlativoData['serie'],
                'correlativo' => $correlativoData['correlativo'],
                'codigo' => $correlativoData['codigo'],
                'fecha' => $data['fecha'],
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $total,
                'estado' => 'pendiente',
                'observaciones' => $data['observaciones'] ?? null,
                'forma_pago' => $data['forma_pago'] ?? '30 días crédito / Factura',
                'plazo_entrega' => $data['plazo_entrega'] ?? '3 a 5 días hábiles',
                'lugar_entrega' => $data['lugar_entrega'] ?? 'Almacén Central JHARDSYSTEX',
            ]);

            foreach ($data['lineas'] as $l) {
                $precio = $l['precio_compra_unit'] ?? $l['precio_compra'] ?? 0;
                DB::table('orden_compra_detalle')->insert([
                    'orden_compra_id' => $orden->id,
                    'producto_id' => $l['producto_id'],
                    'presentacion_compra_id' => null,
                    'cantidad_presentacion' => null,
                    'cantidad' => $l['cantidad'],
                    'precio_compra_unit' => $precio,
                ]);
            }

            return $orden;
        });

        return redirect()->route('ordenes-compra.show', $ordenCompra)
            ->with('success', "Orden de Compra {$ordenCompra->codigo_formateado} registrada exitosamente.");
    }

    public function show(OrdenCompra $orden_compra)
    {
        $orden_compra->load('proveedor', 'usuario');

        $detalles = DB::table('orden_compra_detalle')
            ->join('productos', 'productos.id', '=', 'orden_compra_detalle.producto_id')
            ->where('orden_compra_id', $orden_compra->id)
            ->select('orden_compra_detalle.*', 'productos.nombre as producto_nombre', 'productos.sku as producto_sku', 'productos.unidad_medida')
            ->get();

        $configuracion = Configuracion::where('activo', true)->first();

        $cantidadRecibida = DB::table('compra_detalle')->join('compras', 'compras.id', '=', 'compra_detalle.compra_id')
            ->where('compras.orden_compra_id', $orden_compra->id)->sum('compra_detalle.cantidad');
        $cantidadOrdenada = $detalles->sum('cantidad');

        return view('ordenes-compra.show', compact('orden_compra', 'detalles', 'configuracion', 'cantidadRecibida', 'cantidadOrdenada'));
    }

    public function aprobar(OrdenCompra $orden_compra)
    {
        if ($orden_compra->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden aprobar órdenes en estado pendiente.');
        }

        $orden_compra->update(['estado' => 'aprobada']);

        return back()->with('success', "Orden de Compra {$orden_compra->codigo_formateado} aprobada correctamente.");
    }

    public function anular(OrdenCompra $orden_compra)
    {
        $anulada = DB::transaction(function () use ($orden_compra) {
            $orden = OrdenCompra::whereKey($orden_compra->id)->lockForUpdate()->firstOrFail();
            if (! in_array($orden->estado, ['pendiente', 'aprobada'], true) || $orden->compras()->exists()) {
                return false;
            }
            $orden->update(['estado' => 'anulada']);

            return true;
        });
        if (! $anulada) {
            return back()->with('error', 'No se puede anular una orden con recepciones, incluso si son parciales.');
        }

        return back()->with('success', "Orden de Compra {$orden_compra->codigo_formateado} anulada correctamente.");
    }

    public function pdf(OrdenCompra $orden_compra)
    {
        $orden_compra->load('proveedor', 'usuario');
        $detalles = DB::table('orden_compra_detalle')
            ->join('productos', 'productos.id', '=', 'orden_compra_detalle.producto_id')
            ->where('orden_compra_id', $orden_compra->id)
            ->select('orden_compra_detalle.*', 'productos.nombre as producto_nombre', 'productos.sku as producto_sku', 'productos.unidad_medida')
            ->get();

        $configuracion = Configuracion::where('activo', true)->first();

        $pdf = Pdf::loadView('ordenes-compra.pdf', compact('orden_compra', 'detalles', 'configuracion'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        return $pdf->stream("Orden_Compra_{$orden_compra->codigo_formateado}.pdf");
    }

    public function descargarPdf(OrdenCompra $orden_compra)
    {
        $orden_compra->load('proveedor', 'usuario');
        $detalles = DB::table('orden_compra_detalle')
            ->join('productos', 'productos.id', '=', 'orden_compra_detalle.producto_id')
            ->where('orden_compra_id', $orden_compra->id)
            ->select('orden_compra_detalle.*', 'productos.nombre as producto_nombre', 'productos.sku as producto_sku', 'productos.unidad_medida')
            ->get();

        $configuracion = Configuracion::where('activo', true)->first();

        $pdf = Pdf::loadView('ordenes-compra.pdf', compact('orden_compra', 'detalles', 'configuracion'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        return $pdf->download("Orden_Compra_{$orden_compra->codigo_formateado}.pdf");
    }
}
