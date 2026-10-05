<?php

namespace App\Models;

use App\Models\Concerns\PersistsAuditably;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DevolucionVenta extends Model
{
    use PersistsAuditably;

    public $timestamps = false;

    protected $table = 'devoluciones_venta';

    protected $fillable = [
        'venta_id', 'usuario_id', 'idempotency_key', 'fecha', 'motivo',
        'subtotal', 'igv', 'total', 'retencion',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'subtotal' => 'decimal:2',
            'igv' => 'decimal:2',
            'total' => 'decimal:2',
            'retencion' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DevolucionVentaDetalle::class, 'devolucion_venta_id');
    }
}
