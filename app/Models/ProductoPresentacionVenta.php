<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoPresentacionVenta extends Model
{
    public $timestamps = false;

    protected $table = 'producto_presentaciones_venta';

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
}
