<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_is_disabled(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_admin_can_create_new_users_via_management(): void
    {
        $admin = User::factory()->create([
            'rol' => 'administrador',
        ]);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Nuevo Trabajador',
            'email' => 'trabajador.nuevo@erp.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'rol' => 'trabajador',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'trabajador.nuevo@erp.local',
            'rol' => 'trabajador',
        ]);
    }
}
