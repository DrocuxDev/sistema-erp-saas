<?php

namespace Tests\Feature;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use App\Services\DevolucionCompraService;
use App\Services\DocumentoFiscalCalculator;
use App\Services\InventarioService;
use Database\Seeders\ConfiguracionSeeder;
use Database\Seeders\RolesAndModulosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class DevolucionCompraTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $vendedor;

    private Producto $producto;

    private Compra $compra;

    private int $detalleId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ConfiguracionSeeder::class);
        $this->seed(RolesAndModulosSeeder::class);
        $this->admin = User::where('is_superadmin', true)->firstOrFail();
        $this->vendedor = User::where('email', 'ventas@jhardsystex.com')->firstOrFail();

        $proveedor = Proveedor::create([
            'nombre' => 'PROVEEDOR DEVOLUCIONES', 'ruc' => '20600000001',
            'direccion' => 'Av. Prueba 123', 'estado' => 'activo',
        ]);
        $this->producto = Producto::create([
            'nombre' => 'PRODUCTO DEVOLUCIONES COMPRA', 'sku' => 'DEV-COMPRA-001', 'unidad_medida' => 'UND',
            'stock_actual' => 0, 'stock_minimo' => 0,
            'precio_base_compra' => 100, 'precio_base_venta' => 150,
            'puede_venderse' => true, 'puede_comprarse' => true, 'estado' => 'activo',
        ]);
        $this->actingAs($this->admin)->post(route('compras.store'), [
            'proveedor_id' => $proveedor->id,
            'fecha' => now()->toDateString(),
            'lineas' => [[
                'producto_id' => $this->producto->id, 'cantidad' => 3, 'precio_compra_unit' => 100,
            ]],
        ])->assertRedirect();

        $this->compra = Compra::latest('id')->firstOrFail();
        $this->detalleId = $this->compra->detalles()->firstOrFail()->id;
    }

    private function solicitud(string $cantidad, ?string $clave = null): array
    {
        return [
            'idempotency_key' => $clave ?? (string) Str::uuid(),
            'fecha' => now()->toDateString(),
            'motivo' => 'Mercancía defectuosa devuelta al proveedor',
            'lineas' => [['compra_detalle_id' => $this->detalleId, 'cantidad' => $cantidad]],
        ];
    }

    public function test_devolucion_parcial_y_total_descuenta_stock_y_conserva_importes(): void
    {
        $this->assertEquals(3, (float) $this->producto->fresh()->stock_actual);
        $this->actingAs($this->admin)->get(route('compras.devoluciones.create', $this->compra))->assertOk();

        $this->post(route('compras.devoluciones.store', $this->compra), $this->solicitud('1.00'))
            ->assertRedirect(route('compras.show', $this->compra));
        $this->assertEquals(2, (float) $this->producto->fresh()->stock_actual);

        $this->post(route('compras.devoluciones.store', $this->compra), $this->solicitud('3.00'))
            ->assertSessionHasErrors('lineas.0.cantidad');
        $this->assertEquals(2, (float) $this->producto->fresh()->stock_actual);

        $this->post(route('compras.devoluciones.store', $this->compra), $this->solicitud('2.00'))
            ->assertRedirect(route('compras.show', $this->compra));
        $this->assertEquals(0, (float) $this->producto->fresh()->stock_actual);
        $this->assertDatabaseCount('devoluciones_compra', 2);
        $this->assertDatabaseCount('devolucion_compra_detalle', 2);
        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $this->producto->id,
            'referencia_tipo' => 'devolucion_compra',
            'tipo' => 'salida',
        ]);
        $this->assertEquals(
            DocumentoFiscalCalculator::aCentimos($this->compra->total),
            $this->compra->devoluciones->sum(fn ($devolucion) => DocumentoFiscalCalculator::aCentimos($devolucion->total))
        );
        $this->actingAs($this->admin)->get(route('compras.show', $this->compra))->assertOk()->assertSee('Devolución #');
    }

    public function test_reenvio_no_duplica_la_devolucion_ni_la_salida_de_stock(): void
    {
        $datos = $this->solicitud('1.00');
        $this->actingAs($this->admin)->post(route('compras.devoluciones.store', $this->compra), $datos)->assertRedirect();
        $this->post(route('compras.devoluciones.store', $this->compra), $datos)->assertRedirect();

        $this->assertDatabaseCount('devoluciones_compra', 1);
        $this->assertDatabaseCount('devolucion_compra_detalle', 1);
        $this->assertEquals(2, (float) $this->producto->fresh()->stock_actual);
    }

    public function test_rechaza_linea_ajena_y_usuario_sin_permiso(): void
    {
        $this->actingAs($this->vendedor)->get(route('compras.devoluciones.create', $this->compra))->assertForbidden();
        $this->post(route('compras.devoluciones.store', $this->compra), $this->solicitud('1.00'))->assertForbidden();

        $datos = $this->solicitud('1.00');
        $datos['lineas'][0]['compra_detalle_id'] = $this->detalleId + 999;
        $this->actingAs($this->admin)->post(route('compras.devoluciones.store', $this->compra), $datos)
            ->assertSessionHasErrors('lineas.0.compra_detalle_id');
        $this->assertDatabaseCount('devoluciones_compra', 0);
    }

    public function test_stock_insuficiente_revierte_la_devolucion_completa(): void
    {
        DB::transaction(fn () => app(InventarioService::class)->registrar(
            $this->producto->id, 'salida', '2.00', 'ajuste', null, now()->toDateString()
        ));

        $this->actingAs($this->admin)->post(route('compras.devoluciones.store', $this->compra), $this->solicitud('2.00'))
            ->assertSessionHasErrors('lineas');
        $this->assertDatabaseCount('devoluciones_compra', 0);
        $this->assertDatabaseMissing('movimientos_inventario', ['referencia_tipo' => 'devolucion_compra']);
        $this->assertEquals(1, (float) $this->producto->fresh()->stock_actual);
    }

    public function test_fallo_posterior_al_movimiento_revierte_stock_y_registros(): void
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
            app(DevolucionCompraService::class)->registrar($this->compra, $this->solicitud('1.00'), $this->admin->id);
            $this->fail('La operación debió fallar.');
        } catch (RuntimeException $e) {
            $this->assertSame('Fallo simulado posterior al movimiento', $e->getMessage());
        }

        $this->assertDatabaseCount('devoluciones_compra', 0);
        $this->assertDatabaseCount('devolucion_compra_detalle', 0);
        $this->assertDatabaseMissing('movimientos_inventario', ['referencia_tipo' => 'devolucion_compra']);
        $this->assertEquals(3, (float) $this->producto->fresh()->stock_actual);
    }
}
