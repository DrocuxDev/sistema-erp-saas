<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Cotizacion;
use App\Models\CotizacionDetalle;
use App\Models\CotizacionResumen;
use App\Models\OrdenCompraCliente;
use App\Models\Producto;
use App\Services\CotizacionResumenCalculator;
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

        $cotizaciones = $query->paginate(15)->withQueryString();
        $clientes = Cliente::where('estado', 'activo')->orderBy('nombre')->get();

        return view('cotizaciones.index', compact('cotizaciones', 'clientes'));
    }

    public function create(Request $request)
    {
        $clientes = Cliente::where('estado', 'activo')->orderBy('nombre')->get();
        $productos = Producto::where('puede_venderse', true)
            ->with(['productoProveedores.proveedor'])
            ->orderBy('nombre')
            ->get();

        $ordenCompra = null;
        $clienteIdPreseleccionado = old('cliente_id');

        if ($request->filled('orden_compra_cliente_id')) {
            $ordenCompra = OrdenCompraCliente::with('cliente')->findOrFail($request->orden_compra_cliente_id);
            $clienteIdPreseleccionado = $ordenCompra->cliente_id;
        }

        return view('cotizaciones.create', compact(
            'clientes',
            'productos',
            'ordenCompra',
            'clienteIdPreseleccionado'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validarCotizacion($request);
        $configuracion = Configuracion::where('activo', true)->firstOrFail();
        $lineas = collect($data['lineas']);

        $cotizacion = DB::transaction(function () use ($data, $configuracion, $lineas) {
            $cotizacion = Cotizacion::create([
                'cliente_id' => $data['cliente_id'],
                'usuario_id' => auth()->id(),
                'orden_compra_cliente_id' => $data['orden_compra_cliente_id'] ?? null,
                'fecha' => $data['fecha'],
                'estado' => 'pendiente',
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            $this->guardarDetalleYResumen($cotizacion, $lineas, $configuracion, (float) ($data['no_utilizable'] ?? 0));

            if (! empty($data['orden_compra_cliente_id'])) {
                OrdenCompraCliente::where('id', $data['orden_compra_cliente_id'])->update([
                    'estado' => 'procesada',
                    'cotizacion_id' => $cotizacion->id,
                ]);
            }

            return $cotizacion;
        });

        return redirect()->route('cotizaciones.show', $cotizacion)
            ->with('success', 'Cotización registrada correctamente.');
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

        return view('cotizaciones.show', compact('cotizacion'));
    }

    public function edit(Cotizacion $cotizacion)
    {
        if ($cotizacion->estado !== 'pendiente') {
            return redirect()->route('cotizaciones.show', $cotizacion)
                ->with('error', 'Solo se pueden editar cotizaciones en estado pendiente.');
        }

        $cotizacion->load('detalles.producto', 'detalles.proveedor', 'resumen');
        $clientes = Cliente::where('estado', 'activo')->orderBy('nombre')->get();
        $productos = Producto::where('puede_venderse', true)
            ->with(['productoProveedores.proveedor'])
            ->orderBy('nombre')
            ->get();

        return view('cotizaciones.edit', compact('cotizacion', 'clientes', 'productos'));
    }

    public function update(Request $request, Cotizacion $cotizacion)
    {
        if ($cotizacion->estado !== 'pendiente') {
            return redirect()->route('cotizaciones.show', $cotizacion)
                ->with('error', 'Solo se pueden editar cotizaciones en estado pendiente.');
        }

        $data = $this->validarCotizacion($request);
        $configuracion = Configuracion::where('activo', true)->firstOrFail();
        $lineas = collect($data['lineas']);

        DB::transaction(function () use ($cotizacion, $data, $configuracion, $lineas) {
            $cotizacion->update([
                'cliente_id' => $data['cliente_id'],
                'fecha' => $data['fecha'],
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            $cotizacion->detalles()->delete();
            $cotizacion->resumen()?->delete();

            $this->guardarDetalleYResumen($cotizacion, $lineas, $configuracion, (float) ($data['no_utilizable'] ?? 0));
        });

        return redirect()->route('cotizaciones.show', $cotizacion)
            ->with('success', 'Cotización actualizada correctamente.');
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

    public function pdf(Cotizacion $cotizacion)
    {
        $cotizacion->load([
            'cliente',
            'detalles.producto',
            'detalles.proveedor',
            'resumen',
        ]);

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('cotizaciones.pdf', compact('cotizacion'));

            return $pdf->download('cotizacion-' . $cotizacion->id . '.pdf');
        }

        return view('cotizaciones.pdf', compact('cotizacion'));
    }

    private function validarCotizacion(Request $request): array
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'orden_compra_cliente_id' => 'nullable|exists:ordenes_compra_cliente,id',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string|max:255',
            'no_utilizable' => 'nullable|numeric|min:0',
            'lineas' => 'required|array|min:1',
            'lineas.*.producto_id' => 'required|exists:productos,id',
            'lineas.*.proveedor_id' => 'required|exists:proveedores,id',
            'lineas.*.descripcion' => 'nullable|string|max:255',
            'lineas.*.unidad_medida' => 'nullable|string|max:20',
            'lineas.*.cantidad' => 'required|numeric|min:0.01',
            'lineas.*.precio_venta_unit' => 'required|numeric|min:0',
            'lineas.*.precio_compra_unit' => 'required|numeric|min:0',
            'lineas.*.igv_compra' => 'nullable|numeric|min:0',
        ]);

        $lineas = collect($data['lineas'])->map(function (array $linea) {
            $linea['igv_compra'] = (float) ($linea['igv_compra'] ?? 0);

            return $linea;
        });

        $configuracion = Configuracion::where('activo', true)->firstOrFail();
        $resumen = CotizacionResumenCalculator::calcular($lineas, $configuracion, (float) ($data['no_utilizable'] ?? 0));

        if (! CotizacionResumenCalculator::validarSubtotal($lineas, $resumen['subtotal'])) {
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
        float $noUtilizable = 0
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

        $resumen = CotizacionResumenCalculator::calcular($lineas, $configuracion, $noUtilizable);

        CotizacionResumen::create([
            'cotizacion_id' => $cotizacion->id,
            'configuracion_id' => $configuracion->id,
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
