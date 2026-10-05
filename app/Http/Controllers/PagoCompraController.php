<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroFinancieroRequest;
use App\Models\Compra;
use App\Models\PagoCompra;
use App\Services\RegistroFinancieroService;
use App\Services\SaldoFinancieroService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PagoCompraController extends Controller
{
    public function create(Request $request, Compra $compra, SaldoFinancieroService $service)
    {
        $tipo = $request->query('tipo', 'pago');
        abort_unless(in_array($tipo, ['pago', 'reintegro'], true), 404);
        $compra->load('proveedor');
        $saldo = $service->compra($compra);
        $limite = $tipo === 'pago' ? $saldo['saldo'] : $saldo['exceso'];
        if ($limite === 0) {
            return redirect()->route('compras.show', $compra)
                ->with('error', 'No existe saldo disponible para registrar esta operación.');
        }

        return view('finanzas.registro-create', [
            'tipo' => $tipo,
            'titulo' => $tipo === 'pago' ? 'Registrar pago' : 'Registrar reintegro del proveedor',
            'origen' => 'Compras',
            'documento' => 'Recepción #'.$compra->id,
            'tercero' => $compra->proveedor->nombre,
            'fechaOrigen' => $compra->fecha,
            'limite' => $limite,
            'rutaVolver' => route('compras.show', $compra),
            'rutaStore' => route('compras.pagos.store', $compra),
            'idempotencyKey' => (string) Str::uuid(),
        ]);
    }

    public function store(RegistroFinancieroRequest $request, Compra $compra, RegistroFinancieroService $service)
    {
        $data = $request->validated();
        $registro = $service->pago($compra, $data, (int) $request->user()->id);

        return redirect()->route('compras.show', $compra)
            ->with('success', ucfirst($registro->tipo)." #{$registro->id} registrado correctamente.");
    }

    public function anular(Request $request, Compra $compra, PagoCompra $pago, RegistroFinancieroService $service)
    {
        $data = $request->validate(['motivo_anulacion' => 'required|string|min:10|max:1000']);
        $service->anularPago($compra, $pago, $data['motivo_anulacion'], (int) $request->user()->id);

        return redirect()->route('compras.show', $compra)->with('success', "Movimiento #{$pago->id} anulado.");
    }
}
