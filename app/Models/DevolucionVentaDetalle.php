<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevolucionVentaDetalle extends Model
{
    public $timestamps = false;

    protected $table = 'devolucion_venta_detalle';

    protected $fillable = ['devolucion_venta_id', 'venta_detalle_id', 'cantidad', 'precio_venta_unit'];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_venta_unit' => 'decimal:2',
        ];
    }

    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(DevolucionVenta::class, 'devolucion_venta_id');
    }

    public function ventaDetalle(): BelongsTo
    {
        return $this->belongsTo(VentaDetalle::class, 'venta_detalle_id');
    }
}
