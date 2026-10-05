<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\GuiaRemision;
use App\Models\Producto;
use App\Models\SerieDocumento;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuiaRemisionController extends Controller
{
    public function index(Request $request)
    {
        $query = GuiaRemision::with('venta.cliente', 'usuario')->latest('fecha_traslado');

        if ($request->filled('estado') && $request->estado !== 'TODOS') {
            $query->where('estado', $request->estado);
        }

        $guias = $query->paginate(15)->withQueryString();

        return view('guias-remision.index', compact('guias'));
    }

    public function create(Request $request)
    {
        $ventas = Venta::with('cliente')->latest('fecha')->take(100)->get();
        $productos = Producto::where('puede_venderse', true)->orderBy('nombre')->get();

        $ventaPreseleccionada = null;
        $lineasPrecargadas = collect();
        if ($request->filled('venta_id')) {
            $ventaPreseleccionada = Venta::with('cliente')->findOrFail($request->venta_id);
            $lineasPrecargadas = DB::table('venta_detalle')
                ->join('productos', 'productos.id', '=', 'venta_detalle.producto_id')
                ->where('venta_id', $ventaPreseleccionada->id)
                ->select('venta_detalle.producto_id', 'venta_detalle.cantidad', 'productos.nombre as producto_nombre', 'productos.unidad_medida')
                ->get();
        }

        return view('guias-remision.create', compact('ventas', 'productos', 'ventaPreseleccionada', 'lineasPrecargadas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'venta_id' => 'nullable|exists:ventas,id',
            'fecha_traslado' => 'required|date',
            'motivo_traslado' => 'required|in:venta,compra,traslado_interno,otro',
            'punto_partida' => 'nullable|string|max:255',
            'punto_llegada' => 'nullable|string|max:255',
            'transportista' => 'nullable|string|max:150',
            'placa_vehiculo' => 'nullable|string|max:20',
            'lineas' => 'required|array|min:1',
            'lineas.*.producto_id' => 'required|exists:productos,id',
            'lineas.*.cantidad' => 'required|numeric|min:0.01',
            'lineas.*.unidad_medida' => 'nullable|string|max:20',
        ]);

        $guia = DB::transaction(function () use ($data) {
            $correlativoData = SerieDocumento::siguienteCodigo('guia_remision');

            $guia = GuiaRemision::create([
                'venta_id' => $data['venta_id'] ?? null,
                'usuario_id' => auth()->id(),
                'serie' => $correlativoData['serie'],
                'numero' => $correlativoData['correlativo'],
                'fecha_traslado' => $data['fecha_traslado'],
                'motivo_traslado' => $data['motivo_traslado'],
                'punto_partida' => $data['punto_partida'] ?? null,
                'punto_llegada' => $data['punto_llegada'] ?? null,
                'transportista' => $data['transportista'] ?? null,
                'placa_vehiculo' => $data['placa_vehiculo'] ?? null,
            ]);

            foreach ($data['lineas'] as $linea) {
                DB::table('guia_remision_detalle')->insert([
                    'guia_remision_id' => $guia->id,
                    'producto_id' => $linea['producto_id'],
                    'cantidad' => $linea['cantidad'],
                    'unidad_medida' => $linea['unidad_medida'] ?? 'UND',
                ]);
            }

            return $guia;
        });

        return redirect()->route('guias-remision.show', $guia)
            ->with('success', "Guía {$guia->codigo_formateado} emitida correctamente.");
    }

    public function show(GuiaRemision $guia_remision)
    {
        $guia_remision->load('venta.cliente', 'venta.usuario', 'usuario', 'detalles.producto');

        $configuracion = Configuracion::where('activo', true)->first();

        return view('guias-remision.show', ['guia' => $guia_remision, 'configuracion' => $configuracion]);
    }

    public function anular(GuiaRemision $guia_remision)
    {
        if ($guia_remision->estado !== 'emitida') {
            return back()->with('error', 'Solo se pueden anular guías en estado emitida.');
        }

        $guia_remision->update(['estado' => 'anulada']);

        return back()->with('success', "Guía {$guia_remision->codigo_formateado} anulada correctamente.");
    }

    public function pdf(GuiaRemision $guia_remision)
    {
        $guia_remision->load('venta.cliente', 'venta.usuario', 'usuario', 'detalles.producto');

        $configuracion = Configuracion::where('activo', true)->first();

        $pdf = Pdf::loadView('guias-remision.pdf', ['guia' => $guia_remision, 'configuracion' => $configuracion])
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        return $pdf->stream("Guia_{$guia_remision->codigo_formateado}.pdf");
    }

    public function descargarPdf(GuiaRemision $guia_remision)
    {
        $guia_remision->load('venta.cliente', 'venta.usuario', 'usuario', 'detalles.producto');

        $configuracion = Configuracion::where('activo', true)->first();

        $pdf = Pdf::loadView('guias-remision.pdf', ['guia' => $guia_remision, 'configuracion' => $configuracion])
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        return $pdf->download("Guia_{$guia_remision->codigo_formateado}.pdf");
    }
}
