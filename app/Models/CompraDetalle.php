<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompraDetalle extends Model
{
    public $timestamps = false;

    protected $table = 'compra_detalle';

    protected $fillable = [
        'compra_id',
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
        ];
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
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
