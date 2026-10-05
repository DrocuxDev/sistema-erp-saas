<?php

namespace App\Models;

use App\Models\Concerns\PersistsAuditably;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DevolucionCompra extends Model
{
    use PersistsAuditably;

    public $timestamps = false;

    protected $table = 'devoluciones_compra';

    protected $fillable = [
        'compra_id', 'usuario_id', 'idempotency_key', 'fecha', 'motivo', 'total',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'total' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DevolucionCompraDetalle::class, 'devolucion_compra_id');
    }
}
