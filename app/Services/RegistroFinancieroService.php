<?php

namespace App\Services;

use App\Models\CobroVenta;
use App\Models\Compra;
use App\Models\PagoCompra;
use App\Models\Venta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistroFinancieroService
{
    public function __construct(private SaldoFinancieroService $saldos) {}

    public function cobro(Venta $venta, array $data, int $usuarioId): CobroVenta
    {
        return DB::transaction(function () use ($venta, $data, $usuarioId) {
            $venta = Venta::whereKey($venta->id)->lockForUpdate()->firstOrFail();
            $existente = CobroVenta::where('idempotency_key', $data['idempotency_key'])->lockForUpdate()->first();
            if ($existente) {
                if ((int) $existente->venta_id !== (int) $venta->id) {
                    throw ValidationException::withMessages(['idempotency_key' => 'La solicitud pertenece a otra venta.']);
                }
                if ($existente->estado === 'anulado') {
                    throw ValidationException::withMessages(['idempotency_key' => 'La solicitud ya fue anulada.']);
                }

                return $existente;
            }

            if ($data['fecha'] < $venta->fecha->toDateString()) {
                throw ValidationException::withMessages(['fecha' => 'La fecha no puede ser anterior a la venta.']);
            }
            if ($data['tipo'] === 'reembolso') {
                $ultimaDevolucion = DB::table('devoluciones_venta')->where('venta_id', $venta->id)
                    ->orderByDesc('fecha')->lockForUpdate()->first(['fecha']);
                if ($ultimaDevolucion && $data['fecha'] < Carbon::parse($ultimaDevolucion->fecha)->toDateString()) {
                    throw ValidationException::withMessages(['fecha' => 'El reembolso no puede ser anterior a la devolución.']);
                }
            }

            $saldo = $this->saldos->venta($venta, true);
            $monto = DocumentoFiscalCalculator::aCentimos($data['monto']);
            $limite = $data['tipo'] === 'cobro' ? $saldo['saldo'] : $saldo['exceso'];
            if ($monto < 1 || $monto > $limite) {
                throw ValidationException::withMessages([
                    'monto' => 'El importe supera el saldo disponible para '.($data['tipo'] === 'cobro' ? 'cobro' : 'reembolso').'.',
                ]);
            }

            return CobroVenta::create([
                'venta_id' => $venta->id,
                'usuario_id' => $usuarioId,
                ...$data,
                'monto' => DocumentoFiscalCalculator::deCentimos($monto),
            ]);
        }, 3);
    }

    public function pago(Compra $compra, array $data, int $usuarioId): PagoCompra
    {
        return DB::transaction(function () use ($compra, $data, $usuarioId) {
            $compra = Compra::whereKey($compra->id)->lockForUpdate()->firstOrFail();
            $existente = PagoCompra::where('idempotency_key', $data['idempotency_key'])->lockForUpdate()->first();
            if ($existente) {
                if ((int) $existente->compra_id !== (int) $compra->id) {
                    throw ValidationException::withMessages(['idempotency_key' => 'La solicitud pertenece a otra compra.']);
                }
                if ($existente->estado === 'anulado') {
                    throw ValidationException::withMessages(['idempotency_key' => 'La solicitud ya fue anulada.']);
                }

                return $existente;
            }

            if ($data['fecha'] < $compra->fecha->toDateString()) {
                throw ValidationException::withMessages(['fecha' => 'La fecha no puede ser anterior a la recepción.']);
            }
            if ($data['tipo'] === 'reintegro') {
                $ultimaDevolucion = DB::table('devoluciones_compra')->where('compra_id', $compra->id)
                    ->orderByDesc('fecha')->lockForUpdate()->first(['fecha']);
                if ($ultimaDevolucion && $data['fecha'] < Carbon::parse($ultimaDevolucion->fecha)->toDateString()) {
                    throw ValidationException::withMessages(['fecha' => 'El reintegro no puede ser anterior a la devolución.']);
                }
            }

            $saldo = $this->saldos->compra($compra, true);
            $monto = DocumentoFiscalCalculator::aCentimos($data['monto']);
            $limite = $data['tipo'] === 'pago' ? $saldo['saldo'] : $saldo['exceso'];
            if ($monto < 1 || $monto > $limite) {
                throw ValidationException::withMessages([
                    'monto' => 'El importe supera el saldo disponible para '.($data['tipo'] === 'pago' ? 'pago' : 'reintegro').'.',
                ]);
            }

            return PagoCompra::create([
                'compra_id' => $compra->id,
                'usuario_id' => $usuarioId,
                ...$data,
                'monto' => DocumentoFiscalCalculator::deCentimos($monto),
            ]);
        }, 3);
    }

    public function anularCobro(Venta $venta, CobroVenta $cobro, string $motivo, int $usuarioId): CobroVenta
    {
        return DB::transaction(function () use ($venta, $cobro, $motivo, $usuarioId) {
            $venta = Venta::whereKey($venta->id)->lockForUpdate()->firstOrFail();
            $cobro = CobroVenta::whereKey($cobro->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $cobro->venta_id === (int) $venta->id, 404);
            if ($cobro->estado === 'anulado') {
                return $cobro;
            }

            $saldo = $this->saldos->venta($venta, true);
            if ($cobro->tipo === 'cobro' && $saldo['recibido'] < DocumentoFiscalCalculator::aCentimos($cobro->monto)) {
                throw ValidationException::withMessages([
                    'motivo_anulacion' => 'Anule primero los reembolsos posteriores; este cobro aún los respalda.',
                ]);
            }

            $cobro->update([
                'estado' => 'anulado',
                'motivo_anulacion' => trim($motivo),
                'anulado_por' => $usuarioId,
                'anulado_en' => now(),
            ]);

            return $cobro;
        }, 3);
    }

    public function anularPago(Compra $compra, PagoCompra $pago, string $motivo, int $usuarioId): PagoCompra
    {
        return DB::transaction(function () use ($compra, $pago, $motivo, $usuarioId) {
            $compra = Compra::whereKey($compra->id)->lockForUpdate()->firstOrFail();
            $pago = PagoCompra::whereKey($pago->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $pago->compra_id === (int) $compra->id, 404);
            if ($pago->estado === 'anulado') {
                return $pago;
            }

            $saldo = $this->saldos->compra($compra, true);
            if ($pago->tipo === 'pago' && $saldo['pagado'] < DocumentoFiscalCalculator::aCentimos($pago->monto)) {
                throw ValidationException::withMessages([
                    'motivo_anulacion' => 'Anule primero los reintegros posteriores; este pago aún los respalda.',
                ]);
            }

            $pago->update([
                'estado' => 'anulado',
                'motivo_anulacion' => trim($motivo),
                'anulado_por' => $usuarioId,
                'anulado_en' => now(),
            ]);

            return $pago;
        }, 3);
    }
}
