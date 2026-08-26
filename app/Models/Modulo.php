<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;

    protected $table = 'modulos';

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria',
        'icono',
        'ruta_base',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_modulo')
            ->withPivot('puede_ver', 'puede_crear', 'puede_editar', 'puede_eliminar')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_modulo')
            ->withPivot('permitido')
            ->withTimestamps();
    }
}
