<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Cotizacion;
use App\Models\CotizacionDetalle;
use App\Models\CotizacionResumen;
use App\Models\CuentaBancaria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Configuración de documentos para la cotización en PDF.
 *
 * Todo el contenido del PDF sale de la base de datos (tabla
 * `configuracion` + `cuentas_bancarias`). Cada cotización guarda una
 * copia versionada (`cotizaciones.documento_snapshot`) al emitirse, de
 * modo que reimprimir un documento viejo no cambia aunque la
 * configuración actual cambie después.
 */
class CotizacionDocumento
{
    public const COLOR_TURQUESA = '#2EC4C6';

    public const SALUDO_DEFAULT = 'Reciban un saludo cordial por parte de {empresa}, nos dirigimos hacia usted a fin de presentarle la cotización solicitada. Estaremos a su disposición por cualquier duda o consulta.';

    public const FIRMA_DEFAULT = "Atentamente,\nEjecutivo de Ventas,\n{empresa}";

    /**
     * Foto instantánea de la configuración actual para guardar en la cotización.
     */
    public static function snapshot(Configuracion $config): array
    {
        return [
            'empresa' => [
                'nombre' => $config->nombre,
                'razon_social' => $config->razon_social,
                'ruc' => $config->ruc,
                'direccion' => $config->direccion,
                'telefono' => $config->telefono,
                'email' => $config->email,
                'sitio_web' => $config->sitio_web,
            ],
            'logo_path' => $config->logo_path,
            'portada_imagen_path' => $config->portada_imagen_path,
            'incluir_portada' => (bool) $config->incluir_portada,
            'color_principal' => $config->color_principal ?: '#0076A8',
            'color_tabla_encabezado' => $config->color_tabla_encabezado ?: '#111111',
            'color_turquesa' => self::COLOR_TURQUESA,
            'texto_saludo' => $config->texto_saludo ?: self::SALUDO_DEFAULT,
            'texto_firma' => $config->texto_firma ?: self::FIRMA_DEFAULT,
            'titular_cuenta' => $config->titular_cuenta,
            'cuentas' => CuentaBancaria::query()
                ->where('mostrar_en_pdf', true)
                ->orderBy('orden')
                ->orderBy('id')
                ->get(['banco', 'moneda', 'numero_cuenta', 'cci'])
                ->map(fn ($c) => [
                    'banco' => $c->banco,
                    'moneda' => $c->moneda,
                    'numero_cuenta' => $c->numero_cuenta,
                    'cci' => $c->cci,
                ])->all(),
            // Texto libre legacy (se mantiene como respaldo si no hay cuentas estructuradas).
            'cuentas_bancarias_texto' => $config->cuentas_bancarias,
        ];
    }

    /**
     * Configuración efectiva para imprimir: la copia guardada en la
     * cotización, o la actual si el documento es anterior a esta función.
     */
    public static function resolver(Cotizacion $cotizacion): array
    {
        $snap = $cotizacion->documento_snapshot;
        if (is_array($snap) && $snap !== []) {
            $snap['color_turquesa'] ??= self::COLOR_TURQUESA;

            return $snap;
        }

        $config = Configuracion::query()->where('activo', true)->first()
            ?? Configuracion::query()->first();

        if (! $config) {
            return self::snapshot(new Configuracion);
        }

        return self::snapshot($config);
    }

    /**
     * Reemplaza las variables {empresa} y {cliente} de los textos configurados.
     */
    public static function variables(?string $texto, Cotizacion $cotizacion, array $doc): string
    {
        $empresa = $doc['empresa']['razon_social'] ?? $doc['empresa']['nombre'] ?? '';
        $cliente = $cotizacion->cliente->razon_social ?? $cotizacion->cliente->nombre ?? '';

        return str_replace(['{empresa}', '{cliente}'], [$empresa, $cliente], (string) $texto);
    }

    /**
     * Imagen del disco público como data URI para incrustar en el PDF (DomPDF).
     * En base de datos solo se guarda la ruta.
     */
    public static function imagenBase64(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        return 'data:'.$mime.';base64,'.base64_encode($disk->get($path));
    }

