<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CotizacionResumen extends Model
{
    public $timestamps = false;

    protected $table = 'cotizacion_resumen';

    protected $fillable = [
        'cotizacion_id',
        'configuracion_id',
        'subtotal',
        'igv',
        'retencion',
        'compra_activa',
        'compra_fija',
        'no_utilizable',
        'comision_venta',
        'comision_ganancia',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'igv' => 'decimal:2',
            'retencion' => 'decimal:2',
            'total_facturar' => 'decimal:2',
            'compra_activa' => 'decimal:2',
            'compra_fija' => 'decimal:2',
            'no_utilizable' => 'decimal:2',
            'ganancia' => 'decimal:2',
            'comision_venta' => 'decimal:2',
            'comision_ganancia' => 'decimal:2',
            'comision_total' => 'decimal:2',
            'saldo_a_favor' => 'decimal:2',
            'rentabilidad' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function configuracion(): BelongsTo
    {
        return $this->belongsTo(Configuracion::class, 'configuracion_id');
    }
}
