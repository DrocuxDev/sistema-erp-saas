<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuentaBancaria extends Model
{
    public $timestamps = false;

    protected $table = 'cuentas_bancarias';

    protected $fillable = [
        'banco',
        'moneda',
        'numero_cuenta',
        'cci',
        'mostrar_en_pdf',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'mostrar_en_pdf' => 'boolean',
            'orden' => 'integer',
        ];
    }
}