    /** Conserva los archivos que todavía forman parte de documentos emitidos. */
    public static function eliminarImagenSinReferencias(?string $path): void
    {
        if (! $path) {
            return;
        }

        $enUso = Cotizacion::query()
            ->where('documento_snapshot->logo_path', $path)
            ->orWhere('documento_snapshot->portada_imagen_path', $path)
            ->exists();

        if (! $enUso) {
            Storage::disk('public')->delete($path);
        }
    }

    private static function hexRgb(string $hex, array $defecto): array
    {
        if (preg_match('/^#([0-9A-Fa-f]{6})$/', trim($hex), $m)) {
            return [hexdec(substr($m[1], 0, 2)), hexdec(substr($m[1], 2, 2)), hexdec(substr($m[1], 4, 2))];
        }

        return $defecto;
    }

    /**
     * Franja superior como PNG embebido (DomPDF no dibuja degradados CSS):
     * degradado del color principal al turquesa con corte diagonal en el
     * extremo izquierdo.
     */
    public static function franjaBase64(string $principal, string $turquesa): string
    {
        $w = 1600;
        $h = 64;
        [$r1, $g1, $b1] = self::hexRgb($principal, [0, 118, 168]);
        [$r2, $g2, $b2] = self::hexRgb($turquesa, [46, 196, 198]);

        $img = imagecreatetruecolor($w, $h);
        for ($x = 0; $x < $w; $x++) {
            $t = $x / ($w - 1);
            $c = imagecolorallocate($img,
                (int) round($r1 + ($r2 - $r1) * $t),
                (int) round($g1 + ($g2 - $g1) * $t),
                (int) round($b1 + ($b2 - $b1) * $t));
            imageline($img, $x, 0, $x, $h - 1, $c);
        }
        // Corte diagonal izquierdo: triángulo blanco superior-izquierdo.
        $blanco = imagecolorallocate($img, 255, 255, 255);
        imagefilledpolygon($img, [0, 0, 110, 0, 0, $h], $blanco);

        ob_start();
        imagepng($img);
        $png = (string) ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,'.base64_encode($png);
    }

    /**
     * Variables que la plantilla `cotizaciones.pdf` necesita.
     * El controlador solo pasa este arreglo a la vista: la plantilla no
     * consulta la base de datos (todo viene del snapshot o del documento).
     */
    public static function datosPdf(Cotizacion $cotizacion, ?array $doc = null): array
    {
        $doc ??= self::resolver($cotizacion);

        $resumen = $cotizacion->resumen;
        $subtotal = (float) ($resumen->subtotal ?? 0);
        $pctIgv = (float) ($resumen->pct_igv ?? 18);
        $igv = (float) ($resumen->igv ?? $subtotal * ($pctIgv / 100));
        $retencion = (float) ($resumen->retencion ?? 0);
        // Igual que la columna generada MySQL: (subtotal + igv) - retencion.
        $total = (float) ($resumen->total_facturar ?? ($subtotal + $igv - $retencion));

        $portadaBase64 = self::imagenBase64($doc['portada_imagen_path'] ?? null);
        // Serie + correlativo de 8 dígitos, siempre (C001-00000001).
        $codigo = ($cotizacion->serie ?: 'C001').'-'.str_pad((int) ($cotizacion->correlativo ?? 0), 8, '0', STR_PAD_LEFT);

        return [
            'cotizacion' => $cotizacion,
            'doc' => $doc,
            'logoBase64' => self::imagenBase64($doc['logo_path'] ?? null),
            'franjaBase64' => self::franjaBase64($doc['color_principal'] ?? '#0076A8', $doc['color_turquesa'] ?? self::COLOR_TURQUESA),
            'portadaBase64' => $portadaBase64,
            'conPortada' => (bool) ($doc['incluir_portada'] ?? false) && $portadaBase64 !== null,
            'codigoCompleto' => $codigo,
            'subtotal' => $subtotal,
            'pctIgv' => $pctIgv,
            'igv' => $igv,
            'retencion' => $retencion,
            'total' => $total,
            'montoEnLetras' => \App\Support\NumeroALetras::convertir($total, 'Soles'),
            'saludo' => self::variables($doc['texto_saludo'] ?? self::SALUDO_DEFAULT, $cotizacion, $doc),
            'firma' => self::variables($doc['texto_firma'] ?? self::FIRMA_DEFAULT, $cotizacion, $doc),
        ];
    }

