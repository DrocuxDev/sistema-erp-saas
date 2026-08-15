<?php

namespace App\Services;

use App\Models\Configuracion;
use Illuminate\Support\Collection;

class CotizacionResumenCalculator
{
    /**
     * @param  Collection<int, array{cantidad: float, precio_venta_unit: float, precio_compra_unit: float, igv_compra: float}>  $lineas
     * @return array<string, float>
     */
    public static function calcular(Collection $lineas, Configuracion $configuracion, float $noUtilizable = 0): array
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

        $igv = round($subtotal * ((float) $configuracion->pct_igv / 100), 2);
        $retencion = round($subtotal * ((float) $configuracion->pct_retencion / 100), 2);
        $ganancia = round($subtotal - $compraFija, 2);
        $comisionVenta = round($subtotal * ((float) $configuracion->pct_comision_venta / 100), 2);
        $comisionGanancia = round(max(0, $ganancia) * ((float) $configuracion->pct_comision_ganancia / 100), 2);

        return [
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
