<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_overview_requires_authentication(): void
    {
        $this->get('/mi-perfil')->assertRedirect('/login');
    }

    public function test_profile_overview_shows_only_the_authenticated_users_activity(): void
    {
        $user = User::factory()->create([
            'name' => 'Ana María Ruiz',
            'username' => 'ana.ruiz',
            'is_superadmin' => true,
        ]);
        $other = User::factory()->create();
        $cliente = Cliente::create(['nombre' => 'Cliente de prueba', 'ruc' => '20123456789']);

        Cotizacion::create([
            'cliente_id' => $cliente->id, 'usuario_id' => $user->id,
            'codigo' => 'C001-101', 'serie' => 'C001', 'correlativo' => 101,
            'asunto' => 'Propuesta propia', 'fecha' => now()->toDateString(),
        ]);
        Cotizacion::create([
            'cliente_id' => $cliente->id, 'usuario_id' => $other->id,
            'codigo' => 'C001-102', 'serie' => 'C001', 'correlativo' => 102,
            'asunto' => 'Propuesta ajena', 'fecha' => now()->toDateString(),
        ]);
        Venta::create([
            'cliente_id' => $cliente->id, 'usuario_id' => $user->id,
            'codigo' => 'V001-101', 'serie' => 'V001', 'correlativo' => 101,
            'fecha' => now()->toDateString(), 'total' => 100,
        ]);
        Venta::create([
            'cliente_id' => $cliente->id, 'usuario_id' => $other->id,
            'codigo' => 'V001-102', 'serie' => 'V001', 'correlativo' => 102,
            'fecha' => now()->toDateString(), 'total' => 200,
        ]);

        $this->actingAs($user)->get('/mi-perfil')
            ->assertOk()
            ->assertSee('Ana María Ruiz')
            ->assertSee('@ana.ruiz')
            ->assertSee('Propuesta propia')
            ->assertDontSee('Propuesta ajena');

        $this->actingAs($user)->get('/mi-perfil?seccion=ventas')
            ->assertOk()
            ->assertSee('V001-101')
            ->assertDontSee('V001-102');
    }

    public function test_profile_overview_hides_commercial_activity_without_module_access(): void
    {
        $user = User::factory()->create(['activo' => false]);

        $this->actingAs($user)->get('/mi-perfil?seccion=ventas')
            ->assertOk()
            ->assertSee('No tienes acceso a módulos de actividad comercial.')
            ->assertDontSee('Actividad del perfil');
    }
}
