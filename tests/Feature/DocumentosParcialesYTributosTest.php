<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Cliente;
use App\Models\ComprobanteVenta;
use App\Models\Cotizacion;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraCliente;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Venta;
use App\Services\CotizacionResumenCalculator;
use App\Support\ImagenUpload;
use Database\Seeders\ConfiguracionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentosParcialesYTributosTest extends TestCase
{
    use RefreshDatabase;

    public function test_venta_parcial_respeta_cliente_saldo_y_condiciones_tributarias(): void
    {
        $this->seed(ConfiguracionSeeder::class);
        $usuario = User::factory()->create(['rol' => 'administrador']);
        $cliente = Cliente::create(['nombre' => 'Cliente A', 'ruc' => '20111111111', 'pct_igv' => 10, 'pct_retencion' => 2]);
        $otro = Cliente::create(['nombre' => 'Cliente B', 'ruc' => '20222222222']);
        $proveedor = Proveedor::create(['nombre' => 'Proveedor', 'ruc' => '20333333333']);
        $producto = Producto::create(['nombre' => 'Producto', 'sku' => 'PARCIAL-1', 'stock_actual' => 10, 'puede_venderse' => true]);
        $ordenCliente = OrdenCompraCliente::create([
            'cliente_id' => $cliente->id, 'numero_oc' => 'OC-CLIENTE-01', 'fecha_recepcion' => '2026-10-03',
        ]);

        $this->actingAs($usuario)->post(route('cotizaciones.store'), [
            'cliente_id' => $cliente->id, 'orden_compra_cliente_id' => $ordenCliente->id,
            'tipo_cotizacion' => 'producto', 'fecha' => '2026-10-03',
            'lineas' => [[
                'producto_id' => $producto->id, 'proveedor_id' => $proveedor->id,
                'cantidad' => 3, 'precio_venta_unit' => 100, 'precio_compra_unit' => 50,
            ]],
        ])->assertRedirect();
        $cotizacion = Cotizacion::firstOrFail();
        $cotizacion->update(['estado' => 'aprobada']);

        $base = ['cotizacion_id' => $cotizacion->id, 'fecha' => '2026-10-03', 'lineas' => [[
            'producto_id' => $producto->id, 'cantidad' => 1, 'precio_venta_unit' => 100, 'precio_compra_unit' => 50,
        ]]];
        $this->actingAs($usuario)->post(route('ventas.store'), ['cliente_id' => $otro->id, ...$base])
            ->assertSessionHasErrors('cotizacion_id');
        $this->actingAs($usuario)->post(route('ventas.store'), ['cliente_id' => $cliente->id, ...$base])->assertRedirect();
        $primera = Venta::firstOrFail();
        $this->assertEquals($ordenCliente->id, $primera->orden_compra_cliente_id);
        $this->assertSame('10.00', $primera->igv);
        $this->assertSame('2.00', $primera->retencion);
        $this->assertSame('aprobada', $cotizacion->fresh()->estado);

        $base['lineas'][0]['cantidad'] = 2;
        $this->actingAs($usuario)->post(route('ventas.store'), ['cliente_id' => $cliente->id, ...$base])->assertRedirect();
        $this->assertEquals(2, Venta::where('orden_compra_cliente_id', $ordenCliente->id)->count());
        $this->assertSame('facturada', $cotizacion->fresh()->estado);
        $this->assertEquals(7, (float) $producto->fresh()->stock_actual);
        $this->actingAs($usuario)->post(route('ventas.store'), ['cliente_id' => $cliente->id, ...$base])
            ->assertSessionHasErrors('cotizacion_id');
    }

    public function test_recepcion_parcial_no_supera_orden_y_actualiza_saldo(): void
    {
        $usuario = User::factory()->create(['rol' => 'administrador']);
        $proveedor = Proveedor::create(['nombre' => 'Proveedor', 'ruc' => '20333333333']);
        $producto = Producto::create(['nombre' => 'Producto', 'sku' => 'PARCIAL-2', 'stock_actual' => 4, 'puede_comprarse' => true]);
        $orden = OrdenCompra::create(['proveedor_id' => $proveedor->id, 'fecha' => '2026-10-03', 'estado' => 'aprobada', 'total' => 60]);
        DB::table('orden_compra_detalle')->insert([
            'orden_compra_id' => $orden->id, 'producto_id' => $producto->id,
            'cantidad' => 3, 'precio_compra_unit' => 20,
        ]);
        $base = ['proveedor_id' => $proveedor->id, 'orden_compra_id' => $orden->id, 'fecha' => '2026-10-03',
            'lineas' => [['producto_id' => $producto->id, 'cantidad' => 1, 'precio_compra_unit' => 20]]];
        $this->actingAs($usuario)->post(route('compras.store'), $base)->assertRedirect();
        $this->assertSame('aprobada', $orden->fresh()->estado);
        $this->assertEquals(5, (float) $producto->fresh()->stock_actual);

        $base['lineas'][0]['cantidad'] = 3;
        $this->actingAs($usuario)->post(route('compras.store'), $base)->assertSessionHasErrors('lineas');
        $base['lineas'][0]['cantidad'] = 2;
        $this->actingAs($usuario)->post(route('compras.store'), $base)->assertRedirect();
        $this->assertSame('recibida', $orden->fresh()->estado);
        $this->assertEquals(7, (float) $producto->fresh()->stock_actual);
    }

    public function test_precio_con_igv_se_traslada_sin_cambiar_total_entre_documentos(): void
    {
        $this->seed(ConfiguracionSeeder::class);
        $usuario = User::factory()->create(['rol' => 'administrador']);
        $cliente = Cliente::create([
            'nombre' => 'Precio bruto', 'ruc' => '20666666666',
            'precio_incluye_igv' => true, 'pct_igv' => 18, 'pct_retencion' => 0,
        ]);
        $proveedor = Proveedor::create(['nombre' => 'Proveedor bruto', 'ruc' => '20777777777']);
        $producto = Producto::create([
            'nombre' => 'Producto bruto', 'sku' => 'IGV-118', 'stock_actual' => 2, 'puede_venderse' => true,
        ]);
        $this->actingAs($usuario)->post(route('cotizaciones.store'), [
            'cliente_id' => $cliente->id, 'tipo_cotizacion' => 'producto', 'fecha' => '2026-10-03',
            'lineas' => [[
                'producto_id' => $producto->id, 'proveedor_id' => $proveedor->id,
                'cantidad' => 1, 'precio_venta_unit' => 118, 'precio_compra_unit' => 50,
            ]],
        ])->assertRedirect();
        $cotizacion = Cotizacion::with('resumen')->firstOrFail();
        $this->assertSame('100.00', $cotizacion->resumen->subtotal);
        $this->assertSame('18.00', $cotizacion->resumen->igv);
        $cotizacion->update(['estado' => 'aprobada']);

        $this->actingAs($usuario)->post(route('ventas.store'), [
            'cliente_id' => $cliente->id, 'cotizacion_id' => $cotizacion->id, 'fecha' => '2026-10-03',
            'lineas' => [[
                'producto_id' => $producto->id, 'cantidad' => 1,
                'precio_venta_unit' => 118, 'precio_compra_unit' => 50,
            ]],
        ])->assertRedirect();
        $venta = Venta::firstOrFail();
        $this->assertSame('118.00', $venta->total);
        $this->assertSame('100.00', $venta->subtotal);
        $this->actingAs($usuario)->post(route('comprobantes.store'), [
            'venta_id' => $venta->id, 'tipo' => 'factura', 'fecha_emision' => '2026-10-03',
        ])->assertRedirect();
        $factura = ComprobanteVenta::firstOrFail();
        $this->assertSame('100.00', $factura->subtotal);
        $this->assertSame('18.00', $factura->igv);
    }

    public function test_auditoria_registra_valor_anterior_nuevo_y_eliminacion(): void
    {
        $cliente = Cliente::create(['nombre' => 'Anterior', 'ruc' => '20444444444']);
        $cliente->update(['nombre' => 'Nuevo']);
        $cambio = Auditoria::where('tabla', 'clientes')->where('registro_id', $cliente->id)
            ->where('accion', 'edicion')->latest('id')->firstOrFail();
        $this->assertSame('Anterior', $cambio->datos_anteriores['nombre']);
        $this->assertSame('Nuevo', $cambio->datos_nuevos['nombre']);

        $cliente->delete();
        $this->assertDatabaseHas('auditoria', [
            'tabla' => 'clientes', 'registro_id' => $cliente->id, 'accion' => 'eliminacion',
        ]);
    }

    public function test_error_de_auditoria_aborta_y_revierte_la_operacion(): void
    {
        Log::spy();
        DB::statement("CREATE TRIGGER bloquear_auditoria_test BEFORE INSERT ON auditoria BEGIN SELECT RAISE(ABORT, 'auditoria no disponible'); END");

        try {
            Cliente::create(['nombre' => 'Sin auditoría', 'ruc' => '20888888888']);
            $this->fail('La operación debía fallar si no se puede persistir la auditoría.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('No se pudo registrar la auditoría', $exception->getMessage());
        } finally {
            DB::statement('DROP TRIGGER IF EXISTS bloquear_auditoria_test');
        }

        $this->assertDatabaseMissing('clientes', ['ruc' => '20888888888']);
        Log::shouldHaveReceived('critical')->once();
    }

    public function test_fallo_de_auditoria_revierte_edicion_y_borrado_aunque_se_capture_el_error(): void
    {
        $cliente = Cliente::create(['nombre' => 'Original', 'ruc' => '20999999999']);
        $nivel = DB::transactionLevel();
        Log::spy();
        DB::statement("CREATE TRIGGER bloquear_auditoria_test BEFORE INSERT ON auditoria BEGIN SELECT RAISE(ABORT, 'auditoria no disponible'); END");

        try {
            foreach (['editar', 'eliminar'] as $operacion) {
                try {
                    $registro = Cliente::findOrFail($cliente->id);
                    if ($operacion === 'editar') {
                        $registro->update(['nombre' => 'No debe persistir']);
                    } else {
                        $registro->delete();
                    }
                    $this->fail('La escritura debía revertirse por el fallo de auditoría.');
                } catch (\RuntimeException $exception) {
                    $this->assertStringContainsString('No se pudo registrar la auditoría', $exception->getMessage());
                }

                $this->assertSame($nivel, DB::transactionLevel());
                $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'nombre' => 'Original']);
            }
        } finally {
            DB::statement('DROP TRIGGER IF EXISTS bloquear_auditoria_test');
        }

        Log::shouldHaveReceived('critical')->twice();
    }

    public function test_resumen_de_cotizacion_calcula_costos_y_comisiones_en_centimos(): void
    {
        $resumen = CotizacionResumenCalculator::calcular(collect([
            ['cantidad' => '3', 'precio_venta_unit' => '0.10', 'precio_compra_unit' => '0.01', 'igv_compra' => '0.01'],
        ]), [
            'precio_incluye_igv' => false, 'pct_igv' => '18', 'pct_retencion' => '10',
            'pct_comision_venta' => '10', 'pct_comision_ganancia' => '10',
        ], '0.01');

        $this->assertSame('0.30', $resumen['subtotal']);
        $this->assertSame('0.05', $resumen['igv']);
        $this->assertSame('0.03', $resumen['retencion']);
        $this->assertSame('0.03', $resumen['compra_activa']);
        $this->assertSame('0.05', $resumen['compra_fija']);
        $this->assertSame('0.03', $resumen['comision_venta']);
        $this->assertSame('0.03', $resumen['comision_ganancia']);
    }

    public function test_foto_permanece_si_borrado_de_producto_falla_por_documentos(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('productos/foto.png', 'contenido');
        $cliente = Cliente::create(['nombre' => 'Cliente foto', 'ruc' => '20555555555']);
        $producto = Producto::create([
            'nombre' => 'Producto foto', 'sku' => 'FOTO-001', 'imagen' => 'productos/foto.png',
            'puede_venderse' => true,
        ]);
        $venta = Venta::create(['cliente_id' => $cliente->id, 'fecha' => '2026-10-03', 'total' => 0]);
        DB::table('venta_detalle')->insert([
            'venta_id' => $venta->id, 'producto_id' => $producto->id,
            'cantidad' => 1, 'precio_venta_unit' => 0, 'precio_compra_unit' => 0,
        ]);

        try {
            ImagenUpload::eliminarModelo($producto);
            $this->fail('El borrado debió fallar por clave foránea.');
        } catch (QueryException) {
            $this->assertTrue(Storage::disk('public')->exists('productos/foto.png'));
            $this->assertDatabaseHas('productos', ['id' => $producto->id]);
        }
    }
}
