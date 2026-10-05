<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class SaldoFinancieroService
{
    public function venta(Venta $venta, bool $bloquear = false): array
    {
        $devoluciones = DB::table('devoluciones_venta')
            ->where('venta_id', $venta->id)->select('total', 'retencion');
        $cobros = DB::table('cobros_venta')
            ->where('venta_id', $venta->id)->where('estado', 'registrado')->select('tipo', 'monto');

        $devuelto = ($bloquear ? $devoluciones->lockForUpdate() : $devoluciones)->get()
            ->sum(fn ($item) => DocumentoFiscalCalculator::aCentimos($item->total)
                - DocumentoFiscalCalculator::aCentimos($item->retencion));
        $recibido = ($bloquear ? $cobros->lockForUpdate() : $cobros)->get()
            ->sum(fn ($item) => ($item->tipo === 'cobro' ? 1 : -1) * DocumentoFiscalCalculator::aCentimos($item->monto));
        $original = DocumentoFiscalCalculator::aCentimos($venta->total)
            - DocumentoFiscalCalculator::aCentimos($venta->retencion);
        $neto = $original - $devuelto;

        return [
            'original' => $original,
            'devuelto' => $devuelto,
            'neto' => $neto,
            'recibido' => $recibido,
            'saldo' => max(0, $neto - $recibido),
            'exceso' => max(0, $recibido - $neto),
        ];
    }

    public function compra(Compra $compra, bool $bloquear = false): array
    {
        $devoluciones = DB::table('devoluciones_compra')
            ->where('compra_id', $compra->id)->select('total');
        $pagos = DB::table('pagos_compra')
            ->where('compra_id', $compra->id)->where('estado', 'registrado')->select('tipo', 'monto');

        $devuelto = ($bloquear ? $devoluciones->lockForUpdate() : $devoluciones)->get()
            ->sum(fn ($item) => DocumentoFiscalCalculator::aCentimos($item->total));
        $pagado = ($bloquear ? $pagos->lockForUpdate() : $pagos)->get()
            ->sum(fn ($item) => ($item->tipo === 'pago' ? 1 : -1) * DocumentoFiscalCalculator::aCentimos($item->monto));
        $original = DocumentoFiscalCalculator::aCentimos($compra->total);
        $neto = $original - $devuelto;

        return [
            'original' => $original,
            'devuelto' => $devuelto,
            'neto' => $neto,
            'pagado' => $pagado,
            'saldo' => max(0, $neto - $pagado),
            'exceso' => max(0, $pagado - $neto),
        ];
    }
}
