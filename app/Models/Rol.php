<?php

namespace App\Models;

use App\Models\Concerns\PersistsAuditably;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;
    use PersistsAuditably;

    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'es_sistema',
    ];

    protected $casts = [
        'es_sistema' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'rol_id');
    }

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'rol_modulo')
            ->withPivot('puede_ver', 'puede_crear', 'puede_editar', 'puede_eliminar')
            ->withTimestamps();
    }
}
