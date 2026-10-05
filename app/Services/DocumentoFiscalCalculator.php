<?php

namespace App\Services;

use App\Models\Cliente;
use Illuminate\Validation\ValidationException;

class DocumentoFiscalCalculator
{
    /** Los importes se convierten a céntimos antes de operar. */
    public static function calcular(array $lineas, array $perfil, string $campoPrecio = 'precio_venta_unit'): array
    {
        $importe = 0;
        foreach ($lineas as $linea) {
            $importe += self::importeLineaCentimos($linea['cantidad'], $linea[$campoPrecio]);
        }

        $igvBps = self::centesimos((string) $perfil['pct_igv']);
        $retencionBps = self::centesimos((string) $perfil['pct_retencion']);

        if ($perfil['precio_incluye_igv']) {
            $totalCent = $importe;
            $denominador = 10000 + $igvBps;
            $subtotalCent = intdiv($totalCent, $denominador) * 10000
                + intdiv(($totalCent % $denominador) * 10000 + intdiv($denominador, 2), $denominador);
            $igvCent = $totalCent - $subtotalCent;
        } else {
            $subtotalCent = $importe;
            $igvCent = self::porcentaje($subtotalCent, $igvBps);
            $totalCent = $subtotalCent + $igvCent;
        }

        $retencionCent = self::porcentaje($subtotalCent, $retencionBps);

        return [
            'subtotal' => self::deCentimos($subtotalCent),
            'igv' => self::deCentimos($igvCent),
            'total' => self::deCentimos($totalCent),
            'retencion' => self::deCentimos($retencionCent),
        ];
    }

    /** Usa el perfil del cliente o del documento de origen como base inmutable. */
    public static function perfil(Cliente $cliente, array $entrada, ?array $origen = null): array
    {
        $base = $origen ?? [
            'precio_incluye_igv' => (bool) $cliente->precio_incluye_igv,
            'pct_igv' => (string) $cliente->pct_igv,
            'pct_retencion' => (string) $cliente->pct_retencion,
        ];

        $perfil = [
            'precio_incluye_igv' => isset($entrada['precio_incluye_igv']) && $entrada['precio_incluye_igv'] !== ''
                ? (bool) $entrada['precio_incluye_igv'] : (bool) $base['precio_incluye_igv'],
            'pct_igv' => $entrada['pct_igv'] ?? $base['pct_igv'],
            'pct_retencion' => $entrada['pct_retencion'] ?? $base['pct_retencion'],
        ];

        $cambia = $perfil['precio_incluye_igv'] !== (bool) $base['precio_incluye_igv']
            || self::centesimos((string) $perfil['pct_igv']) !== self::centesimos((string) $base['pct_igv'])
            || self::centesimos((string) $perfil['pct_retencion']) !== self::centesimos((string) $base['pct_retencion']);

        $motivo = trim((string) ($entrada['motivo_excepcion_tributaria'] ?? ''));
        if ($cambia && $motivo === '') {
            throw ValidationException::withMessages([
                'motivo_excepcion_tributaria' => 'Indique el motivo para modificar las condiciones tributarias del documento.',
            ]);
        }

        $perfil['motivo_excepcion_tributaria'] = $cambia ? $motivo : null;

        return $perfil;
    }

    public static function importeLineaCentimos(string|int|float $cantidad, string|int|float $precioUnitario): int
    {
        return intdiv(self::centesimos((string) $cantidad) * self::centesimos((string) $precioUnitario) + 50, 100);
    }

    public static function aCentimos(string|int|float $decimal): int
    {
        return self::centesimos((string) $decimal);
    }

    public static function deCentimos(int $centimos): string
    {
        return intdiv($centimos, 100).'.'.str_pad((string) ($centimos % 100), 2, '0', STR_PAD_LEFT);
    }

    private static function centesimos(string $decimal): int
    {
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $decimal)) {
            throw ValidationException::withMessages(['lineas' => 'Los importes y porcentajes admiten hasta dos decimales.']);
        }
        [$entero, $fraccion] = array_pad(explode('.', $decimal, 2), 2, '');

        return ((int) $entero * 100) + (int) str_pad($fraccion, 2, '0');
    }

    public static function porcentaje(int $montoCent, int $bps): int
    {
        return intdiv($montoCent, 10000) * $bps
            + intdiv(($montoCent % 10000) * $bps + 5000, 10000);
    }
}
