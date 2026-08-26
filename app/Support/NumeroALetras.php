<?php

namespace App\Support;

class NumeroALetras
{
    private static array $unidades = [
        '', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
        'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE', 'VEINTE'
    ];

    private static array $decenas = [
        '', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'
    ];

    private static array $centenas = [
        '', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'
    ];

    public static function convertir(float $monto, string $moneda = 'Soles'): string
    {
        $partes = explode('.', number_format($monto, 2, '.', ''));
        $entero = (int) $partes[0];
        $decimal = $partes[1] ?? '00';

        if ($entero === 0) {
            $texto = 'CERO';
        } else {
            $texto = self::seccion($entero);
        }

        $texto = trim(preg_replace('/\s+/', ' ', $texto));

        return sprintf('%s con %s/100 %s', ucfirst(strtolower($texto)), $decimal, $moneda);
    }

    private static function seccion(int $numero): string
    {
        if ($numero === 0) return '';
        if ($numero === 100) return 'CIEN';

        if ($numero < 1000) {
            return self::centenas($numero);
        }

        if ($numero < 1000000) {
            $miles = (int) ($numero / 1000);
            $resto = $numero % 1000;
            $txtMiles = ($miles === 1) ? 'MIL' : self::seccion($miles) . ' MIL';
            return trim($txtMiles . ' ' . self::seccion($resto));
        }

        if ($numero < 1000000000) {
            $millones = (int) ($numero / 1000000);
            $resto = $numero % 1000000;
            $txtMillones = ($millones === 1) ? 'UN MILLÓN' : self::seccion($millones) . ' MILLONES';
            return trim($txtMillones . ' ' . self::seccion($resto));
        }

        return (string) $numero;
    }

    private static function centenas(int $numero): string
    {
        $c = (int) ($numero / 100);
        $resto = $numero % 100;

        $str = self::$centenas[$c] ?? '';

        if ($resto > 0) {
            $str .= ' ' . self::decenas($resto);
        }

        return trim($str);
    }

    private static function decenas(int $numero): string
    {
        if ($numero <= 20) {
            return self::$unidades[$numero];
        }

        if ($numero < 30) {
            return 'VEINTI' . self::$unidades[$numero - 20];
        }

        $d = (int) ($numero / 10);
        $u = $numero % 10;

        $str = self::$decenas[$d] ?? '';
        if ($u > 0) {
            $str .= ' Y ' . self::$unidades[$u];
        }

        return trim($str);
    }
}
