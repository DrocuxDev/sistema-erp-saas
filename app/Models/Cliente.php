<?php

namespace App\Models;

use App\Models\Concerns\PersistsAuditably;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use PersistsAuditably;

    protected $table = 'clientes';

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
        'precio_incluye_igv',
        'pct_igv',
        'pct_retencion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'precio_incluye_igv' => 'boolean',
            'pct_igv' => 'decimal:2',
            'pct_retencion' => 'decimal:2',
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
