<?php

namespace App\Services;

use Illuminate\Support\Collection;

class CotizacionResumenCalculator
{
    /**
     * @param  Collection<int, array{cantidad: float, precio_venta_unit: float, precio_compra_unit: float, igv_compra: float}>  $lineas
     * @param  array{pct_igv: float, pct_retencion: float, pct_comision_venta: float, pct_comision_ganancia: float}  $tasas
     * @return array<string, float>
     */
    public static function calcular(Collection $lineas, array $tasas, float $noUtilizable = 0): array
    {
        $subtotal = round($lineas->sum(
            fn (array $linea) => (float) $linea['cantidad'] * (float) $linea['precio_venta_unit']
        ), 2);

        $compraActiva = round($lineas->sum(
            fn (array $linea) => (float) $linea['cantidad'] * (float) $linea['precio_compra_unit']
        ), 2);

        $compraFija = round($lineas->sum(
            fn (array $linea) => ((float) $linea['cantidad'] * (float) $linea['precio_compra_unit']) + (float) $linea['igv_compra']
        ) + $noUtilizable, 2);

        $pctIgv = (float) ($tasas['pct_igv'] ?? 18);
        $pctRetencion = (float) ($tasas['pct_retencion'] ?? 3);
        $pctComisionVenta = (float) ($tasas['pct_comision_venta'] ?? 5);
        $pctComisionGanancia = (float) ($tasas['pct_comision_ganancia'] ?? 10);

        $igv = round($subtotal * ($pctIgv / 100), 2);
        $retencion = round($subtotal * ($pctRetencion / 100), 2);
        $ganancia = round($subtotal - $compraFija, 2);
        $comisionVenta = round($subtotal * ($pctComisionVenta / 100), 2);
        $comisionGanancia = round(max(0, $ganancia) * ($pctComisionGanancia / 100), 2);

        return [
            'pct_igv' => $pctIgv,
            'pct_retencion' => $pctRetencion,
            'pct_comision_venta' => $pctComisionVenta,
            'pct_comision_ganancia' => $pctComisionGanancia,
            'subtotal' => $subtotal,
            'igv' => $igv,
            'retencion' => $retencion,
            'compra_activa' => $compraActiva,
            'compra_fija' => $compraFija,
            'no_utilizable' => round($noUtilizable, 2),
            'comision_venta' => $comisionVenta,
            'comision_ganancia' => $comisionGanancia,
        ];
    }

    public static function validarSubtotal(Collection $lineas, float $subtotal): bool
    {
        $sumaReal = round($lineas->sum(
            fn (array $linea) => (float) $linea['cantidad'] * (float) $linea['precio_venta_unit']
        ), 2);

        return abs($subtotal - $sumaReal) <= 0.01;
    }
}
