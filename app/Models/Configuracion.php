<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Configuracion extends Model
{
    public $timestamps = false;

    protected $table = 'configuracion';

    protected $fillable = [
        'nombre',
        'pct_igv',
        'pct_retencion',
        'pct_comision_venta',
        'pct_comision_ganancia',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'pct_igv' => 'decimal:2',
            'pct_retencion' => 'decimal:2',
            'pct_comision_venta' => 'decimal:2',
            'pct_comision_ganancia' => 'decimal:2',
            'activo' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function cotizacionResumenes(): HasMany
    {
        return $this->hasMany(CotizacionResumen::class, 'configuracion_id');
    }
}
