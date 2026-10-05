<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevolucionCompraDetalle extends Model
{
    public $timestamps = false;

    protected $table = 'devolucion_compra_detalle';

    protected $fillable = ['devolucion_compra_id', 'compra_detalle_id', 'cantidad', 'precio_compra_unit'];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_compra_unit' => 'decimal:2',
        ];
    }

    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(DevolucionCompra::class, 'devolucion_compra_id');
    }

    public function compraDetalle(): BelongsTo
    {
        return $this->belongsTo(CompraDetalle::class, 'compra_detalle_id');
    }
}