    /**
     * Cotización ficticia (no persistida) para el "PDF de ejemplo" de la
     * pantalla de configuración: usa la configuración actual.
     */
    public static function ejemplo(): Cotizacion
    {
        $cotizacion = new Cotizacion([
            'serie' => 'C001',
            'correlativo' => 1,
            'codigo' => 'C001-00000001',
            'version' => 1,
            'tipo_cotizacion' => 'producto',
            'asunto' => 'EJEMPLO DE COTIZACIÓN — VISTA PREVIA',
            'representante' => 'Área de compras',
            'fecha' => now()->toDateString(),
            'estado' => 'pendiente',
            'observaciones' => 'Documento de ejemplo generado desde Configuración de documentos.',
            'plazo_entrega' => '2 a 5 días hábiles tras recepción de O/C',
            'forma_pago' => '30 días calendario previa conformidad y factura',
            'logistica' => 'Entrega en instalaciones del cliente / Lima Metropolitana',
            'validez_oferta' => '15 días calendario',
            'condiciones_adicionales' => 'Stock sujeto a disponibilidad. Garantía de 12 meses por defectos de fabricación.',
        ]);
        $cotizacion->id = 0;

        $cotizacion->setRelation('cliente', new Cliente([
            'nombre' => 'Empresa Ejemplo S.A.C.',
            'razon_social' => 'EMPRESA EJEMPLO S.A.C.',
            'ruc' => '20123456789',
            'direccion' => 'Av. Ejemplo N° 123, Lima',
            'contacto' => 'Responsable de compras',
            'telefono' => '(01) 123-4567',
            'email' => 'compras@ejemplo.com',
        ]));
        $cotizacion->setRelation('usuario', new User(['name' => 'Ejecutivo de Ventas']));

        $items = [
            ['sku' => 'EJM-001', 'nombre' => 'Cámara PTZ de ejemplo 20X', 'detalle' => 'Zoom óptico 20X, resolución Full HD, uso interior.', 'cant' => 2, 'um' => 'UND', 'pu' => 2600.00],
            ['sku' => 'EJM-002', 'nombre' => 'Soporte de techo para cámara', 'detalle' => 'Brazo metálico con accesorios de instalación.', 'cant' => 2, 'um' => 'UND', 'pu' => 180.00],
        ];

        $detalles = [];
        foreach ($items as $it) {
            $detalle = new CotizacionDetalle([
                'descripcion' => $it['detalle'],
                'unidad_medida' => $it['um'],
                'cantidad' => $it['cant'],
                'precio_venta_unit' => $it['pu'],
                'precio_compra_unit' => $it['pu'] * 0.7,
                'igv_compra' => 0,
            ]);
            $detalle->setRelation('producto', new Producto([
                'sku' => $it['sku'],
                'nombre' => $it['nombre'],
                'unidad_medida' => $it['um'],
            ]));
            // Totales de línea calculados (las columnas generadas solo existen en MySQL).
            $detalle->setAttribute('precio_venta_total', $it['cant'] * $it['pu']);
            $detalles[] = $detalle;
        }
        $cotizacion->setRelation('detalles', collect($detalles));

        $subtotal = 2 * 2600.00 + 2 * 180.00; // 5560.00
        $igv = round($subtotal * 0.18, 2);    // 1000.80
        $resumen = new CotizacionResumen([
            'pct_igv' => 18,
            'pct_retencion' => 0,
            'subtotal' => $subtotal,
            'igv' => $igv,
            'retencion' => 0,
        ]);
        $resumen->setAttribute('total_facturar', $subtotal + $igv);
        $cotizacion->setRelation('resumen', $resumen);

        return $cotizacion;
    }
}
