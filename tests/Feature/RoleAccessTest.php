<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ConfiguracionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ConfiguracionSeeder::class);
    }

    public function test_administrador_puede_acceder_a_configuracion_y_auditoria(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);

        $this->actingAs($admin)
            ->get(route('configuracion.edit'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('auditoria.index'))
            ->assertOk();
    }

    public function test_trabajador_no_puede_acceder_a_rutas_de_administrador(): void
    {
        $trabajador = User::factory()->create(['rol' => 'trabajador']);

        $this->actingAs($trabajador)
            ->get(route('configuracion.edit'))
            ->assertForbidden();

        $this->actingAs($trabajador)
            ->get(route('auditoria.index'))
            ->assertForbidden();
    }

    public function test_trabajador_puede_acceder_al_dashboard(): void
    {
        $trabajador = User::factory()->create(['rol' => 'trabajador']);

        $this->actingAs($trabajador)
            ->get(route('dashboard'))
            ->assertOk();
    }
}
