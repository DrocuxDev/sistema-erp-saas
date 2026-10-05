<?php

namespace App\Models;

use App\Models\Concerns\PersistsAuditably;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenCompra extends Model
{
    use PersistsAuditably;

    public $timestamps = false;

    protected $table = 'ordenes_compra';

    protected $fillable = [
        'proveedor_id',
        'usuario_id',
        'serie',
        'correlativo',
        'codigo',
        'fecha',
        'subtotal',
        'igv',
        'total',
        'estado',
        'observaciones',
        'forma_pago',
        'plazo_entrega',
        'lugar_entrega',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'correlativo' => 'integer',
            'subtotal' => 'decimal:2',
            'igv' => 'decimal:2',
            'total' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function getCodigoFormateadoAttribute(): string
    {
        if (! empty($this->codigo)) {
            return $this->codigo;
        }
        $serie = $this->serie ?: 'OC01';

        return $serie.'-'.str_pad($this->correlativo ?: 1, 6, '0', STR_PAD_LEFT);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(OrdenCompraDetalle::class, 'orden_compra_id');
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'orden_compra_id');
    }

    public function guiasRemision(): HasMany
    {
        return $this->hasMany(GuiaRemision::class, 'orden_compra_id');
    }
}
