<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class SpanishInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_and_validation_messages_are_in_spanish(): void
    {
        $this->assertSame('es', app()->getLocale());

        $this->post('/login', [])->assertSessionHasErrors([
            'email' => 'El campo correo electrónico es obligatorio.',
            'password' => 'El campo contraseña es obligatorio.',
        ]);

        $this->post('/login', [
            'email' => 'sin-cuenta@example.com',
            'password' => 'contraseña-incorrecta',
        ])->assertSessionHasErrors([
            'email' => 'Las credenciales ingresadas no coinciden con nuestros registros.',
        ]);
    }

    public function test_account_views_and_http_errors_are_in_spanish(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('¿Olvidaste tu contraseña?')
            ->assertSee('Enviar enlace para restablecer la contraseña');

        $this->get('/reset-password/token-de-prueba')
            ->assertOk()
            ->assertSee('Confirmar contraseña')
            ->assertSee('Restablecer contraseña');

        $user = User::factory()->create(['email_verified_at' => null]);
        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Información del perfil')
            ->assertSee('Actualizar contraseña')
            ->assertSee('Eliminar cuenta');

        $this->get('/confirm-password')
            ->assertOk()
            ->assertSee('Esta es una sección segura.');

        $this->get('/verify-email')
            ->assertOk()
            ->assertSee('Gracias por registrarte.')
            ->assertSee('Reenviar correo de verificación');

        $this->get('/pagina-inexistente')
            ->assertNotFound()
            ->assertSee('Página no encontrada');
    }

    public function test_pagination_and_password_notification_are_in_spanish(): void
    {
        $paginator = new LengthAwarePaginator([1, 2], 20, 2);
        $html = $paginator->links()->toHtml();

        $this->assertStringContainsString('Mostrando', $html);
        $this->assertStringContainsString('resultados', $html);
        $this->assertStringContainsString('Siguiente', $html);

        $notification = (new ResetPassword('token-de-prueba'))
            ->toMail(User::factory()->make(['email' => 'usuario@example.com']));

        $this->assertSame('Restablece tu contraseña', $notification->subject);
        $this->assertSame('Restablecer contraseña', $notification->actionText);
        $this->assertStringContainsString('¡Hola!', $notification->render()->toHtml());
        $this->assertStringContainsString('Si tienes problemas al pulsar el botón', $notification->render()->toHtml());
    }
}
