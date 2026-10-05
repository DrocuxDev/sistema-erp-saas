<?php

namespace App\Http\Controllers;

use App\Models\ComprobanteVenta;
use App\Models\Configuracion;
use App\Models\SerieDocumento;
use App\Models\Venta;
use App\Services\DocumentoFiscalCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ComprobanteVentaController extends Controller
{
    public function index(Request $request)
    {
        $query = ComprobanteVenta::with('venta.cliente', 'usuario')->latest('fecha_emision');

        if ($request->filled('tipo') && $request->tipo !== 'TODOS') {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('estado') && $request->estado !== 'TODOS') {
            $query->where('estado', $request->estado);
        }

        $comprobantes = $query->paginate(15)->withQueryString();

        return view('comprobantes.index', compact('comprobantes'));
    }

    public function create(Request $request)
    {
        $ventas = Venta::with('cliente')
            ->whereDoesntHave('comprobantes', function ($q) {
                $q->where('estado', 'emitido');
            })
            ->latest('fecha')
            ->take(100)
            ->get();

        $ventaPreseleccionada = null;
        if ($request->filled('venta_id')) {
            $ventaPreseleccionada = Venta::with('cliente')->findOrFail($request->venta_id);

            if ($ventaPreseleccionada->comprobantes()->where('estado', 'emitido')->exists()) {
                return redirect()->route('ventas.show', $ventaPreseleccionada)
                    ->with('error', 'Esta venta ya tiene un comprobante emitido.');
            }
        }

        $tipos = [
            'factura' => 'Factura (F001)',
            'boleta' => 'Boleta (B001)',
            'nota_venta' => 'Nota de Venta (NV01)',
        ];

        return view('comprobantes.create', compact('ventas', 'ventaPreseleccionada', 'tipos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'venta_id' => 'required|exists:ventas,id',
            'tipo' => 'required|in:factura,boleta,nota_venta',
            'fecha_emision' => 'required|date',
            'precio_incluye_igv' => 'nullable|boolean',
            'pct_igv' => 'nullable|numeric|min:0|max:100|decimal:0,2',
            'pct_retencion' => 'nullable|numeric|min:0|max:100|decimal:0,2',
            'motivo_excepcion_tributaria' => 'nullable|string|max:1000',
        ]);

        $comprobante = DB::transaction(function () use ($data) {
            $venta = Venta::with('cliente')->whereKey($data['venta_id'])->lockForUpdate()->firstOrFail();
            // Lectura actual después del bloqueo: en MySQL REPEATABLE READ, exists()
            // puede reutilizar una instantánea tomada durante la validación previa.
            if ($venta->comprobantes()->where('estado', 'emitido')->lockForUpdate()->first()) {
                throw ValidationException::withMessages([
                    'venta_id' => 'Esta venta ya tiene un comprobante emitido. Anúlelo antes de emitir otro.',
                ]);
            }
            $perfil = DocumentoFiscalCalculator::perfil($venta->cliente, $data, [
                'precio_incluye_igv' => (bool) $venta->precio_incluye_igv,
                'pct_igv' => $venta->pct_igv,
                'pct_retencion' => $venta->pct_retencion,
            ]);
            $lineas = DB::table('venta_detalle')->where('venta_id', $venta->id)->get()->map(fn ($linea) => [
                'cantidad' => $linea->cantidad,
                'precio_venta_unit' => $linea->precio_venta_unit,
            ])->all();
            $importes = DocumentoFiscalCalculator::calcular($lineas, $perfil);
            $correlativoData = SerieDocumento::siguienteCodigo($data['tipo']);

            return ComprobanteVenta::create([
                'venta_id' => $venta->id,
                'usuario_id' => auth()->id(),
                'tipo' => $data['tipo'],
                'serie' => $correlativoData['serie'],
                'numero' => $correlativoData['correlativo'],
                'fecha_emision' => $data['fecha_emision'],
                'subtotal' => $importes['subtotal'],
                'igv' => $importes['igv'],
                'retencion' => $importes['retencion'],
                ...$perfil,
            ]);
        });

        return redirect()->route('comprobantes.show', $comprobante)
            ->with('success', "Comprobante {$comprobante->codigo_formateado} emitido correctamente.");
    }

    public function show(ComprobanteVenta $comprobante)
    {
        $comprobante->load('venta.cliente', 'venta.usuario', 'usuario');

        $detalle = DB::table('venta_detalle')
            ->join('productos', 'productos.id', '=', 'venta_detalle.producto_id')
            ->where('venta_id', $comprobante->venta_id)
            ->select('venta_detalle.*', 'productos.nombre as producto_nombre', 'productos.sku as producto_sku', 'productos.unidad_medida')
            ->get();

        $configuracion = Configuracion::where('activo', true)->first();

        return view('comprobantes.show', compact('comprobante', 'detalle', 'configuracion'));
    }

    public function anular(ComprobanteVenta $comprobante)
    {
        if ($comprobante->estado !== 'emitido') {
            return back()->with('error', 'Solo se pueden anular comprobantes en estado emitido.');
        }

        $comprobante->update(['estado' => 'anulado']);

        return back()->with('success', "Comprobante {$comprobante->codigo_formateado} anulado correctamente.");
    }

    public function pdf(ComprobanteVenta $comprobante)
    {
        $comprobante->load('venta.cliente', 'venta.usuario', 'usuario');

        $detalle = DB::table('venta_detalle')
            ->join('productos', 'productos.id', '=', 'venta_detalle.producto_id')
            ->where('venta_id', $comprobante->venta_id)
            ->select('venta_detalle.*', 'productos.nombre as producto_nombre', 'productos.sku as producto_sku', 'productos.unidad_medida')
            ->get();

        $configuracion = Configuracion::where('activo', true)->first();

        $pdf = Pdf::loadView('comprobantes.pdf', compact('comprobante', 'detalle', 'configuracion'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        return $pdf->stream("Comprobante_{$comprobante->codigo_formateado}.pdf");
    }

    public function descargarPdf(ComprobanteVenta $comprobante)
    {
        $comprobante->load('venta.cliente', 'venta.usuario', 'usuario');

        $detalle = DB::table('venta_detalle')
            ->join('productos', 'productos.id', '=', 'venta_detalle.producto_id')
            ->where('venta_id', $comprobante->venta_id)
            ->select('venta_detalle.*', 'productos.nombre as producto_nombre', 'productos.sku as producto_sku', 'productos.unidad_medida')
            ->get();

        $configuracion = Configuracion::where('activo', true)->first();

        $pdf = Pdf::loadView('comprobantes.pdf', compact('comprobante', 'detalle', 'configuracion'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        return $pdf->download("Comprobante_{$comprobante->codigo_formateado}.pdf");
    }
}
