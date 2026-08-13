<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $table = 'productos';
    public $timestamps = false;

    protected $fillable = [
        'sku',
        'nombre',
        'descripcion',
        'unidad_medida',
        'categoria',
        'stock_actual',
        'stock_minimo',
        'puede_venderse',
        'puede_comprarse',
    ];

    protected function casts(): array
    {
        return [
            'stock_actual' => 'decimal:2',
            'stock_minimo' => 'decimal:2',
            'puede_venderse' => 'boolean',
            'puede_comprarse' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function productoProveedores(): HasMany
    {
        return $this->hasMany(ProductoProveedor::class, 'producto_id');
    }

    public function presentacionesCompra(): HasMany
    {
        return $this->hasMany(ProductoPresentacionCompra::class, 'producto_id');
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }
}
