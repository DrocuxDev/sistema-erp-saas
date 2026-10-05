<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Database\Seeders\ConfiguracionSeeder;
use Database\Seeders\RolesAndModulosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ConfiguracionSeeder::class);
        $this->seed(RolesAndModulosSeeder::class);
    }

    public function test_usuario_con_permiso_puede_acceder_al_dashboard(): void
    {
        $admin = User::where('email', 'admin@jhardsystex.com')->first();
        if (! $admin) {
            $admin = User::factory()->create(['rol' => 'administrador', 'is_superadmin' => true]);
        }

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Panel comercial');
        $response->assertSee('Ventas Totales');
        $response->assertSee('Margen Bruto (Ganancia)');
        $response->assertSee('Margen promedio ponderado');
        $response->assertSee('Facturación Mensual');
        $response->assertSee('Top Productos por Facturación');
    }

    public function test_dashboard_calcula_metricas_correctamente(): void
    {
        $admin = User::where('email', 'admin@jhardsystex.com')->first();
        if (! $admin) {
            $admin = User::factory()->create(['rol' => 'administrador', 'is_superadmin' => true]);
        }

        $cliente = Cliente::create(['nombre' => 'CLIENTE TEST DASHBOARD', 'ruc' => '20123456789']);
        $proveedor = Proveedor::create(['nombre' => 'PROVEEDOR TEST DASHBOARD', 'ruc' => '20987654321']);
        $producto = Producto::create([
            'sku' => 'PROD-DASH',
            'nombre' => 'LAPTOP THINKPAD T14',
            'unidad_medida' => 'UND',
            'puede_venderse' => true,
            'stock_actual' => 2,
            'stock_minimo' => 5, // Activa alerta de stock bajo
        ]);

        $cotizacion = Cotizacion::create([
            'cliente_id' => $cliente->id,
            'usuario_id' => $admin->id,
            'serie' => 'C001',
            'correlativo' => 1,
            'codigo' => 'C001-1',
            'version' => 1,
            'tipo_cotizacion' => 'producto',
            'asunto' => 'VENTA LAPTOPS',
            'fecha' => date('Y-m-d'),
            'estado' => 'facturada',
        ]);

        // Venta de S/ 5000 con costo S/ 3500 -> Ganancia S/ 1500
        $venta = Venta::create([
            'cotizacion_id' => $cotizacion->id,
            'cliente_id' => $cliente->id,
            'usuario_id' => $admin->id,
            'serie' => 'V001',
            'correlativo' => 1,
            'codigo' => 'V001-000001',
            'fecha' => date('Y-m-d'),
            'subtotal' => 5000.00,
            'igv' => 900.00,
            'total' => 5900.00,
        ]);

        VentaDetalle::create([
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'precio_venta_unit' => 5000.00,
            'precio_compra_unit' => 3500.00,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();

        // Verificar datos pasados a la vista
        $response->assertViewHas('totalVentas', 5900.00);
        $response->assertViewHas('gananciaVentas', 1500.00);
        $response->assertViewHas('margenPct', 30.0);
        $response->assertSee('S/ 5,900.00');
        $response->assertSee('S/ 1,500.00');
        $response->assertSee('30.0%');
        $response->assertSee('LAPTOP THINKPAD T14');
    }

    public function test_dashboard_filtros_por_anio_y_mes(): void
    {
        $admin = User::where('email', 'admin@jhardsystex.com')->first();
        if (! $admin) {
            $admin = User::factory()->create(['rol' => 'administrador', 'is_superadmin' => true]);
        }

        $currentYear = (int) date('Y');
        $response = $this->actingAs($admin)->get(route('dashboard', ['anio' => $currentYear, 'mes' => 1]));
        $response->assertOk();
        $response->assertViewHas('anio', $currentYear);
        $response->assertViewHas('mes', 1);
    }
}
