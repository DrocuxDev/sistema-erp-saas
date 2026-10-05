<?php

namespace Tests\Feature;

use App\Models\Modulo;
use App\Models\Rol;
use App\Models\User;
use Database\Seeders\ConfiguracionSeeder;
use Database\Seeders\RolesAndModulosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ConfiguracionSeeder::class);
        $this->seed(RolesAndModulosSeeder::class);
    }

    public function test_superadmin_base_es_inmutable_y_no_puede_ser_eliminado_por_otro_admin(): void
    {
        $superAdmin = User::where('is_superadmin', true)->first();
        $otroAdmin = User::factory()->create([
            'rol' => 'administrador',
            'is_superadmin' => false,
            'activo' => true,
        ]);

        // Intentar eliminar al SuperAdmin desde la sesión de otro Admin
        $response = $this->actingAs($otroAdmin)->delete(route('users.destroy', $superAdmin));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error', 'Acceso denegado: El Administrador Principal (SuperAdmin) no puede ser eliminado.');

        // El SuperAdmin sigue existiendo
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    public function test_usuario_con_rol_sin_permiso_a_modulo_recibe_403(): void
    {
        // Rol limitado solo a ventas
        $rolVentas = Rol::where('slug', 'ventas')->first();

        $vendedor = User::factory()->create([
            'rol' => 'trabajador',
            'rol_id' => $rolVentas->id,
            'is_superadmin' => false,
            'activo' => true,
        ]);

        // Puede acceder a cotizaciones
        $responseCotizaciones = $this->actingAs($vendedor)->get(route('cotizaciones.index'));
        $responseCotizaciones->assertOk();

        // NO puede acceder a configuración (debe dar 403)
        $responseConfig = $this->actingAs($vendedor)->get(route('configuracion.edit'));
        $responseConfig->assertForbidden();

        // NO puede acceder a proveedores si su rol no lo tiene
        $responseProveedores = $this->actingAs($vendedor)->get(route('proveedores.index'));
        $responseProveedores->assertForbidden();
    }

    public function test_excepcion_individual_concede_acceso_a_modulo_especifico(): void
    {
        $rolVentas = Rol::where('slug', 'ventas')->first();
        $moduloProveedores = Modulo::where('codigo', 'proveedores')->first();

        $vendedor = User::factory()->create([
            'rol' => 'trabajador',
            'rol_id' => $rolVentas->id,
            'is_superadmin' => false,
            'activo' => true,
        ]);

        // Antes de la excepción: Forbidden
        $this->actingAs($vendedor)->get(route('proveedores.index'))->assertForbidden();

        // Se le asigna el módulo 'proveedores' como excepción individual
        $vendedor->modulosExcepciones()->attach($moduloProveedores->id, ['permitido' => true]);

        // Después de la excepción: Acceso concedido (200 OK)
        $this->actingAs($vendedor)->get(route('proveedores.index'))->assertOk();
    }

    public function test_usuario_inactivo_no_puede_acceder(): void
    {
        $rolVentas = Rol::where('slug', 'ventas')->first();
        $usuarioInactivo = User::factory()->create([
            'rol' => 'trabajador',
            'rol_id' => $rolVentas->id,
            'is_superadmin' => false,
            'activo' => false,
        ]);

        $response = $this->actingAs($usuarioInactivo)->get(route('cotizaciones.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_administrador_no_puede_autoasignarse_privilegios_ni_crear_superadmin(): void
    {
        $rolAdministracion = Rol::where('slug', 'administracion')->firstOrFail();
        $rolSuperadmin = Rol::where('slug', 'superadmin')->firstOrFail();
        $administrador = User::factory()->create([
            'rol' => 'administrador', 'rol_id' => $rolAdministracion->id,
            'is_superadmin' => false, 'activo' => true,
        ]);
        $datos = [
            'name' => 'Administración', 'email' => $administrador->email,
            'username' => $administrador->username, 'rol_id' => $rolSuperadmin->id,
            'punto_emision' => 'OFICINA', 'activo' => 1,
        ];
        $this->actingAs($administrador)->put(route('users.update', $administrador), $datos)->assertForbidden();
        $this->assertFalse($administrador->fresh()->isSuperAdmin());

        $this->actingAs($administrador)->post(route('users.store'), [
            ...$datos, 'email' => 'nuevo@ejemplo.test', 'username' => 'nuevo',
            'password' => 'secreto123', 'password_confirmation' => 'secreto123',
        ])->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'nuevo@ejemplo.test']);
    }
}
