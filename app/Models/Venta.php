<?php

namespace App\Models;

use App\Models\Concerns\PersistsAuditably;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    use PersistsAuditably;

    public $timestamps = false;

    protected $fillable = [
        'cotizacion_id',
        'cliente_id',
        'usuario_id',
        'orden_compra_cliente_id',
        'serie',
        'correlativo',
        'codigo',
        'fecha',
        'subtotal',
        'igv',
        'total',
        'precio_incluye_igv',
        'pct_igv',
        'pct_retencion',
        'retencion',
        'motivo_excepcion_tributaria',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'correlativo' => 'integer',
            'subtotal' => 'decimal:2',
            'igv' => 'decimal:2',
            'total' => 'decimal:2',
            'precio_incluye_igv' => 'boolean',
            'pct_igv' => 'decimal:2',
            'pct_retencion' => 'decimal:2',
            'retencion' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function getCodigoFormateadoAttribute(): string
    {
        if (! empty($this->codigo)) {
            return $this->codigo;
        }
        $serie = $this->serie ?: 'V001';

        return $serie.'-'.str_pad($this->correlativo ?: 1, 6, '0', STR_PAD_LEFT);
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function ordenCompraCliente(): BelongsTo
    {
        return $this->belongsTo(OrdenCompraCliente::class, 'orden_compra_cliente_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class, 'venta_id');
    }

    public function comprobantes(): HasMany
    {
        return $this->hasMany(ComprobanteVenta::class, 'venta_id');
    }

    public function guiasRemision(): HasMany
    {
        return $this->hasMany(GuiaRemision::class, 'venta_id');
    }

    public function devoluciones(): HasMany
    {
        return $this->hasMany(DevolucionVenta::class, 'venta_id');
    }

    public function cobros(): HasMany
    {
        return $this->hasMany(CobroVenta::class, 'venta_id');
    }
}
