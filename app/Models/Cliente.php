<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $table = 'clientes';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
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

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class, 'cliente_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }

    public function ordenesCompraCliente(): HasMany
    {
        return $this->hasMany(OrdenCompraCliente::class, 'cliente_id');
    }
}
