<?php

namespace App\Models;

use App\Models\Concerns\PersistsAuditably;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuiaRemision extends Model
{
    use PersistsAuditably;

    public $timestamps = false;

    protected $table = 'guias_remision';

    protected $fillable = [
        'venta_id',
        'orden_compra_id',
        'usuario_id',
        'serie',
        'numero',
        'fecha_traslado',
        'motivo_traslado',
        'punto_partida',
        'punto_llegada',
        'transportista',
        'placa_vehiculo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_traslado' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function getCodigoFormateadoAttribute(): string
    {
        $serie = $this->serie ?: 'T001';

        return $serie.'-'.str_pad((int) $this->numero, 6, '0', STR_PAD_LEFT);
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function ordenCompra(): BelongsTo
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_compra_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(GuiaRemisionDetalle::class, 'guia_remision_id');
    }
}
