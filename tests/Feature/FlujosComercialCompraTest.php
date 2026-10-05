<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Compra;
use App\Models\ComprobanteVenta;
use App\Models\GuiaRemision;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Venta;
use Database\Seeders\ConfiguracionSeeder;
use Database\Seeders\RolesAndModulosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujosComercialCompraTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $vendedor;

    protected Cliente $cliente;

    protected Proveedor $proveedor;

    protected Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ConfiguracionSeeder::class);
        $this->seed(RolesAndModulosSeeder::class);

        $this->admin = User::where('is_superadmin', true)->first();
        $this->vendedor = User::where('email', 'ventas@jhardsystex.com')->first();

        $this->cliente = Cliente::create([
            'nombre' => 'CLIENTE E2E SAC', 'ruc' => '20500000001',
            'direccion' => 'Av. Prueba 123', 'estado' => 'activo',
        ]);

        $this->proveedor = Proveedor::create([
            'nombre' => 'PROVEEDOR E2E SAC', 'ruc' => '20600000001',
            'direccion' => 'Av. Proveedor 456', 'estado' => 'activo',
        ]);

        $this->producto = Producto::create([
            'nombre' => 'PRODUCTO E2E', 'sku' => 'E2E-001', 'unidad_medida' => 'UND',
            'stock_actual' => 50, 'stock_minimo' => 5,
            'precio_base_compra' => 100, 'precio_base_venta' => 150,
            'puede_venderse' => true, 'puede_comprarse' => true, 'estado' => 'activo',
        ]);
    }

    public function test_flujo_comercial_completo_venta_comprobante_guia(): void
    {
        // 1. Venta directa (descuenta stock 50 -> 48)
        $this->actingAs($this->admin)->post(route('ventas.store'), [
            'cliente_id' => $this->cliente->id,
            'fecha' => now()->toDateString(),
            'lineas' => [[
                'producto_id' => $this->producto->id, 'cantidad' => 2,
                'precio_venta_unit' => 150, 'precio_compra_unit' => 100,
            ]],
        ])->assertRedirect();

        $venta = Venta::latest('id')->first();
        $this->assertNotNull($venta);
        // El descuento físico se aplica en la misma transacción en ambos motores.
        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $this->producto->id,
            'tipo' => 'salida',
            'referencia_tipo' => 'venta',
            'referencia_id' => $venta->id,
        ]);
        $this->assertEquals(48, (int) $this->producto->fresh()->stock_actual);

        // 2. Emitir comprobante (factura hereda totales, serie F001)
        $this->actingAs($this->admin)->post(route('comprobantes.store'), [
            'venta_id' => $venta->id, 'tipo' => 'factura',
            'fecha_emision' => now()->toDateString(),
        ])->assertRedirect();

        $comprobante = ComprobanteVenta::latest('id')->first();
        $this->assertNotNull($comprobante);
        $this->assertEquals('F001', $comprobante->serie);
        $this->assertEquals((float) $venta->total, (float) $comprobante->total);

        // No se permite segundo comprobante vigente para la misma venta
        $this->actingAs($this->admin)->post(route('comprobantes.store'), [
            'venta_id' => $venta->id, 'tipo' => 'boleta',
            'fecha_emision' => now()->toDateString(),
        ])->assertSessionHasErrors('venta_id');

        // 3. Emitir guía desde la venta (serie T001, precarga 1 línea)
        $this->actingAs($this->admin)->post(route('guias-remision.store'), [
            'venta_id' => $venta->id,
            'fecha_traslado' => now()->toDateString(),
            'motivo_traslado' => 'venta',
            'punto_partida' => 'Almacén Central',
            'punto_llegada' => 'Av. Prueba 123',
            'lineas' => [[
                'producto_id' => $this->producto->id, 'cantidad' => 2, 'unidad_medida' => 'UND',
            ]],
        ])->assertRedirect();

        $guia = GuiaRemision::latest('id')->first();
        $this->assertNotNull($guia);
        $this->assertEquals('T001', $guia->serie);
        // La guía no genera movimientos de inventario
        $this->assertDatabaseMissing('movimientos_inventario', [
            'referencia_tipo' => 'guia_remision',
            'referencia_id' => $guia->id,
        ]);

        // 4. PDFs responden 200 con contenido PDF
        foreach ([
            route('comprobantes.pdf', $comprobante),
            route('guias-remision.pdf', $guia),
            route('ventas.pdf', $venta),
        ] as $pdfRoute) {
            $resp = $this->actingAs($this->admin)->get($pdfRoute);
            $resp->assertOk();
            $this->assertStringContainsString('application/pdf', $resp->headers->get('Content-Type'));
        }

        // 5. Anular comprobante y guía
        $this->actingAs($this->admin)->patch(route('comprobantes.anular', $comprobante))->assertRedirect();
        $this->actingAs($this->admin)->patch(route('guias-remision.anular', $guia))->assertRedirect();
        $this->assertEquals('anulado', $comprobante->fresh()->estado);
        $this->assertEquals('anulada', $guia->fresh()->estado);
    }

    public function test_flujo_compra_oc_recepcion_aumenta_stock(): void
    {
        // 1. O/C a proveedor y aprobación
        $this->actingAs($this->admin)->post(route('ordenes-compra.store'), [
            'proveedor_id' => $this->proveedor->id,
            'fecha' => now()->toDateString(),
            'lineas' => [[
                'producto_id' => $this->producto->id, 'cantidad' => 10, 'precio_compra' => 100,
            ]],
        ])->assertRedirect();

        $oc = OrdenCompra::latest('id')->first();
        $this->assertEquals('pendiente', $oc->estado);

        $this->actingAs($this->admin)->patch(route('ordenes-compra.aprobar', $oc))->assertRedirect();
        $this->assertEquals('aprobada', $oc->fresh()->estado);

        // 2. Recepción precargada desde O/C aprobada
        $respCreate = $this->actingAs($this->admin)->get(route('compras.create', ['orden_compra_id' => $oc->id]));
        $respCreate->assertOk();

        $this->actingAs($this->admin)->post(route('compras.store'), [
            'proveedor_id' => $this->proveedor->id,
            'orden_compra_id' => $oc->id,
            'fecha' => now()->toDateString(),
            'lineas' => [[
                'producto_id' => $this->producto->id, 'cantidad' => 10, 'precio_compra_unit' => 100,
            ]],
        ])->assertRedirect();

        $compra = Compra::latest('id')->first();
        $this->assertNotNull($compra);
        $this->assertEquals(1000.00, (float) $compra->total);
        $this->assertEquals('recibida', $oc->fresh()->estado);
        $this->assertEquals(60, (int) $this->producto->fresh()->stock_actual);

        // Kardex registró la entrada
        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $this->producto->id,
            'tipo' => 'entrada',
            'referencia_tipo' => 'compra',
            'referencia_id' => $compra->id,
        ]);

        $this->actingAs($this->admin)->get(route('compras.show', $compra))->assertOk();
    }

    public function test_seguridad_rutas_y_permisos_nuevos_modulos(): void
    {
        // Vendedor (rol ventas) no tiene módulo comprobantes ni compras: 403
        $this->actingAs($this->vendedor)->get(route('comprobantes.index'))->assertForbidden();
        $this->actingAs($this->vendedor)->get(route('compras.index'))->assertForbidden();
        $this->actingAs($this->vendedor)->get(route('guias-remision.index'))->assertForbidden();

        // Admin accede a todo lo nuevo sin 500
        foreach ([
            route('comprobantes.index'), route('comprobantes.create'),
            route('guias-remision.index'), route('guias-remision.create'),
            route('compras.index'), route('compras.create'),
            route('auditoria.index'),
        ] as $ruta) {
            $this->actingAs($this->admin)->get($ruta)->assertOk("Fallo en {$ruta}");
        }
    }
}
