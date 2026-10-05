<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $trabajador;

    protected Cliente $cliente;

    protected Proveedor $proveedor;

    protected Producto $producto;

    protected Cotizacion $cotizacion;

    protected function setUp(): void
    {
        parent::setUp();

        Configuracion::create([
            'nombre' => 'General',
            'razon_social' => 'JHARDSYSTEX S.A.C.',
            'ruc' => '20601245781',
            'direccion' => 'Av. Principal N° 450 Int. 302, Lima',
            'telefono' => '(+51) 987 654 321 - (01) 748-2300',
            'email' => 'ventas@jhardsystex.com | info@jhardsystex.com',
            'sitio_web' => 'www.jhardsystex.com',
            'pct_igv' => 18.00,
            'pct_retencion' => 3.00,
            'pct_comision_venta' => 5.00,
            'pct_comision_ganancia' => 10.00,
            'activo' => true,
        ]);

        $this->admin = User::factory()->create([
            'rol' => 'administrador',
        ]);

        $this->trabajador = User::factory()->create([
            'rol' => 'trabajador',
        ]);

        $this->cliente = Cliente::create([
            'nombre' => 'SURPERU S.A.',
            'ruc' => '20512345678',
            'estado' => 'activo',
        ]);

        $this->proveedor = Proveedor::create([
            'nombre' => 'DISTRIBUIDOR TI SAC',
            'ruc' => '20609876543',
            'estado' => 'activo',
        ]);

        $this->producto = Producto::create([
            'nombre' => 'SERVIDOR HPE GEN10',
            'sku' => 'SRV-HPE-G10',
            'unidad_medida' => 'UND',
            'stock_actual' => 10,
            'stock_minimo' => 2,
            'precio_base_compra' => 5000,
            'precio_base_venta' => 7000,
            'puede_venderse' => true,
            'puede_comprarse' => true,
            'estado' => 'activo',
        ]);

        $this->cotizacion = Cotizacion::create([
            'cliente_id' => $this->cliente->id,
            'usuario_id' => $this->admin->id,
            'serie' => 'C001',
            'correlativo' => 0,
            'codigo' => 'C001-0',
            'version' => 1,
            'tipo_cotizacion' => 'producto',
            'asunto' => 'COTIZACION DE PRUEBA',
            'fecha' => now()->toDateString(),
            'estado' => 'pendiente',
        ]);
    }

    public function test_all_admin_modules_can_be_accessed_without_500_errors(): void
    {
        $routes = [
            '/dashboard',
            '/users',
            '/users/create',
            '/proveedores',
            '/proveedores/create',
            '/clientes',
            '/clientes/create',
            '/productos',
            '/productos/create',
            '/inventario/kardex',
            '/inventario/ajuste',
            '/ordenes-compra-cliente',
            '/ordenes-compra-cliente/create',
            '/cotizaciones',
            '/cotizaciones/create',
            '/cotizaciones/'.$this->cotizacion->id,
            '/cotizaciones/'.$this->cotizacion->id.'/edit',
            '/cotizaciones/'.$this->cotizacion->id.'/pdf',
            '/ventas',
            '/ventas/create',
            '/comprobantes',
            '/comprobantes/create',
            '/guias-remision',
            '/guias-remision/create',
            '/ordenes-compra',
            '/compras',
            '/compras/create',
            '/configuracion',
            '/auditoria',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->admin)->get($route);
            $response->assertOk("Failed accessing route {$route}");
        }
    }
}
