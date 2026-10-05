<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Services\DevolucionCompraService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DevolucionCompraController extends Controller
{
    public function create(Compra $compra, DevolucionCompraService $service)
    {
        $compra->load('proveedor', 'detalles.producto');
        $saldos = $service->saldos($compra);
        if (array_sum($saldos) === 0) {
            return redirect()->route('compras.show', $compra)
                ->with('error', 'Todos los productos de esta recepción ya fueron devueltos.');
        }

        $idempotencyKey = (string) Str::uuid();

        return view('compras.devolucion-create', compact('compra', 'saldos', 'idempotencyKey'));
    }

    public function store(Request $request, Compra $compra, DevolucionCompraService $service)
    {
        $data = $request->validate([
            'idempotency_key' => 'required|uuid',
            'fecha' => 'required|date|before_or_equal:today',
            'motivo' => 'required|string|min:5|max:1000',
            'lineas' => 'required|array|min:1',
            'lineas.*.compra_detalle_id' => 'required|integer|distinct:strict',
            'lineas.*.cantidad' => 'required|numeric|min:0|decimal:0,2',
        ]);

        $data['lineas'] = collect($data['lineas'])->filter(fn ($linea) => (float) $linea['cantidad'] > 0)->values()->all();
        if ($data['lineas'] === []) {
            throw ValidationException::withMessages(['lineas' => 'Indique al menos una cantidad a devolver.']);
        }

        $devolucion = $service->registrar($compra, $data, (int) $request->user()->id);

        return redirect()->route('compras.show', $compra)
            ->with('success', "Devolución #{$devolucion->id} registrada. El stock fue actualizado.");
    }
}
