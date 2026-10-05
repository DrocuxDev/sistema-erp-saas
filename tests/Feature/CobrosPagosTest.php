<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\CobroVenta;
use App\Models\Compra;
use App\Models\PagoCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Venta;
use App\Services\SaldoFinancieroService;
use Database\Seeders\ConfiguracionSeeder;
use Database\Seeders\RolesAndModulosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CobrosPagosTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $vendedor;

    private Venta $venta;

    private Compra $compra;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ConfiguracionSeeder::class);
        $this->seed(RolesAndModulosSeeder::class);
        $this->admin = User::where('is_superadmin', true)->firstOrFail();
        $this->vendedor = User::where('email', 'ventas@jhardsystex.com')->firstOrFail();

        $cliente = Cliente::create(['nombre' => 'CLIENTE COBROS', 'ruc' => '20500000001', 'estado' => 'activo']);
        $proveedor = Proveedor::create(['nombre' => 'PROVEEDOR PAGOS', 'ruc' => '20600000001', 'estado' => 'activo']);
        $producto = Producto::create([
            'nombre' => 'PRODUCTO FINANZAS', 'sku' => 'FIN-001', 'unidad_medida' => 'UND',
            'stock_actual' => 50, 'stock_minimo' => 0,
            'precio_base_compra' => 100, 'precio_base_venta' => 150,
            'puede_venderse' => true, 'puede_comprarse' => true, 'estado' => 'activo',
        ]);

        $this->actingAs($this->admin)->post(route('ventas.store'), [
            'cliente_id' => $cliente->id, 'fecha' => now()->toDateString(),
            'lineas' => [[
                'producto_id' => $producto->id, 'cantidad' => 3,
                'precio_venta_unit' => 150, 'precio_compra_unit' => 100,
            ]],
        ])->assertRedirect();
        $this->venta = Venta::latest('id')->firstOrFail();

        $this->post(route('compras.store'), [
            'proveedor_id' => $proveedor->id, 'fecha' => now()->toDateString(),
            'lineas' => [[
                'producto_id' => $producto->id, 'cantidad' => 3, 'precio_compra_unit' => 100,
            ]],
        ])->assertRedirect();
        $this->compra = Compra::latest('id')->firstOrFail();
    }

    private function movimiento(string $tipo, string $monto, ?string $clave = null): array
    {
        return [
            'idempotency_key' => $clave ?? (string) Str::uuid(),
            'tipo' => $tipo,
            'fecha' => now()->toDateString(),
            'monto' => $monto,
            'medio' => 'transferencia',
            'referencia' => 'OPERACION-123',
            'observacion' => 'Movimiento confirmado en banco',
        ];
    }

    public function test_cobros_parciales_devolucion_y_reembolso_con_saldo_exacto(): void
    {
        $saldo = app(SaldoFinancieroService::class)->venta($this->venta);
        $this->assertSame(53100, $saldo['saldo']);
        $this->actingAs($this->admin)->get(route('ventas.cobros.create', $this->venta))->assertOk();

        $this->post(route('ventas.cobros.store', $this->venta), $this->movimiento('cobro', '200.00'))->assertRedirect();
        $this->post(route('ventas.cobros.store', $this->venta), $this->movimiento('cobro', '400.00'))
            ->assertSessionHasErrors('monto');
        $this->post(route('ventas.cobros.store', $this->venta), $this->movimiento('cobro', '331.00'))->assertRedirect();
        $this->assertSame(0, app(SaldoFinancieroService::class)->venta($this->venta)['saldo']);

        $this->post(route('ventas.devoluciones.store', $this->venta), [
            'idempotency_key' => (string) Str::uuid(), 'fecha' => now()->toDateString(),
            'motivo' => 'Producto devuelto',
            'lineas' => [['venta_detalle_id' => $this->venta->detalles()->firstOrFail()->id, 'cantidad' => '1.00']],
        ])->assertRedirect();
        $this->assertSame(17700, app(SaldoFinancieroService::class)->venta($this->venta)['exceso']);

        $this->get(route('ventas.cobros.create', ['venta' => $this->venta, 'tipo' => 'reembolso']))->assertOk();
        $this->post(route('ventas.cobros.store', $this->venta), $this->movimiento('reembolso', '177.00'))->assertRedirect();
        $saldo = app(SaldoFinancieroService::class)->venta($this->venta);
        $this->assertSame(0, $saldo['saldo']);
        $this->assertSame(0, $saldo['exceso']);
        $this->assertDatabaseCount('cobros_venta', 3);
        $this->assertDatabaseHas('auditoria', ['tabla' => 'cobros_venta', 'accion' => 'creacion']);
        $this->get(route('ventas.show', $this->venta))->assertOk()->assertSee('Cobros (3)');
    }

    public function test_pagos_parciales_devolucion_y_reintegro_con_saldo_exacto(): void
    {
        $this->assertSame(30000, app(SaldoFinancieroService::class)->compra($this->compra)['saldo']);
        $this->actingAs($this->admin)->get(route('compras.pagos.create', $this->compra))->assertOk();

        $this->post(route('compras.pagos.store', $this->compra), $this->movimiento('pago', '100.00'))->assertRedirect();
        $this->post(route('compras.pagos.store', $this->compra), $this->movimiento('pago', '250.00'))
            ->assertSessionHasErrors('monto');
        $this->post(route('compras.pagos.store', $this->compra), $this->movimiento('pago', '200.00'))->assertRedirect();
        $this->assertSame(0, app(SaldoFinancieroService::class)->compra($this->compra)['saldo']);

        $this->post(route('compras.devoluciones.store', $this->compra), [
            'idempotency_key' => (string) Str::uuid(), 'fecha' => now()->toDateString(),
            'motivo' => 'Mercancía defectuosa',
            'lineas' => [['compra_detalle_id' => $this->compra->detalles()->firstOrFail()->id, 'cantidad' => '1.00']],
        ])->assertRedirect();
        $this->assertSame(10000, app(SaldoFinancieroService::class)->compra($this->compra)['exceso']);

        $this->get(route('compras.pagos.create', ['compra' => $this->compra, 'tipo' => 'reintegro']))->assertOk();
        $this->post(route('compras.pagos.store', $this->compra), $this->movimiento('reintegro', '100.00'))->assertRedirect();
        $saldo = app(SaldoFinancieroService::class)->compra($this->compra);
        $this->assertSame(0, $saldo['saldo']);
        $this->assertSame(0, $saldo['exceso']);
        $this->assertDatabaseCount('pagos_compra', 3);
        $this->assertDatabaseHas('auditoria', ['tabla' => 'pagos_compra', 'accion' => 'creacion']);
        $this->get(route('compras.show', $this->compra))->assertOk()->assertSee('Pagos (3)');
    }

    public function test_reenvio_y_permisos_no_duplican_movimientos(): void
    {
        $cobro = $this->movimiento('cobro', '50.00');
        $pago = $this->movimiento('pago', '50.00');
        $this->actingAs($this->vendedor)->post(route('ventas.cobros.store', $this->venta), $cobro)->assertForbidden();
        $this->post(route('compras.pagos.store', $this->compra), $pago)->assertForbidden();

        $this->actingAs($this->admin)->post(route('ventas.cobros.store', $this->venta), $cobro)->assertRedirect();
        $this->post(route('ventas.cobros.store', $this->venta), $cobro)->assertRedirect();
        $this->post(route('compras.pagos.store', $this->compra), $pago)->assertRedirect();
        $this->post(route('compras.pagos.store', $this->compra), $pago)->assertRedirect();
        $this->assertDatabaseCount('cobros_venta', 1);
        $this->assertDatabaseCount('pagos_compra', 1);
    }

    public function test_rechaza_importes_sin_referencia_o_sin_saldo_disponible(): void
    {
        $sinReferencia = $this->movimiento('cobro', '10.00');
        unset($sinReferencia['referencia']);
        $this->actingAs($this->admin)->post(route('ventas.cobros.store', $this->venta), $sinReferencia)
            ->assertSessionHasErrors('referencia');

        $this->post(route('ventas.cobros.store', $this->venta), $this->movimiento('reembolso', '10.00'))
            ->assertSessionHasErrors('monto');
        $this->post(route('compras.pagos.store', $this->compra), $this->movimiento('reintegro', '10.00'))
            ->assertSessionHasErrors('monto');
        $this->assertDatabaseCount('cobros_venta', 0);
        $this->assertDatabaseCount('pagos_compra', 0);
    }

    public function test_anulacion_auditada_reabre_saldo_y_no_permite_reusar_la_solicitud(): void
    {
        $datosCobro = $this->movimiento('cobro', '100.00');
        $this->actingAs($this->admin)->post(route('ventas.cobros.store', $this->venta), $datosCobro)->assertRedirect();
        $cobro = CobroVenta::firstOrFail();
        $this->patch(route('ventas.cobros.anular', ['venta' => $this->venta, 'cobro' => $cobro]), [
            'motivo_anulacion' => 'Operación registrada en documento equivocado',
        ])->assertRedirect();
        $this->assertSame('anulado', $cobro->fresh()->estado);
        $this->assertSame($this->admin->id, $cobro->fresh()->anulado_por);
        $this->assertSame(53100, app(SaldoFinancieroService::class)->venta($this->venta)['saldo']);
        $this->assertDatabaseHas('auditoria', ['tabla' => 'cobros_venta', 'accion' => 'anulacion']);
        $this->post(route('ventas.cobros.store', $this->venta), $datosCobro)
            ->assertSessionHasErrors('idempotency_key');

        $this->post(route('compras.pagos.store', $this->compra), $this->movimiento('pago', '100.00'))->assertRedirect();
        $pago = PagoCompra::firstOrFail();
        $this->patch(route('compras.pagos.anular', ['compra' => $this->compra, 'pago' => $pago]), [
            'motivo_anulacion' => 'Pago ingresado por duplicado en el sistema',
        ])->assertRedirect();
        $this->assertSame('anulado', $pago->fresh()->estado);
        $this->assertSame(30000, app(SaldoFinancieroService::class)->compra($this->compra)['saldo']);
        $this->assertDatabaseHas('auditoria', ['tabla' => 'pagos_compra', 'accion' => 'anulacion']);
    }

    public function test_no_anula_un_abono_que_respalda_un_reembolso_o_reintegro_vigente(): void
    {
        $this->actingAs($this->admin)->post(route('ventas.cobros.store', $this->venta), $this->movimiento('cobro', '531.00'))->assertRedirect();
        $cobro = CobroVenta::firstOrFail();
        $this->post(route('ventas.devoluciones.store', $this->venta), [
            'idempotency_key' => (string) Str::uuid(), 'fecha' => now()->toDateString(),
            'motivo' => 'Producto devuelto',
            'lineas' => [['venta_detalle_id' => $this->venta->detalles()->firstOrFail()->id, 'cantidad' => '1.00']],
        ])->assertRedirect();
        $this->post(route('ventas.cobros.store', $this->venta), $this->movimiento('reembolso', '177.00'))->assertRedirect();
        $reembolso = CobroVenta::where('tipo', 'reembolso')->firstOrFail();
        $motivo = ['motivo_anulacion' => 'Corrección financiera documentada'];
        $this->patch(route('ventas.cobros.anular', ['venta' => $this->venta, 'cobro' => $cobro]), $motivo)
            ->assertSessionHasErrors('motivo_anulacion');
        $this->patch(route('ventas.cobros.anular', ['venta' => $this->venta, 'cobro' => $reembolso]), $motivo)->assertRedirect();
        $this->patch(route('ventas.cobros.anular', ['venta' => $this->venta, 'cobro' => $cobro]), $motivo)->assertRedirect();

        $this->post(route('compras.pagos.store', $this->compra), $this->movimiento('pago', '300.00'))->assertRedirect();
        $pago = PagoCompra::where('tipo', 'pago')->firstOrFail();
        $this->post(route('compras.devoluciones.store', $this->compra), [
            'idempotency_key' => (string) Str::uuid(), 'fecha' => now()->toDateString(),
            'motivo' => 'Mercancía defectuosa',
            'lineas' => [['compra_detalle_id' => $this->compra->detalles()->firstOrFail()->id, 'cantidad' => '1.00']],
        ])->assertRedirect();
        $this->post(route('compras.pagos.store', $this->compra), $this->movimiento('reintegro', '100.00'))->assertRedirect();
        $reintegro = PagoCompra::where('tipo', 'reintegro')->firstOrFail();
        $this->patch(route('compras.pagos.anular', ['compra' => $this->compra, 'pago' => $pago]), $motivo)
            ->assertSessionHasErrors('motivo_anulacion');
        $this->patch(route('compras.pagos.anular', ['compra' => $this->compra, 'pago' => $reintegro]), $motivo)->assertRedirect();
        $this->patch(route('compras.pagos.anular', ['compra' => $this->compra, 'pago' => $pago]), $motivo)->assertRedirect();
    }

    public function test_saldo_de_venta_descuenta_retencion_y_devolucion_proporcional(): void
    {
        $cliente = Cliente::create([
            'nombre' => 'CLIENTE CON RETENCIÓN', 'ruc' => '20500000002',
            'estado' => 'activo', 'pct_igv' => '18.00', 'pct_retencion' => '10.00',
        ]);
        $producto = Producto::create([
            'nombre' => 'PRODUCTO RETENCIÓN', 'sku' => 'FIN-RET-001',
            'stock_actual' => 2, 'puede_venderse' => true,
        ]);
        $this->actingAs($this->admin)->post(route('ventas.store'), [
            'cliente_id' => $cliente->id, 'fecha' => now()->toDateString(),
            'lineas' => [[
                'producto_id' => $producto->id, 'cantidad' => '2.00',
                'precio_venta_unit' => '100.00', 'precio_compra_unit' => '50.00',
            ]],
        ])->assertRedirect();
        $venta = Venta::latest('id')->firstOrFail();
        $this->assertSame('236.00', $venta->total);
        $this->assertSame('20.00', $venta->retencion);
        $this->assertSame(21600, app(SaldoFinancieroService::class)->venta($venta)['saldo']);

        $this->post(route('ventas.devoluciones.store', $venta), [
            'idempotency_key' => (string) Str::uuid(), 'fecha' => now()->toDateString(),
            'motivo' => 'Producto retornado',
            'lineas' => [['venta_detalle_id' => $venta->detalles()->firstOrFail()->id, 'cantidad' => '1.00']],
        ])->assertRedirect();
        $this->assertSame(10800, app(SaldoFinancieroService::class)->venta($venta)['saldo']);
    }
}
