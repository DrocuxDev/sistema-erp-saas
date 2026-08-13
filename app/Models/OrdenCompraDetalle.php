<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenCompraDetalle extends Model
{
    public $timestamps = false;

    protected $table = 'orden_compra_detalle';

    protected $fillable = [
        'orden_compra_id',
        'producto_id',
        'presentacion_compra_id',
        'cantidad_presentacion',
        'cantidad',
        'precio_compra_unit',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_presentacion' => 'decimal:2',
            'cantidad' => 'decimal:2',
            'precio_compra_unit' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function ordenCompra(): BelongsTo
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_compra_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function presentacionCompra(): BelongsTo
    {
        return $this->belongsTo(ProductoPresentacionCompra::class, 'presentacion_compra_id');
    }
}
