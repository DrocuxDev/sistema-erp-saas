<?php

namespace App\Models;

use App\Models\Concerns\PersistsAuditably;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoCompra extends Model
{
    use PersistsAuditably;

    public $timestamps = false;

    protected $table = 'pagos_compra';

    protected $fillable = [
        'compra_id', 'usuario_id', 'idempotency_key', 'tipo', 'fecha',
        'monto', 'medio', 'referencia', 'observacion',
        'estado', 'motivo_anulacion', 'anulado_por', 'anulado_en',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'date', 'monto' => 'decimal:2', 'created_at' => 'datetime', 'anulado_en' => 'datetime'];
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
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
