<?php

namespace App\Services;

use Illuminate\Support\Collection;

class CotizacionResumenCalculator
{
    /**
     * @param  Collection<int, array{cantidad: string|int|float, precio_venta_unit: string|int|float, precio_compra_unit: string|int|float, igv_compra: string|int|float}>  $lineas
     * @param  array{pct_igv: string|int|float, pct_retencion: string|int|float, pct_comision_venta: string|int|float, pct_comision_ganancia: string|int|float}  $tasas
     * @return array<string, string>
     */
    public static function calcular(Collection $lineas, array $tasas, string|int|float $noUtilizable = 0): array
    {
        $fiscal = DocumentoFiscalCalculator::calcular($lineas->all(), $tasas);
        $compraActivaCent = 0;
        $compraFijaCent = DocumentoFiscalCalculator::aCentimos($noUtilizable);
        foreach ($lineas as $linea) {
            $compraCent = DocumentoFiscalCalculator::importeLineaCentimos($linea['cantidad'], $linea['precio_compra_unit']);
            $compraActivaCent += $compraCent;
            $compraFijaCent += $compraCent + DocumentoFiscalCalculator::aCentimos($linea['igv_compra'] ?? 0);
        }

        $subtotalCent = DocumentoFiscalCalculator::aCentimos($fiscal['subtotal']);
        $gananciaCent = $subtotalCent - $compraFijaCent;
        $pctIgv = DocumentoFiscalCalculator::deCentimos(DocumentoFiscalCalculator::aCentimos($tasas['pct_igv'] ?? 18));
        $pctRetencion = DocumentoFiscalCalculator::deCentimos(DocumentoFiscalCalculator::aCentimos($tasas['pct_retencion'] ?? 3));
        $pctComisionVentaCent = DocumentoFiscalCalculator::aCentimos($tasas['pct_comision_venta'] ?? 5);
        $pctComisionGananciaCent = DocumentoFiscalCalculator::aCentimos($tasas['pct_comision_ganancia'] ?? 10);
        $comisionVentaCent = DocumentoFiscalCalculator::porcentaje($subtotalCent, $pctComisionVentaCent);
        $comisionGananciaCent = DocumentoFiscalCalculator::porcentaje(max(0, $gananciaCent), $pctComisionGananciaCent);

        return [
            'pct_igv' => $pctIgv,
            'pct_retencion' => $pctRetencion,
            'pct_comision_venta' => DocumentoFiscalCalculator::deCentimos($pctComisionVentaCent),
            'pct_comision_ganancia' => DocumentoFiscalCalculator::deCentimos($pctComisionGananciaCent),
            'subtotal' => $fiscal['subtotal'],
            'igv' => $fiscal['igv'],
            'retencion' => $fiscal['retencion'],
            'compra_activa' => DocumentoFiscalCalculator::deCentimos($compraActivaCent),
            'compra_fija' => DocumentoFiscalCalculator::deCentimos($compraFijaCent),
            'no_utilizable' => DocumentoFiscalCalculator::deCentimos(DocumentoFiscalCalculator::aCentimos($noUtilizable)),
            'comision_venta' => DocumentoFiscalCalculator::deCentimos($comisionVentaCent),
            'comision_ganancia' => DocumentoFiscalCalculator::deCentimos($comisionGananciaCent),
        ];
    }

    public static function validarSubtotal(Collection $lineas, string|int|float $subtotal, array $tasas): bool
    {
        $sumaReal = DocumentoFiscalCalculator::aCentimos(DocumentoFiscalCalculator::calcular($lineas->all(), $tasas)['subtotal']);

        return abs(DocumentoFiscalCalculator::aCentimos($subtotal) - $sumaReal) <= 1;
    }
}
