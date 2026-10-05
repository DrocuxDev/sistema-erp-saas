<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroFinancieroRequest;
use App\Models\CobroVenta;
use App\Models\Venta;
use App\Services\RegistroFinancieroService;
use App\Services\SaldoFinancieroService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CobroVentaController extends Controller
{
    public function create(Request $request, Venta $venta, SaldoFinancieroService $service)
    {
        $tipo = $request->query('tipo', 'cobro');
        abort_unless(in_array($tipo, ['cobro', 'reembolso'], true), 404);
        $venta->load('cliente');
        $saldo = $service->venta($venta);
        $limite = $tipo === 'cobro' ? $saldo['saldo'] : $saldo['exceso'];
        if ($limite === 0) {
            return redirect()->route('ventas.show', $venta)
                ->with('error', 'No existe saldo disponible para registrar esta operación.');
        }

        return view('finanzas.registro-create', [
            'tipo' => $tipo,
            'titulo' => $tipo === 'cobro' ? 'Registrar cobro' : 'Registrar reembolso al cliente',
            'origen' => 'Ventas',
            'documento' => $venta->codigo_formateado,
            'tercero' => $venta->cliente->nombre,
            'fechaOrigen' => $venta->fecha,
            'limite' => $limite,
            'rutaVolver' => route('ventas.show', $venta),
            'rutaStore' => route('ventas.cobros.store', $venta),
            'idempotencyKey' => (string) Str::uuid(),
        ]);
    }

    public function store(RegistroFinancieroRequest $request, Venta $venta, RegistroFinancieroService $service)
    {
        $data = $request->validated();
        $registro = $service->cobro($venta, $data, (int) $request->user()->id);

        return redirect()->route('ventas.show', $venta)
            ->with('success', ucfirst($registro->tipo)." #{$registro->id} registrado correctamente.");
    }

    public function anular(Request $request, Venta $venta, CobroVenta $cobro, RegistroFinancieroService $service)
    {
        $data = $request->validate(['motivo_anulacion' => 'required|string|min:10|max:1000']);
        $service->anularCobro($venta, $cobro, $data['motivo_anulacion'], (int) $request->user()->id);

        return redirect()->route('ventas.show', $venta)->with('success', "Movimiento #{$cobro->id} anulado.");
    }
}
