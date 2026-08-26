<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'rol',
        'rol_id',
        'punto_emision',
        'is_superadmin',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_superadmin' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function rolPersonalizado(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function modulosExcepciones(): BelongsToMany
    {
        return $this->belongsToMany(Modulo::class, 'user_modulo')
            ->withPivot('permitido')
            ->withTimestamps();
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class, 'usuario_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'usuario_id');
    }

    public function isAdministrador(): bool
    {
        return $this->is_superadmin || $this->rol === 'administrador' || ($this->rolPersonalizado?->slug === 'superadmin');
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_superadmin;
    }

    /**
     * Verifica si el usuario actual tiene acceso a un módulo específico por su código.
     */
    public function tieneModulo(string $codigoModulo): bool
    {
        // 1. SuperAdmin tiene acceso irrestricto a todo
        if ($this->isSuperAdmin()) {
            return true;
        }

        // 2. Si el usuario está inactivo, no tiene acceso
        if (! $this->activo) {
            return false;
        }

        // 3. Revisar excepción individual de usuario (override)
        $excepcion = $this->modulosExcepciones()->where('codigo', $codigoModulo)->first();
        if ($excepcion) {
            return (bool) $excepcion->pivot->permitido;
        }

        // 4. Si tiene Rol Personalizado asignado, se rige estrictamente por los módulos de dicho rol
        if ($this->rolPersonalizado) {
            return $this->rolPersonalizado->modulos()->where('codigo', $codigoModulo)->exists();
        }

        // 5. Fallback retrocompatible con el rol binario clásico (cuando no tiene rol_id)
        if ($this->rol === 'administrador') {
            return true;
        }

        // Si es trabajador tradicional sin rol_id asignado, se permiten módulos operativos básicos
        $modulosBasicosTrabajador = [
            'dashboard',
            'proveedores',
            'clientes',
            'productos',
            'inventario',
            'ordenes_compra_cliente',
            'cotizaciones',
            'ventas',
        ];

        return in_array($codigoModulo, $modulosBasicosTrabajador, true);
    }

    /**
     * Verifica si el usuario autenticado tiene jerarquía para editar/eliminar a otro usuario.
     */
    public function puedeGestionarA(User $otroUsuario): bool
    {
        // Nadie (ni siquiera otro admin) puede borrar ni degradar a un SuperAdmin original
        if ($otroUsuario->isSuperAdmin() && ! $this->isSuperAdmin()) {
            return false;
        }

        // Un usuario no puede eliminarse a sí mismo
        if ($this->id === $otroUsuario->id) {
            return false;
        }

        // Un SuperAdmin puede gestionar a cualquiera excepto eliminarse a sí mismo
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Un Administrador normal solo puede gestionar a trabajadores o administradores que no sean SuperAdmin
        return $this->isAdministrador() && ! $otroUsuario->isSuperAdmin();
    }
}
