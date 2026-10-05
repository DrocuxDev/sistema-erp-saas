<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Database\Seeders\ConfiguracionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CotizacionComboboxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ConfiguracionSeeder::class);
        $this->actingAs(User::factory()->create(['rol' => 'trabajador']));
    }

    private function productoRapido(): array
    {
        $proveedor = Proveedor::create(['nombre' => 'Proveedor de prueba', 'estado' => 'activo']);

        return [
            'nombre' => 'Producto rápido', 'sku' => 'RAPIDO-01',
            'precio_venta' => '100.00', 'precio_compra' => '60.00',
            'proveedor_id' => $proveedor->id,
        ];
    }

    private function cotizacionPayload(array $producto): array
    {
        $cliente = Cliente::create(['nombre' => 'Cliente de prueba']);

        return [
            'cliente_id' => $cliente->id, 'tipo_cotizacion' => 'producto', 'fecha' => '2026-10-07',
            'lineas' => [[
                'producto_id' => $producto['id'], 'proveedor_id' => $producto['proveedores'][0]['id'],
                'cantidad' => '1', 'precio_venta_unit' => '100.00', 'precio_compra_unit' => '60.00',
            ]],
        ];
    }

    public function test_producto_rapido_con_proveedor_se_puede_cotizar_inmediatamente(): void
    {
        $data = $this->productoRapido();
        $producto = $this->postJson(route('productos.rapido'), $data)->assertCreated()
            ->assertJsonPath('unidad_medida', 'UND')
            ->assertJsonPath('proveedores.0.id', $data['proveedor_id'])->json();
        $this->assertDatabaseHas('producto_proveedor', [
            'producto_id' => $producto['id'], 'proveedor_id' => $data['proveedor_id'], 'precio_compra' => 60,
        ]);
        $this->post(route('cotizaciones.store'), $this->cotizacionPayload($producto))
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('cotizacion_detalle', ['producto_id' => $producto['id']]);
    }

    public function test_producto_rapido_requiere_proveedor_activo_y_precio_de_compra(): void
    {
        $data = $this->productoRapido();
        $sinProveedor = $data;
        unset($sinProveedor['proveedor_id'], $sinProveedor['precio_compra']);
        $this->postJson(route('productos.rapido'), $sinProveedor)->assertUnprocessable()
            ->assertJsonValidationErrors(['proveedor_id', 'precio_compra']);
        Proveedor::findOrFail($data['proveedor_id'])->update(['estado' => 'inactivo']);
        $this->postJson(route('productos.rapido'), $data)->assertUnprocessable()
            ->assertJsonValidationErrors('proveedor_id');
        $this->assertSame(0, Producto::count());
    }

    public function test_servidor_rechaza_productos_duplicados_al_crear_y_editar(): void
    {
        $producto = $this->postJson(route('productos.rapido'), $this->productoRapido())->assertCreated()->json();
        $payload = $this->cotizacionPayload($producto);
        $duplicado = $payload;
        $duplicado['lineas'][] = $payload['lineas'][0];
        $this->postJson(route('cotizaciones.store'), $duplicado)->assertUnprocessable()
            ->assertJsonValidationErrors('lineas.0.producto_id');
        $this->assertSame(0, Cotizacion::count());

        $this->post(route('cotizaciones.store'), $payload)->assertSessionHasNoErrors();
        $cotizacion = Cotizacion::firstOrFail();
        $version = $cotizacion->version;
        $this->putJson(route('cotizaciones.update', $cotizacion), $duplicado)->assertUnprocessable()
            ->assertJsonValidationErrors('lineas.1.producto_id');
        $this->assertSame($version, $cotizacion->fresh()->version);
        $this->assertSame(1, $cotizacion->detalles()->count());
    }
}
