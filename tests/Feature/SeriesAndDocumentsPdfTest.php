<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Cotizacion;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\SerieDocumento;
use App\Models\User;
use App\Models\Venta;
use Database\Seeders\ConfiguracionSeeder;
use Database\Seeders\RolesAndModulosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeriesAndDocumentsPdfTest extends TestCase
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
            'nombre' => 'TECNOLOGIA PERUANA SAC',
            'ruc' => '20556677881',
            'direccion' => 'Av. Javier Prado Este 2500',
            'estado' => 'activo',
        ]);

        $this->proveedor = Proveedor::create([
            'nombre' => 'DISTRIBUIDOR GLOBAL HARDWARE',
            'ruc' => '20609988771',
            'direccion' => 'Av. República de Panamá 3500',
            'estado' => 'activo',
        ]);

        $this->producto = Producto::create([
            'nombre' => 'SWITCH CISCO CATALYST 24P',
            'sku' => 'SW-CISCO-24P',
            'unidad_medida' => 'UND',
            'stock_actual' => 50,
            'stock_minimo' => 5,
            'precio_base_compra' => 1200,
            'precio_base_venta' => 1800,
            'puede_venderse' => true,
            'puede_comprarse' => true,
            'estado' => 'activo',
        ]);
    }

    public function test_admin_can_view_and_create_series_documentos(): void
    {
        $response = $this->actingAs($this->admin)->get(route('series-documentos.index'));
        $response->assertOk();
        $response->assertSee('Control de Series y Correlativos');

        $responseCreate = $this->actingAs($this->admin)->post(route('series-documentos.store'), [
            'tipo_documento' => 'venta',
            'serie' => 'V002',
            'descripcion' => 'Serie de Ventas Sucursal Norte',
            'correlativo_actual' => 0,
            'longitud_correlativo' => 6,
            'es_predeterminado' => false,
            'activo' => true,
        ]);

        $responseCreate->assertRedirect(route('series-documentos.index'));
        $this->assertDatabaseHas('series_documentos', [
            'tipo_documento' => 'venta',
            'serie' => 'V002',
        ]);
    }

    public function test_venta_uses_dynamic_serie_and_generates_pdf(): void
    {
        $responseStore = $this->actingAs($this->admin)->post(route('ventas.store'), [
            'cliente_id' => $this->cliente->id,
            'fecha' => now()->toDateString(),
            'lineas' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 2,
                    'precio_venta_unit' => 1800,
                    'precio_compra_unit' => 1200,
                ]
            ]
        ]);

        $venta = Venta::latest('id')->first();
        $this->assertNotNull($venta);
        $this->assertEquals('V001', $venta->serie);
        $this->assertStringStartsWith('V001-', $venta->codigo_formateado);
        $this->assertEquals(3600.00, (float) $venta->total);

        // Probar visualización de detalle y PDF
        $responseShow = $this->actingAs($this->admin)->get(route('ventas.show', $venta));
        $responseShow->assertOk();
        $responseShow->assertSee($venta->codigo_formateado);

        $responsePdf = $this->actingAs($this->admin)->get(route('ventas.pdf', $venta));
        $responsePdf->assertOk();
        $this->assertStringContainsString('application/pdf', $responsePdf->headers->get('Content-Type'));
    }

    public function test_orden_compra_uses_dynamic_serie_and_generates_pdf(): void
    {
        $responseStore = $this->actingAs($this->admin)->post(route('ordenes-compra.store'), [
            'proveedor_id' => $this->proveedor->id,
            'fecha' => now()->toDateString(),
            'forma_pago' => '30 días crédito',
            'plazo_entrega' => '3 días hábiles',
            'lugar_entrega' => 'Almacén Central',
            'lineas' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 5,
                    'precio_compra' => 1200,
                ]
            ]
        ]);

        $ordenCompra = OrdenCompra::latest('id')->first();
        $this->assertNotNull($ordenCompra);
        $this->assertEquals('OC01', $ordenCompra->serie);
        $this->assertStringStartsWith('OC01-', $ordenCompra->codigo_formateado);
        $this->assertEquals(6000.00, (float) $ordenCompra->subtotal);

        // Probar visualización de detalle y PDF
        $responseShow = $this->actingAs($this->admin)->get(route('ordenes-compra.show', $ordenCompra));
        $responseShow->assertOk();
        $responseShow->assertSee($ordenCompra->codigo_formateado);

        $responsePdf = $this->actingAs($this->admin)->get(route('ordenes-compra.pdf', $ordenCompra));
        $responsePdf->assertOk();
        $this->assertStringContainsString('application/pdf', $responsePdf->headers->get('Content-Type'));
    }
}
