<?php

namespace App\Models;

use App\Models\Concerns\PersistsAuditably;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use PersistsAuditably;

    protected $table = 'proveedores';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'imagen',
        'razon_social',
        'ruc',
        'direccion',
        'contacto',
        'telefono',
        'email',
        'condicion_pago',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function productoProveedores(): HasMany
    {
        return $this->hasMany(ProductoProveedor::class, 'proveedor_id');
    }

    public function cotizacionDetalles(): HasMany
    {
        return $this->hasMany(CotizacionDetalle::class, 'proveedor_id');
    }

    public function ordenesCompra(): HasMany
    {
        return $this->hasMany(OrdenCompra::class, 'proveedor_id');
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'proveedor_id');
    }
}
