<?php

namespace App\Models;

use App\Models\Concerns\PersistsAuditably;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CobroVenta extends Model
{
    use PersistsAuditably;

    public $timestamps = false;

    protected $table = 'cobros_venta';

    protected $fillable = [
        'venta_id', 'usuario_id', 'idempotency_key', 'tipo', 'fecha',
        'monto', 'medio', 'referencia', 'observacion',
        'estado', 'motivo_anulacion', 'anulado_por', 'anulado_en',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'date', 'monto' => 'decimal:2', 'created_at' => 'datetime', 'anulado_en' => 'datetime'];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function anulador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }
}
