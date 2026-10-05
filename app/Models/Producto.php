<?php

namespace App\Models;

use App\Models\Concerns\PersistsAuditably;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use PersistsAuditably;

    protected $table = 'productos';

    public $timestamps = false;

    protected $fillable = [
        'sku',
        'nombre',
        'imagen',
        'descripcion',
        'unidad_medida',
        'categoria',
        'tipo_producto',
        'tipo_existencia',
        'precio_compra',
        'precio_venta',
        'marca',
        'modelo',
        'stock_actual',
        'stock_minimo',
        'stock_maximo',
        'peso_bruto',
        'seguimiento',
        'condicion',
        'envase',
        'tipo_area_produccion',
        'se_fabrica',
        'puede_venderse',
        'puede_comprarse',
    ];

    protected function casts(): array
    {
        return [
            'precio_compra' => 'decimal:2',
            'precio_venta' => 'decimal:2',
            'stock_actual' => 'decimal:2',
            'stock_minimo' => 'decimal:2',
            'stock_maximo' => 'decimal:2',
            'peso_bruto' => 'decimal:3',
            'se_fabrica' => 'boolean',
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

    public function presentacionesVenta(): HasMany
    {
        return $this->hasMany(ProductoPresentacionVenta::class, 'producto_id');
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }
}
