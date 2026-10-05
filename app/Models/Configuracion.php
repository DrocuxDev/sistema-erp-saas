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
        'razon_social',
        'ruc',
        'direccion',
        'telefono',
        'email',
        'sitio_web',
        'cuentas_bancarias',
        'plazo_entrega_default',
        'forma_pago_default',
        'validez_oferta_default',
        'logistica_default',
        'portada_imagen_path',
        'incluir_portada',
        'logo_path',
        'color_principal',
        'color_tabla_encabezado',
        'texto_saludo',
        'condiciones_adicionales_default',
        'texto_firma',
        'titular_cuenta',
        'pdf_template_cotizacion',
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
            'incluir_portada' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function cotizacionResumenes(): HasMany
    {
        return $this->hasMany(CotizacionResumen::class, 'configuracion_id');
    }
}
