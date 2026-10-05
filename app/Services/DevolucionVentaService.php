<?php

namespace App\Services;

use App\Models\DevolucionVenta;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DevolucionVentaService
{
    public function saldos(Venta $venta, bool $bloquear = false): array
    {
        $consulta = DB::table('devolucion_venta_detalle')
            ->join('devoluciones_venta', 'devoluciones_venta.id', '=', 'devolucion_venta_detalle.devolucion_venta_id')
            ->where('devoluciones_venta.venta_id', $venta->id)
            ->select('venta_detalle_id', 'cantidad');
        $devueltas = ($bloquear ? $consulta->lockForUpdate() : $consulta)->get()
            ->groupBy('venta_detalle_id')
            ->map(fn ($lineas) => $lineas->sum(fn ($linea) => DocumentoFiscalCalculator::aCentimos($linea->cantidad)));

        return $venta->detalles->mapWithKeys(fn ($detalle) => [
            $detalle->id => max(0, DocumentoFiscalCalculator::aCentimos($detalle->cantidad)
                - ($devueltas[$detalle->id] ?? 0)),
        ])->all();
    }

    public function registrar(Venta $venta, array $data, int $usuarioId): DevolucionVenta
    {
        return DB::transaction(function () use ($venta, $data, $usuarioId) {
            // El bloqueo de la venta serializa las devoluciones de todas sus líneas.
            $venta = Venta::whereKey($venta->id)->lockForUpdate()->firstOrFail();
            $existente = DevolucionVenta::where('idempotency_key', $data['idempotency_key'])->lockForUpdate()->first();
            if ($existente) {
                if ((int) $existente->venta_id !== (int) $venta->id) {
                    throw ValidationException::withMessages(['idempotency_key' => 'La solicitud ya pertenece a otra venta.']);
                }

                return $existente;
            }

            if ($data['fecha'] < $venta->fecha->toDateString()) {
                throw ValidationException::withMessages(['fecha' => 'La devolución no puede ser anterior a la venta.']);
            }

            $venta->load('detalles');
            $detalles = $venta->detalles->keyBy('id');
            $saldos = $this->saldos($venta, true);
            $devueltasAntes = [];
            $devueltasDespues = [];
            foreach ($detalles as $detalle) {
                $vendida = DocumentoFiscalCalculator::aCentimos($detalle->cantidad);
                $devueltasAntes[$detalle->id] = $vendida - $saldos[$detalle->id];
                $devueltasDespues[$detalle->id] = $devueltasAntes[$detalle->id];
            }

            foreach ($data['lineas'] as $indice => $linea) {
                $id = (int) $linea['venta_detalle_id'];
                if (! $detalles->has($id)) {
                    throw ValidationException::withMessages(["lineas.$indice.venta_detalle_id" => 'La línea no pertenece a esta venta.']);
                }
                $cantidad = DocumentoFiscalCalculator::aCentimos($linea['cantidad']);
                if ($cantidad < 1 || $cantidad > $saldos[$id]) {
                    throw ValidationException::withMessages(["lineas.$indice.cantidad" => 'La cantidad supera el saldo pendiente de devolución.']);
                }
                $devueltasDespues[$id] += $cantidad;
            }

            $perfil = [
                'precio_incluye_igv' => (bool) $venta->precio_incluye_igv,
                'pct_igv' => $venta->pct_igv,
                'pct_retencion' => $venta->pct_retencion,
            ];
            $calcular = fn (array $cantidades) => DocumentoFiscalCalculator::calcular(
                $detalles->map(fn ($detalle) => [
                    'cantidad' => DocumentoFiscalCalculator::deCentimos($cantidades[$detalle->id]),
                    'precio_venta_unit' => $detalle->precio_venta_unit,
                ])->all(),
                $perfil
            );
            $antes = $calcular($devueltasAntes);
            $despues = $calcular($devueltasDespues);
            $importes = [];
            foreach (['subtotal', 'igv', 'total', 'retencion'] as $campo) {
                $importes[$campo] = DocumentoFiscalCalculator::deCentimos(
                    DocumentoFiscalCalculator::aCentimos($despues[$campo]) - DocumentoFiscalCalculator::aCentimos($antes[$campo])
                );
            }

            $devolucion = DevolucionVenta::create([
                'venta_id' => $venta->id,
                'usuario_id' => $usuarioId,
                'idempotency_key' => $data['idempotency_key'],
                'fecha' => $data['fecha'],
                'motivo' => trim($data['motivo']),
                ...$importes,
            ]);

            $lineas = collect($data['lineas'])->sortBy(fn ($linea) => $detalles[(int) $linea['venta_detalle_id']]->producto_id);
            foreach ($lineas as $linea) {
                $detalle = $detalles[(int) $linea['venta_detalle_id']];
                $devolucion->detalles()->create([
                    'venta_detalle_id' => $detalle->id,
                    'cantidad' => $linea['cantidad'],
                    'precio_venta_unit' => $detalle->precio_venta_unit,
                ]);
                app(InventarioService::class)->registrar(
                    $detalle->producto_id, 'entrada', (string) $linea['cantidad'],
                    'devolucion_venta', $devolucion->id, $data['fecha']
                );
            }

            return $devolucion;
        }, 3);
    }
}
