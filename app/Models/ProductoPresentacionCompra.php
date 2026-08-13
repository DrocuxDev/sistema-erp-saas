<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductoPresentacionCompra extends Model
{
    public $timestamps = false;

    protected $table = 'producto_presentaciones_compra';

    protected $fillable = [
        'producto_id',
        'unidad_medida',
        'equivalencia',
        'preferida',
    ];

    protected function casts(): array
    {
        return [
            'equivalencia' => 'decimal:4',
            'preferida' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function ordenCompraDetalles(): HasMany
    {
        return $this->hasMany(OrdenCompraDetalle::class, 'presentacion_compra_id');
    }

    public function compraDetalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class, 'presentacion_compra_id');
    }
}
