<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Services\DevolucionVentaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DevolucionVentaController extends Controller
{
    public function create(Venta $venta, DevolucionVentaService $service)
    {
        $venta->load('cliente', 'detalles.producto');
        $saldos = $service->saldos($venta);
        if (array_sum($saldos) === 0) {
            return redirect()->route('ventas.show', $venta)
                ->with('error', 'Todos los productos de esta venta ya fueron devueltos.');
        }

        $idempotencyKey = (string) Str::uuid();

        return view('ventas.devolucion-create', compact('venta', 'saldos', 'idempotencyKey'));
    }

    public function store(Request $request, Venta $venta, DevolucionVentaService $service)
    {
        $data = $request->validate([
            'idempotency_key' => 'required|uuid',
            'fecha' => 'required|date|before_or_equal:today',
            'motivo' => 'required|string|min:5|max:1000',
            'lineas' => 'required|array|min:1',
            'lineas.*.venta_detalle_id' => 'required|integer|distinct:strict',
            'lineas.*.cantidad' => 'required|numeric|min:0|decimal:0,2',
        ]);

        $data['lineas'] = collect($data['lineas'])->filter(fn ($linea) => (float) $linea['cantidad'] > 0)->values()->all();
        if ($data['lineas'] === []) {
            throw ValidationException::withMessages(['lineas' => 'Indique al menos una cantidad a devolver.']);
        }

        $devolucion = $service->registrar($venta, $data, (int) $request->user()->id);

        return redirect()->route('ventas.show', $venta)
            ->with('success', "Devolución #{$devolucion->id} registrada. El stock fue actualizado.");
    }
}
