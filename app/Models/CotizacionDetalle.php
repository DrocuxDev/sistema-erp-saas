<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CotizacionDetalle extends Model
{
    public $timestamps = false;

    protected $table = 'cotizacion_detalle';

    protected $fillable = [
        'cotizacion_id',
        'producto_id',
        'proveedor_id',
        'descripcion',
        'unidad_medida',
        'cantidad',
        'precio_venta_unit',
        'precio_compra_unit',
        'igv_compra',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_venta_unit' => 'decimal:2',
            'precio_compra_unit' => 'decimal:2',
            'igv_compra' => 'decimal:2',
            'precio_venta_total' => 'decimal:2',
            'precio_compra_total' => 'decimal:2',
            'ganancia_linea' => 'decimal:2',
        ];
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}
