<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\OrdenCompraCliente;
use App\Models\Proveedor;
use App\Models\User;
use Database\Seeders\ConfiguracionSeeder;
use Database\Seeders\RolesAndModulosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: los parámetros de ruta deben coincidir con las variables
 * de los controladores para que el implicit binding funcione.
 * Casos históricos: {proveedore} vs $proveedor, {ordenes_compra_cliente}
 * vs $ordenesCompraCliente (el singularizador inglés rompe el español).
 */
class RouteBindingRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Proveedor $proveedor;

    protected OrdenCompraCliente $ordenCliente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ConfiguracionSeeder::class);
        $this->seed(RolesAndModulosSeeder::class);

        $this->admin = User::where('is_superadmin', true)->first();

        $this->proveedor = Proveedor::create([
            'nombre' => 'PROVEEDOR BINDING SAC',
            'razon_social' => 'PROVEEDOR BINDING SAC',
            'ruc' => '20600000099',
            'direccion' => 'Av. Binding 123',
            'condicion_pago' => 'Contado',
            'estado' => 'activo',
        ]);

        $cliente = Cliente::create([
            'nombre' => 'CLIENTE BINDING SAC',
            'ruc' => '20500000099',
            'estado' => 'activo',
        ]);

        $this->ordenCliente = OrdenCompraCliente::create([
            'cliente_id' => $cliente->id,
            'usuario_id' => $this->admin->id,
            'numero_oc' => 'OC-BIND-001',
            'fecha_recepcion' => now()->toDateString(),
            'estado' => 'pendiente',
        ]);
    }

    public function test_proveedor_show_edit_update_destroy_resuelven_binding(): void
    {
        $this->actingAs($this->admin)
            ->get(route('proveedores.show', $this->proveedor))
            ->assertOk()
            ->assertSee('PROVEEDOR BINDING SAC');

        $this->actingAs($this->admin)
            ->get(route('proveedores.edit', $this->proveedor))
            ->assertOk();

        $this->actingAs($this->admin)
            ->patch(route('proveedores.update', $this->proveedor), [
                'nombre' => 'PROVEEDOR BINDING SAC',
                'razon_social' => 'PROVEEDOR BINDING SAC',
                'ruc' => '20600000099',
                'direccion' => 'Av. Binding 456',
                'condicion_pago' => 'Contado',
                'estado' => 'activo',
            ])
            ->assertRedirect(route('proveedores.index'));

        $this->actingAs($this->admin)
            ->delete(route('proveedores.destroy', $this->proveedor))
            ->assertRedirect(route('proveedores.index'));

        $this->assertDatabaseMissing('proveedores', ['id' => $this->proveedor->id]);
    }

    public function test_orden_compra_cliente_show_edit_resuelven_binding(): void
    {
        $this->actingAs($this->admin)
            ->get(route('ordenes-compra-cliente.show', $this->ordenCliente))
            ->assertOk()
            ->assertSee('OC-BIND-001');

        $this->actingAs($this->admin)
            ->get(route('ordenes-compra-cliente.edit', $this->ordenCliente))
            ->assertOk();
    }

    public function test_rutas_muertas_devuelven_4xx_en_lugar_de_500(): void
    {
        // Estos controladores no implementan show/edit: debe responder 404
        // (sin coincidencia) o 405 (la URI coincide con otro verbo, ej. DELETE),
        // pero nunca 500 por método inexistente.
        foreach (['/users/1', '/roles/1', '/series-documentos/1', '/ventas/1/edit', '/ordenes-compra/1/edit'] as $uri) {
            $status = $this->actingAs($this->admin)->get($uri)->status();
            $this->assertContains($status, [404, 405], "La ruta {$uri} devolvió {$status}, se esperaba 404 o 405.");
        }
    }
}
