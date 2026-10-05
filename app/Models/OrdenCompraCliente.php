<?php

namespace App\Models;

use App\Models\Concerns\PersistsAuditably;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrdenCompraCliente extends Model
{
    use PersistsAuditably;

    public $timestamps = false;

    protected $table = 'ordenes_compra_cliente';

    protected $fillable = [
        'cliente_id',
        'cotizacion_id',
        'venta_id',
        'usuario_id',
        'numero_oc',
        'archivo',
        'fecha_recepcion',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_recepcion' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function cotizacionGenerada(): HasOne
    {
        return $this->hasOne(Cotizacion::class, 'orden_compra_cliente_id');
    }

    public function ventaGenerada(): HasOne
    {
        return $this->hasOne(Venta::class, 'orden_compra_cliente_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'orden_compra_cliente_id');
    }
}
