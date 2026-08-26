<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Cotizacion;
use App\Models\OrdenCompraCliente;
use App\Models\Producto;
use App\Models\SerieDocumento;
use App\Models\Venta;
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

        $ventas   = $query->paginate(15)->withQueryString();
        $clientes = Cliente::where('estado', 'activo')->orderBy('nombre')->get();

        return view('ventas.index', compact('ventas', 'clientes'));
    }

    public function create(Request $request)
    {
        $clientes  = Cliente::where('estado', 'activo')->orderBy('nombre')->get();
        $productos = Producto::where('puede_venderse', true)
            ->with(['productoProveedores.proveedor'])
            ->orderBy('nombre')
            ->get();

        $cotizacion = null;
        $ordenCompra = null;
        $clienteIdPreseleccionado = old('cliente_id');
        $lineasPrecargadas = collect();

        if ($request->filled('cotizacion_id')) {
            $cotizacion = Cotizacion::with('detalles.producto', 'detalles.proveedor', 'cliente')
                ->findOrFail($request->cotizacion_id);

            if ($cotizacion->estado !== 'aprobada') {
                return redirect()->route('cotizaciones.show', $cotizacion)
                    ->with('error', 'Solo se puede generar una venta desde una cotización aprobada.');
            }

            $clienteIdPreseleccionado = $cotizacion->cliente_id;
            $lineasPrecargadas = $cotizacion->detalles;
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
            'cliente_id'               => 'required|exists:clientes,id',
            'cotizacion_id'            => 'nullable|exists:cotizaciones,id',
            'orden_compra_cliente_id'  => 'nullable|exists:ordenes_compra_cliente,id',
            'fecha'                    => 'required|date',
            'lineas'                   => 'required|array|min:1',
            'lineas.*.producto_id'     => 'required|exists:productos,id',
            'lineas.*.cantidad'        => 'required|numeric|min:0.01',
            'lineas.*.precio_venta_unit'  => 'required|numeric|min:0',
            'lineas.*.precio_compra_unit' => 'required|numeric|min:0',
        ]);

        // Validar que todos los productos sean vendibles y tengan stock suficiente
        foreach ($data['lineas'] as $linea) {
            $producto = Producto::findOrFail($linea['producto_id']);

            if (! $producto->puede_venderse) {
                throw ValidationException::withMessages([
                    'lineas' => "El producto \"{$producto->nombre}\" no está habilitado para venta.",
                ]);
            }

            if ($producto->stock_actual < $linea['cantidad']) {
                throw ValidationException::withMessages([
                    'lineas' => "Stock insuficiente para \"{$producto->nombre}\". Disponible: {$producto->stock_actual}.",
                ]);
            }
        }

        $total = 0;
        foreach ($data['lineas'] as $linea) {
            $total += round($linea['cantidad'] * $linea['precio_venta_unit'], 2);
        }
        $total = round($total, 2);
        $subtotal = round($total / 1.18, 2);
        $igv = round($total - $subtotal, 2);

        $venta = DB::transaction(function () use ($data, $subtotal, $igv, $total) {
            $correlativoData = SerieDocumento::siguienteCodigo('venta');

            $venta = Venta::create([
                'cotizacion_id'            => $data['cotizacion_id'] ?? null,
                'cliente_id'               => $data['cliente_id'],
                'usuario_id'               => auth()->id(),
                'orden_compra_cliente_id'  => $data['orden_compra_cliente_id'] ?? null,
                'serie'                    => $correlativoData['serie'],
                'correlativo'              => $correlativoData['correlativo'],
                'codigo'                   => $correlativoData['codigo'],
                'fecha'                    => $data['fecha'],
                'subtotal'                 => $subtotal,
                'igv'                      => $igv,
                'total'                    => $total,
            ]);

            foreach ($data['lineas'] as $linea) {
                DB::table('venta_detalle')->insert([
                    'venta_id'            => $venta->id,
                    'producto_id'         => $linea['producto_id'],
                    'cantidad'            => $linea['cantidad'],
                    'precio_venta_unit'   => $linea['precio_venta_unit'],
                    'precio_compra_unit'  => $linea['precio_compra_unit'],
                ]);

                // Movimiento de inventario tipo salida (dispara el trigger de stock)
                DB::table('movimientos_inventario')->insert([
                    'producto_id'      => $linea['producto_id'],
                    'tipo'             => 'salida',
                    'cantidad'         => $linea['cantidad'],
                    'referencia_tipo'  => 'venta',
                    'referencia_id'    => $venta->id,
                    'usuario_id'       => auth()->id(),
                    'fecha'            => $data['fecha'],
                    'created_at'       => now(),
                ]);
            }

            // Si nace de una cotización, marcarla como facturada
            if (! empty($data['cotizacion_id'])) {
                Cotizacion::where('id', $data['cotizacion_id'])->update([
                    'estado' => 'facturada',
                ]);
            }

            // Si nace de una orden de compra del cliente, actualizar su estado y FK
            if (! empty($data['orden_compra_cliente_id'])) {
                OrdenCompraCliente::where('id', $data['orden_compra_cliente_id'])->update([
                    'estado'   => 'procesada',
                    'venta_id' => $venta->id,
                ]);
            }

            return $venta;
        });

        return redirect()->route('ventas.show', $venta)
            ->with('success', "Venta {$venta->codigo_formateado} registrada correctamente.");
    }

    public function show(Venta $venta)
    {
        $venta->load('cliente', 'usuario', 'cotizacion', 'ordenCompraCliente');

        $detalle = DB::table('venta_detalle')
            ->join('productos', 'productos.id', '=', 'venta_detalle.producto_id')
            ->where('venta_id', $venta->id)
            ->select('venta_detalle.*', 'productos.nombre as producto_nombre', 'productos.sku as producto_sku', 'productos.unidad_medida')
            ->get();

        $configuracion = Configuracion::where('activo', true)->first();

        return view('ventas.show', compact('venta', 'detalle', 'configuracion'));
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