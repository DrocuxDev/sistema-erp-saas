<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoProveedor extends Model
{
    public $timestamps = false;

    protected $table = 'producto_proveedor';

    protected $fillable = [
        'producto_id',
        'proveedor_id',
        'precio_compra',
        'fecha_actualizacion',
    ];

    protected function casts(): array
    {
        return [
            'precio_compra' => 'decimal:2',
            'fecha_actualizacion' => 'date',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}
