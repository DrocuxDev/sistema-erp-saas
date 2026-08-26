<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cotizacion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cliente_id',
        'usuario_id',
        'orden_compra_cliente_id',
        'serie',
        'correlativo',
        'codigo',
        'version',
        'tipo_cotizacion',
        'asunto',
        'representante',
        'fecha',
        'estado',
        'observaciones',
        'plazo_entrega',
        'forma_pago',
        'logistica',
        'validez_oferta',
    ];

    protected $table = 'cotizaciones';

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'created_at' => 'datetime',
            'correlativo' => 'integer',
            'version' => 'integer',
        ];
    }

    public function getCodigoFormateadoAttribute(): string
    {
        if (! empty($this->codigo)) {
            return $this->codigo;
        }

        $serie = $this->serie ?: 'C001';
        $correlativo = $this->correlativo ?? 0;

        return "{$serie}-{$correlativo}";
    }

    public function getVersionFormateadaAttribute(): string
    {
        return sprintf('%03d', (int) ($this->version ?: 1));
    }

    public function getTipoCotizacionLabelAttribute(): string
    {
        return match ($this->tipo_cotizacion) {
            'servicio' => 'Servicio TI',
            default => 'Producto / Equipamiento',
        };
    }
    
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function ordenCompraCliente(): BelongsTo
    {
        return $this->belongsTo(OrdenCompraCliente::class, 'orden_compra_cliente_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CotizacionDetalle::class, 'cotizacion_id');
    }

    public function resumen(): HasOne
    {
        return $this->hasOne(CotizacionResumen::class, 'cotizacion_id');
    }

    public function venta(): HasOne
    {
        return $this->hasOne(Venta::class, 'cotizacion_id');
    }
}

