<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Database\Seeders\ConfiguracionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CotizacionCorporateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ConfiguracionSeeder::class);
    }

    public function test_crear_cotizacion_genera_codigo_c001_y_version_001(): void
    {
        $user = User::factory()->create(['rol' => 'trabajador']);
        $cliente = Cliente::create([
            'nombre' => 'OPERADORA SURPERU S.A.',
            'ruc' => '20512345678',
            'contacto' => 'Ing. Carlos Mendoza',
        ]);
        $proveedor = Proveedor::create([
            'nombre' => 'GLOBAL TEC S.A.C.',
            'ruc' => '20609876543',
        ]);
        $producto = Producto::create([
            'sku' => 'PROD-001',
            'nombre' => 'SWITCH CISCO CATALYST 24P',
            'unidad_medida' => 'UND',
            'puede_venderse' => true,
            'puede_comprarse' => true,
        ]);

        $payload = [
            'cliente_id' => $cliente->id,
            'tipo_cotizacion' => 'producto',
            'asunto' => 'COTIZACION PROYECTO O_US',
            'representante' => 'Ing. Carlos Mendoza',
            'fecha' => '2026-08-24',
            'plazo_entrega' => '3 días hábiles',
            'forma_pago' => '30 días factura',
            'logistica' => 'Lima Metropolitana',
            'validez_oferta' => '15 días',
            'lineas' => [
                [
                    'producto_id' => $producto->id,
                    'proveedor_id' => $proveedor->id,
                    'descripcion' => 'SWITCH CISCO CATALYST 24P GIGABIT',
                    'unidad_medida' => 'UND',
                    'cantidad' => 2,
                    'precio_venta_unit' => 1500.00,
                    'precio_compra_unit' => 1000.00,
                    'igv_compra' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('cotizaciones.store'), $payload);
        $response->assertRedirect();

        $cotizacion = Cotizacion::first();
        $this->assertNotNull($cotizacion);
        $this->assertEquals('C001', $cotizacion->serie);
        $this->assertEquals(1, $cotizacion->correlativo);
        $this->assertEquals('C001-00000001', $cotizacion->codigo_formateado);
        $this->assertEquals('001', $cotizacion->version_formateada);
        $this->assertEquals('producto', $cotizacion->tipo_cotizacion);
        $this->assertEquals('COTIZACION PROYECTO O_US', $cotizacion->asunto);
        $this->assertEquals('Ing. Carlos Mendoza', $cotizacion->representante);
    }

    public function test_editar_cotizacion_incrementa_version_y_conserva_codigo(): void
    {
        $user = User::factory()->create(['rol' => 'trabajador']);
        $cliente = Cliente::create(['nombre' => 'SURPERU', 'ruc' => '20512345678']);
        $proveedor = Proveedor::create(['nombre' => 'GLOBAL TEC', 'ruc' => '20609876543']);
        $producto = Producto::create([
            'sku' => 'PROD-002',
            'nombre' => 'ROUTER MIKROTIK',
            'puede_venderse' => true,
            'puede_comprarse' => true,
        ]);

        $cotizacion = Cotizacion::create([
            'cliente_id' => $cliente->id,
            'usuario_id' => $user->id,
            'serie' => 'C001',
            'correlativo' => 0,
            'codigo' => 'C001-0',
            'version' => 1,
            'tipo_cotizacion' => 'producto',
            'asunto' => 'VERSION INICIAL',
            'fecha' => '2026-08-24',
            'estado' => 'pendiente',
        ]);

        $updatePayload = [
            'cliente_id' => $cliente->id,
            'tipo_cotizacion' => 'servicio',
            'asunto' => 'PROYECTO SERVICIO REDES',
            'representante' => 'Lic. Roberto Gómez',
            'fecha' => '2026-08-24',
            'lineas' => [
                [
                    'producto_id' => $producto->id,
                    'proveedor_id' => $proveedor->id,
                    'descripcion' => 'INSTALACIÓN Y CONFIGURACIÓN ROUTER',
                    'unidad_medida' => 'SRV',
                    'cantidad' => 1,
                    'precio_venta_unit' => 2000.00,
                    'precio_compra_unit' => 1200.00,
                    'igv_compra' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($user)->put(route('cotizaciones.update', $cotizacion), $updatePayload);
        $response->assertRedirect();

        $cotizacion->refresh();
        $this->assertEquals('C001-0', $cotizacion->codigo_formateado);
        $this->assertEquals(2, $cotizacion->version);
        $this->assertEquals('002', $cotizacion->version_formateada);
        $this->assertEquals('servicio', $cotizacion->tipo_cotizacion);
        $this->assertEquals('PROYECTO SERVICIO REDES', $cotizacion->asunto);
    }

    public function test_vista_pdf_renderiza_correctamente(): void
    {
        $user = User::factory()->create(['rol' => 'trabajador']);
        $cliente = Cliente::create(['nombre' => 'SURPERU', 'ruc' => '20512345678']);
        $proveedor = Proveedor::create(['nombre' => 'GLOBAL TEC', 'ruc' => '20609876543']);
        $producto = Producto::create([
            'sku' => 'PROD-003',
            'nombre' => 'ACCESORIO',
            'puede_venderse' => true,
            'puede_comprarse' => true,
        ]);

        $cotizacion = Cotizacion::create([
            'cliente_id' => $cliente->id,
            'usuario_id' => $user->id,
            'serie' => 'C001',
            'correlativo' => 0,
            'codigo' => 'C001-0',
            'version' => 1,
            'tipo_cotizacion' => 'producto',
            'asunto' => 'COTIZACION TEST PDF',
            'fecha' => '2026-08-24',
            'estado' => 'pendiente',
        ]);

        Configuracion::where('activo', true)->update(['pdf_template_cotizacion' => '@php echo "PLANTILLA_NO_EJECUTADA"; @endphp']);
        $responseHtml = $this->actingAs($user)->get(route('cotizaciones.pdf', ['cotizacion' => $cotizacion, 'html' => 1]));
        $responseHtml->assertOk();
        $responseHtml->assertSee('C001-00000000');
        $responseHtml->assertSee('COTIZACIÓN');
        $responseHtml->assertSee('SURPERU');
        $responseHtml->assertDontSee('PLANTILLA_NO_EJECUTADA');

        $responsePdf = $this->actingAs($user)->get(route('cotizaciones.pdf', $cotizacion));
        $responsePdf->assertOk();
    }

    public function test_crear_cotizacion_con_tasas_personalizadas_por_documento(): void
    {
        $user = User::factory()->create(['rol' => 'trabajador']);
        $cliente = Cliente::create(['nombre' => 'CLIENTE ESPECIAL', 'ruc' => '20100000001']);
        $proveedor = Proveedor::create(['nombre' => 'PROVEEDOR TEST', 'ruc' => '20200000002']);
        $producto = Producto::create([
            'sku' => 'PROD-TASAS',
            'nombre' => 'SERVIDOR DELL R740',
            'unidad_medida' => 'UND',
            'puede_venderse' => true,
            'puede_comprarse' => true,
        ]);

        $payload = [
            'cliente_id' => $cliente->id,
            'tipo_cotizacion' => 'producto',
            'asunto' => 'COTIZACION CON TASAS PERSONALIZADAS',
            'fecha' => '2026-08-26',
            'pct_igv' => 10.00,
            'motivo_excepcion_tributaria' => 'Condición tributaria pactada con el cliente',
            'pct_retencion' => 0.00,
            'pct_comision_venta' => 8.00,
            'pct_comision_ganancia' => 15.00,
            'lineas' => [
                [
                    'producto_id' => $producto->id,
                    'proveedor_id' => $proveedor->id,
                    'descripcion' => 'SERVIDOR DELL R740',
                    'unidad_medida' => 'UND',
                    'cantidad' => 1,
                    'precio_venta_unit' => 10000.00,
                    'precio_compra_unit' => 6000.00,
                    'igv_compra' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('cotizaciones.store'), $payload);
        $response->assertRedirect();

        $cotizacion = Cotizacion::with('resumen')->first();
        $this->assertNotNull($cotizacion);
        $this->assertNotNull($cotizacion->resumen);

        // Validar porcentajes almacenados
        $this->assertEquals(10.00, (float) $cotizacion->resumen->pct_igv);
        $this->assertEquals(0.00, (float) $cotizacion->resumen->pct_retencion);
        $this->assertEquals(8.00, (float) $cotizacion->resumen->pct_comision_venta);
        $this->assertEquals(15.00, (float) $cotizacion->resumen->pct_comision_ganancia);

        // Validar cálculos con las tasas personalizadas:
        // Subtotal: 10,000
        // IGV 10%: 1,000
        // Retención 0%: 0
        // Total facturar: 11,000
        // Ganancia: 10,000 - 6,000 = 4,000
        // Comisión venta 8%: 800
        // Comisión ganancia 15% de 4,000: 600
        $this->assertEquals(10000.00, (float) $cotizacion->resumen->subtotal);
        $this->assertEquals(1000.00, (float) $cotizacion->resumen->igv);
        $this->assertEquals(0.00, (float) $cotizacion->resumen->retencion);
        $this->assertEquals(800.00, (float) $cotizacion->resumen->comision_venta);
        $this->assertEquals(600.00, (float) $cotizacion->resumen->comision_ganancia);
    }
}
