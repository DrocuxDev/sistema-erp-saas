<?php

namespace App\Models;

use App\Models\Concerns\PersistsAuditably;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobanteVenta extends Model
{
    use PersistsAuditably;

    public $timestamps = false;

    protected $table = 'comprobantes_venta';

    protected $fillable = [
        'venta_id',
        'usuario_id',
        'tipo',
        'serie',
        'numero',
        'fecha_emision',
        'subtotal',
        'igv',
        'precio_incluye_igv',
        'pct_igv',
        'pct_retencion',
        'retencion',
        'motivo_excepcion_tributaria',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'subtotal' => 'decimal:2',
            'igv' => 'decimal:2',
            'precio_incluye_igv' => 'boolean',
            'pct_igv' => 'decimal:2',
            'pct_retencion' => 'decimal:2',
            'retencion' => 'decimal:2',
            'total' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function getTotalAttribute($value): float
    {
        // `total` es columna generada STORED solo en MySQL; fallback multi-driver (SQLite/testing).
        if ($value !== null) {
            return (float) $value;
        }

        return round((float) ($this->attributes['subtotal'] ?? 0) + (float) ($this->attributes['igv'] ?? 0), 2);
    }

    public function getCodigoFormateadoAttribute(): string
    {
        $serie = $this->serie ?: 'F001';

        return $serie.'-'.str_pad((int) $this->numero, 6, '0', STR_PAD_LEFT);
    }

    public function getTipoNombreAttribute(): string
    {
        return match ($this->tipo) {
            'factura' => 'Factura',
            'boleta' => 'Boleta',
            'nota_venta' => 'Nota de Venta',
            default => ucfirst((string) $this->tipo),
        };
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
