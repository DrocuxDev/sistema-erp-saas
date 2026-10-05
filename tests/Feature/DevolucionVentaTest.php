<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use App\Services\DevolucionVentaService;
use App\Services\DocumentoFiscalCalculator;
use App\Services\InventarioService;
use Database\Seeders\ConfiguracionSeeder;
use Database\Seeders\RolesAndModulosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class DevolucionVentaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $vendedor;

    private Producto $producto;

    private Venta $venta;

    private int $detalleId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ConfiguracionSeeder::class);
        $this->seed(RolesAndModulosSeeder::class);
        $this->admin = User::where('is_superadmin', true)->firstOrFail();
        $this->vendedor = User::where('email', 'ventas@jhardsystex.com')->firstOrFail();

        $cliente = Cliente::create(['nombre' => 'CLIENTE DEVOLUCIONES', 'ruc' => '20500000001', 'estado' => 'activo']);
        $this->producto = Producto::create([
            'nombre' => 'PRODUCTO DEVOLUCIONES', 'sku' => 'DEV-001', 'unidad_medida' => 'UND',
            'stock_actual' => 50, 'stock_minimo' => 0,
            'precio_base_compra' => 100, 'precio_base_venta' => 150,
            'puede_venderse' => true, 'puede_comprarse' => true, 'estado' => 'activo',
        ]);
        $this->actingAs($this->admin)->post(route('ventas.store'), [
            'cliente_id' => $cliente->id,
            'fecha' => now()->toDateString(),
            'lineas' => [[
                'producto_id' => $this->producto->id, 'cantidad' => 3,
                'precio_venta_unit' => 150, 'precio_compra_unit' => 100,
            ]],
        ])->assertRedirect();

        $this->venta = Venta::latest('id')->firstOrFail();
        $this->detalleId = $this->venta->detalles()->firstOrFail()->id;
    }

    private function solicitud(string $cantidad, ?string $clave = null): array
    {
        return [
            'idempotency_key' => $clave ?? (string) Str::uuid(),
            'fecha' => now()->toDateString(),
            'motivo' => 'Producto retornado por el cliente',
            'lineas' => [['venta_detalle_id' => $this->detalleId, 'cantidad' => $cantidad]],
        ];
    }

    public function test_devolucion_parcial_y_total_actualiza_stock_e_importes_sin_exceder_la_venta(): void
    {
        $this->assertEquals(47, (float) $this->producto->fresh()->stock_actual);

        $this->actingAs($this->admin)->get(route('ventas.devoluciones.create', $this->venta))->assertOk();
        $this->post(route('ventas.devoluciones.store', $this->venta), $this->solicitud('1.00'))->assertRedirect(route('ventas.show', $this->venta));
        $this->assertEquals(48, (float) $this->producto->fresh()->stock_actual);

        $this->post(route('ventas.devoluciones.store', $this->venta), $this->solicitud('3.00'))
            ->assertSessionHasErrors('lineas.0.cantidad');
        $this->assertEquals(48, (float) $this->producto->fresh()->stock_actual);

        $this->post(route('ventas.devoluciones.store', $this->venta), $this->solicitud('2.00'))->assertRedirect(route('ventas.show', $this->venta));
        $this->assertEquals(50, (float) $this->producto->fresh()->stock_actual);
        $this->assertDatabaseCount('devoluciones_venta', 2);
        $this->assertDatabaseCount('devolucion_venta_detalle', 2);
        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $this->producto->id,
            'referencia_tipo' => 'devolucion_venta',
            'tipo' => 'entrada',
        ]);
        $this->assertEquals(
            DocumentoFiscalCalculator::aCentimos($this->venta->total),
            $this->venta->devoluciones->sum(fn ($devolucion) => DocumentoFiscalCalculator::aCentimos($devolucion->total))
        );
        $this->actingAs($this->admin)->get(route('ventas.show', $this->venta))->assertOk()->assertSee('Devolución #');
    }

    public function test_reenvio_de_la_misma_solicitud_no_duplica_devolucion_ni_stock(): void
    {
        $datos = $this->solicitud('1.00');
        $this->actingAs($this->admin)->post(route('ventas.devoluciones.store', $this->venta), $datos)->assertRedirect();
        $this->post(route('ventas.devoluciones.store', $this->venta), $datos)->assertRedirect();

        $this->assertDatabaseCount('devoluciones_venta', 1);
        $this->assertDatabaseCount('devolucion_venta_detalle', 1);
        $this->assertEquals(48, (float) $this->producto->fresh()->stock_actual);
    }

    public function test_devolucion_rechaza_lineas_ajenas_y_usuarios_sin_permiso(): void
    {
        $this->actingAs($this->vendedor)->get(route('ventas.devoluciones.create', $this->venta))->assertForbidden();
        $this->post(route('ventas.devoluciones.store', $this->venta), $this->solicitud('1.00'))->assertForbidden();

        $datos = $this->solicitud('1.00');
        $datos['lineas'][0]['venta_detalle_id'] = $this->detalleId + 999;
        $this->actingAs($this->admin)->post(route('ventas.devoluciones.store', $this->venta), $datos)
            ->assertSessionHasErrors('lineas.0.venta_detalle_id');
        $this->assertDatabaseCount('devoluciones_venta', 0);
    }

    public function test_fallo_en_inventario_revierte_devolucion_e_importe(): void
    {
        $this->app->instance(InventarioService::class, new class extends InventarioService
        {
            public function registrar(int $productoId, string $tipo, string $cantidad, string $referenciaTipo, ?int $referenciaId, string $fecha): void
            {
                parent::registrar($productoId, $tipo, $cantidad, $referenciaTipo, $referenciaId, $fecha);
                throw new RuntimeException('Fallo simulado posterior al movimiento');
            }
        });

        try {
            app(DevolucionVentaService::class)->registrar($this->venta, $this->solicitud('1.00'), $this->admin->id);
            $this->fail('La operación debió fallar.');
        } catch (RuntimeException $e) {
            $this->assertSame('Fallo simulado posterior al movimiento', $e->getMessage());
        }

        $this->assertDatabaseCount('devoluciones_venta', 0);
        $this->assertDatabaseCount('devolucion_venta_detalle', 0);
        $this->assertDatabaseMissing('movimientos_inventario', ['referencia_tipo' => 'devolucion_venta']);
        $this->assertEquals(47, (float) $this->producto->fresh()->stock_actual);
    }
}
