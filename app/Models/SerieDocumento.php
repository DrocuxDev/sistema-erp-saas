<?php

namespace App\Models;

use App\Models\Concerns\PersistsAuditably;
use Illuminate\Database\Eloquent\Model;

class SerieDocumento extends Model
{
    use PersistsAuditably;

    protected $table = 'series_documentos';

    protected $fillable = [
        'tipo_documento',
        'serie',
        'descripcion',
        'correlativo_actual',
        'longitud_correlativo',
        'es_predeterminado',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'correlativo_actual' => 'integer',
            'longitud_correlativo' => 'integer',
            'es_predeterminado' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /**
     * Catálogo amigable de tipos de documento
     */
    public static function tiposDisponibles(): array
    {
        return [
            'cotizacion' => 'Cotización Comercial',
            'venta' => 'Venta / Nota de Pedido',
            'orden_compra' => 'Orden de Compra a Proveedor',
            'orden_compra_cliente' => 'Orden de Compra de Cliente',
            'factura' => 'Factura Electrónica',
            'boleta' => 'Boleta de Venta Electrónica',
            'nota_venta' => 'Nota de Venta',
            'guia_remision' => 'Guía de Remisión Remitente',
        ];
    }

    public function getTipoNombreAttribute(): string
    {
        $tipos = self::tiposDisponibles();

        return $tipos[$this->tipo_documento] ?? strtoupper(str_replace('_', ' ', $this->tipo_documento));
    }

    public function getProximoCodigoAttribute(): string
    {
        $proximo = $this->correlativo_actual + 1;
        $longitud = $this->longitud_correlativo ?: 6;

        return $this->serie.'-'.str_pad($proximo, $longitud, '0', STR_PAD_LEFT);
    }

    public function getCodigoActualAttribute(): string
    {
        $longitud = $this->longitud_correlativo ?: 6;

        return $this->serie.'-'.str_pad($this->correlativo_actual, $longitud, '0', STR_PAD_LEFT);
    }

    /**
     * Obtiene el siguiente número de correlativo de forma segura y transaccional con lockForUpdate.
     *
     * @return array{serie: string, correlativo: int, codigo: string, serie_id: int}
     */
    public static function siguienteCodigo(string $tipoDocumento, ?string $serie = null): array
    {
        $query = self::where('tipo_documento', $tipoDocumento)->where('activo', true);

        if (! empty($serie)) {
            $query->where('serie', $serie);
        } else {
            $query->orderByDesc('es_predeterminado');
        }

        /** @var self|null $serieDoc */
        $serieDoc = $query->lockForUpdate()->first();

        // Si no existe, crear una serie predeterminada de contingencia
        if (! $serieDoc) {
            $prefijo = match ($tipoDocumento) {
                'cotizacion' => 'C001',
                'venta' => 'V001',
                'orden_compra' => 'OC01',
                'orden_compra_cliente' => 'OCC01',
                'factura' => 'F001',
                'boleta' => 'B001',
                'nota_venta' => 'NV01',
                'guia_remision' => 'T001',
                default => 'DOC1',
            };

            $serieDoc = self::create([
                'tipo_documento' => $tipoDocumento,
                'serie' => $serie ?: $prefijo,
                'descripcion' => 'Serie autogenerada para '.$tipoDocumento,
                'correlativo_actual' => 0,
                'longitud_correlativo' => 6,
                'es_predeterminado' => true,
                'activo' => true,
            ]);
        }

        $serieDoc->correlativo_actual++;
        $serieDoc->save();

        $longitud = $serieDoc->longitud_correlativo ?: 6;
        $codigo = $serieDoc->serie.'-'.str_pad($serieDoc->correlativo_actual, $longitud, '0', STR_PAD_LEFT);

        return [
            'serie' => $serieDoc->serie,
            'correlativo' => $serieDoc->correlativo_actual,
            'codigo' => $codigo,
            'serie_id' => $serieDoc->id,
        ];
    }
}
