<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VentaDetalle extends Model
{
    public $timestamps = false;

    protected $table = 'venta_detalle';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'precio_venta_unit',
        'precio_compra_unit',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_venta_unit' => 'decimal:2',
            'precio_compra_unit' => 'decimal:2',
            'ganancia_linea' => 'decimal:2',
        ];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function devoluciones(): HasMany
    {
        return $this->hasMany(DevolucionVentaDetalle::class, 'venta_detalle_id');
    }
}
