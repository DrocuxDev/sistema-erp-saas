<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerieDocumento extends Model
{
    public $timestamps = false;

    protected $table = 'series_documentos';

    protected $fillable = [
        'tipo_documento',
        'serie',
        'correlativo_actual',
    ];

    protected function casts(): array
    {
        return [
            'correlativo_actual' => 'integer',
        ];
    }
}
