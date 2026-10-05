<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\DevolucionCompra;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DevolucionCompraService
{
    public function saldos(Compra $compra, bool $bloquear = false): array
    {
        $consulta = DB::table('devolucion_compra_detalle')
            ->join('devoluciones_compra', 'devoluciones_compra.id', '=', 'devolucion_compra_detalle.devolucion_compra_id')
            ->where('devoluciones_compra.compra_id', $compra->id)
            ->select('compra_detalle_id', 'cantidad');
        $devueltas = ($bloquear ? $consulta->lockForUpdate() : $consulta)->get()
            ->groupBy('compra_detalle_id')
            ->map(fn ($lineas) => $lineas->sum(fn ($linea) => DocumentoFiscalCalculator::aCentimos($linea->cantidad)));

        return $compra->detalles->mapWithKeys(fn ($detalle) => [
            $detalle->id => max(0, DocumentoFiscalCalculator::aCentimos($detalle->cantidad)
                - ($devueltas[$detalle->id] ?? 0)),
        ])->all();
    }

    public function registrar(Compra $compra, array $data, int $usuarioId): DevolucionCompra
    {
        return DB::transaction(function () use ($compra, $data, $usuarioId) {
            $compra = Compra::whereKey($compra->id)->lockForUpdate()->firstOrFail();
            $existente = DevolucionCompra::where('idempotency_key', $data['idempotency_key'])->lockForUpdate()->first();
            if ($existente) {
                if ((int) $existente->compra_id !== (int) $compra->id) {
                    throw ValidationException::withMessages(['idempotency_key' => 'La solicitud ya pertenece a otra compra.']);
                }

                return $existente;
            }

            if ($data['fecha'] < $compra->fecha->toDateString()) {
                throw ValidationException::withMessages(['fecha' => 'La devolución no puede ser anterior a la recepción.']);
            }

            $compra->load('detalles');
            $detalles = $compra->detalles->keyBy('id');
            $saldos = $this->saldos($compra, true);
            $devueltasAntes = [];
            $devueltasDespues = [];
            foreach ($detalles as $detalle) {
                $recibida = DocumentoFiscalCalculator::aCentimos($detalle->cantidad);
                $devueltasAntes[$detalle->id] = $recibida - $saldos[$detalle->id];
                $devueltasDespues[$detalle->id] = $devueltasAntes[$detalle->id];
            }

            foreach ($data['lineas'] as $indice => $linea) {
                $id = (int) $linea['compra_detalle_id'];
                if (! $detalles->has($id)) {
                    throw ValidationException::withMessages(["lineas.$indice.compra_detalle_id" => 'La línea no pertenece a esta compra.']);
                }
                $cantidad = DocumentoFiscalCalculator::aCentimos($linea['cantidad']);
                if ($cantidad < 1 || $cantidad > $saldos[$id]) {
                    throw ValidationException::withMessages(["lineas.$indice.cantidad" => 'La cantidad supera el saldo pendiente de devolución.']);
                }
                $devueltasDespues[$id] += $cantidad;
            }

            $calcular = fn (array $cantidades) => DocumentoFiscalCalculator::calcular(
                $detalles->map(fn ($detalle) => [
                    'cantidad' => DocumentoFiscalCalculator::deCentimos($cantidades[$detalle->id]),
                    'precio_compra_unit' => $detalle->precio_compra_unit,
                ])->all(),
                ['precio_incluye_igv' => false, 'pct_igv' => '0.00', 'pct_retencion' => '0.00'],
                'precio_compra_unit'
            )['total'];
            $total = DocumentoFiscalCalculator::deCentimos(
                DocumentoFiscalCalculator::aCentimos($calcular($devueltasDespues))
                - DocumentoFiscalCalculator::aCentimos($calcular($devueltasAntes))
            );

            $devolucion = DevolucionCompra::create([
                'compra_id' => $compra->id,
                'usuario_id' => $usuarioId,
                'idempotency_key' => $data['idempotency_key'],
                'fecha' => $data['fecha'],
                'motivo' => trim($data['motivo']),
                'total' => $total,
            ]);

            $lineas = collect($data['lineas'])->sortBy(fn ($linea) => $detalles[(int) $linea['compra_detalle_id']]->producto_id);
            foreach ($lineas as $linea) {
                $detalle = $detalles[(int) $linea['compra_detalle_id']];
                $devolucion->detalles()->create([
                    'compra_detalle_id' => $detalle->id,
                    'cantidad' => $linea['cantidad'],
                    'precio_compra_unit' => $detalle->precio_compra_unit,
                ]);
                app(InventarioService::class)->registrar(
                    $detalle->producto_id, 'salida', (string) $linea['cantidad'],
                    'devolucion_compra', $devolucion->id, $data['fecha']
                );
            }

            return $devolucion;
        }, 3);
    }
}
