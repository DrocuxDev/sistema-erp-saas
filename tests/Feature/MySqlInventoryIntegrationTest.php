<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\SerieDocumento;
use App\Models\User;
use App\Models\Venta;
use App\Services\InventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MySqlInventoryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private string $expectedDatabase;

    protected function setUp(): void
    {
        if (getenv('RUN_MYSQL_INTEGRATION_TESTS') !== '1') {
            $this->markTestSkipped('Configura RUN_MYSQL_INTEGRATION_TESTS=1 para ejecutar sobre una base MySQL desechable.');
        }

        if (getenv('DB_CONNECTION') !== 'mysql') {
            $this->fail('La integración de inventario requiere DB_CONNECTION=mysql.');
        }

        $database = (string) getenv('DB_DATABASE');
        $databaseUrl = (string) getenv('DB_URL');
        if ($databaseUrl !== '') {
            $database = ltrim((string) parse_url($databaseUrl, PHP_URL_PATH), '/');
        }

        if (! str_ends_with($database, '_test')) {
            $this->fail('La integración solo puede ejecutarse sobre una base cuyo nombre termine en _test.');
        }

        $this->expectedDatabase = $database;

        parent::setUp();
    }

    public function beforeRefreshingDatabase(): void
    {
        // RefreshDatabase puede ejecutar migrate:fresh: validar la conexión efectiva antes.
        $connection = DB::connection();
        $this->assertSame('mysql', $connection->getDriverName());
        $database = (string) $connection->getDatabaseName();
        $this->assertTrue(str_ends_with($database, '_test'), 'La base efectiva debe terminar en _test.');
        $this->assertSame($this->expectedDatabase, $database, 'Laravel debe usar la base de pruebas solicitada.');
        $actual = $connection->selectOne('SELECT DATABASE() AS database_name')->database_name;
        $this->assertSame($database, $actual, 'La conexión no corresponde a la base de pruebas configurada.');
    }

    public function test_migration_quita_triggers_legacy_y_el_servicio_actualiza_stock_una_vez(): void
    {
        $triggers = DB::select(
            'SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE() AND EVENT_OBJECT_TABLE = ?',
            ['movimientos_inventario'],
        );

        $nombres = array_map(fn ($trigger) => $trigger->TRIGGER_NAME, $triggers);
        $this->assertNotContains('trg_movimiento_stock', $nombres);
        $this->assertNotContains('trg_movimiento_valida', $nombres);

        $producto = Producto::create([
            'nombre' => 'Producto prueba MySQL',
            'sku' => 'MYSQL-STOCK-TEST',
            'stock_actual' => 2,
        ]);

        DB::transaction(fn () => app(InventarioService::class)->registrar(
            $producto->id,
            'entrada',
            '5.00',
            'ajuste',
            null,
            now()->toDateString(),
        ));

        $this->assertSame('7.00', $producto->fresh()->stock_actual);
        $this->assertSame(1, DB::table('movimientos_inventario')->where('producto_id', $producto->id)->count());
    }

    public function test_operaciones_simultaneas_respetan_stock_comprobante_y_correlativo(): void
    {
        $producto = Producto::create(['nombre' => 'Última unidad', 'sku' => 'MYSQL-CONC-STOCK', 'stock_actual' => 1]);
        $cliente = Cliente::create(['nombre' => 'Cliente concurrente', 'ruc' => '20911111111']);
        $venta = Venta::create(['cliente_id' => $cliente->id, 'fecha' => now()->toDateString(), 'total' => 0]);
        // Las migraciones ya crean las series predeterminadas.
        $this->assertNotNull(SerieDocumento::where('tipo_documento', 'factura')->where('activo', true)->first());
        $serieCotizacion = SerieDocumento::where('tipo_documento', 'cotizacion')->where('activo', true)->firstOrFail();
        $correlativoInicial = $serieCotizacion->correlativo_actual;

        // Los procesos secundarios necesitan ver los datos preparados en otra conexión.
        DB::commit();

        [$primero, $segundo] = $this->ejecutarPar('stock', $producto->id);
        $this->assertTrue($primero['ok']);
        $this->assertFalse($segundo['ok'], 'Stock: '.json_encode([$primero, $segundo]));
        $this->assertSame('0.00', $producto->fresh()->stock_actual);
        $this->assertSame(1, DB::table('movimientos_inventario')->where('producto_id', $producto->id)->count());

        [$primero, $segundo] = $this->ejecutarPar('comprobante', $venta->id);
        $this->assertTrue($primero['ok']);
        $this->assertFalse($segundo['ok'], 'Comprobante: '.json_encode([$primero, $segundo]));
        $this->assertSame(1, DB::table('comprobantes_venta')->where('venta_id', $venta->id)->where('estado', 'emitido')->count());

        [$primero, $segundo] = $this->ejecutarPar('cotizacion', $serieCotizacion->id);
        $this->assertTrue($primero['ok'], 'Cotización primer proceso: '.json_encode($primero));
        $this->assertTrue($segundo['ok'], 'Cotización segundo proceso: '.json_encode($segundo));
        $this->assertNotSame($primero['resultado'], $segundo['resultado']);
        $this->assertSame($correlativoInicial + 2, $serieCotizacion->fresh()->correlativo_actual);
    }

    public function test_devoluciones_simultaneas_no_superan_la_cantidad_vendida(): void
    {
        $usuario = User::factory()->create();
        $cliente = Cliente::create(['nombre' => 'Cliente devolución concurrente', 'ruc' => '20911111112']);
        $producto = Producto::create([
            'nombre' => 'Producto devuelto concurrente', 'sku' => 'MYSQL-CONC-RETURN',
            'stock_actual' => 0, 'puede_venderse' => true,
        ]);
        $venta = Venta::create([
            'cliente_id' => $cliente->id,
            'usuario_id' => $usuario->id,
            'fecha' => now()->toDateString(),
            'subtotal' => '100.00', 'igv' => '18.00', 'total' => '118.00',
            'precio_incluye_igv' => false, 'pct_igv' => '18.00', 'pct_retencion' => '0.00', 'retencion' => '0.00',
        ]);
        $venta->detalles()->create([
            'producto_id' => $producto->id, 'cantidad' => '1.00',
            'precio_venta_unit' => '100.00', 'precio_compra_unit' => '50.00',
        ]);

        DB::commit();

        [$primero, $segundo] = $this->ejecutarPar('devolucion', $venta->id);
        $this->assertTrue($primero['ok'], json_encode($primero));
        $this->assertFalse($segundo['ok'], json_encode($segundo));
        $this->assertSame(1, DB::table('devoluciones_venta')->where('venta_id', $venta->id)->count());
        $this->assertSame('1.00', $producto->fresh()->stock_actual);
    }

    public function test_devoluciones_de_compra_simultaneas_no_superan_la_cantidad_recibida(): void
    {
        $usuario = User::factory()->create();
        $proveedor = Proveedor::create(['nombre' => 'Proveedor devolución concurrente', 'ruc' => '20611111112']);
        $producto = Producto::create([
            'nombre' => 'Producto compra concurrente', 'sku' => 'MYSQL-CONC-PURCHASE-RETURN',
            'stock_actual' => 1, 'puede_comprarse' => true,
        ]);
        $compra = Compra::create([
            'proveedor_id' => $proveedor->id,
            'usuario_id' => $usuario->id,
            'fecha' => now()->toDateString(),
            'total' => '100.00',
        ]);
        $compra->detalles()->create([
            'producto_id' => $producto->id,
            'cantidad' => '1.00',
            'precio_compra_unit' => '100.00',
        ]);

        DB::commit();

        [$primero, $segundo] = $this->ejecutarPar('devolucion_compra', $compra->id);
        $this->assertTrue($primero['ok'], json_encode($primero));
        $this->assertFalse($segundo['ok'], json_encode($segundo));
        $this->assertSame(1, DB::table('devoluciones_compra')->where('compra_id', $compra->id)->count());
        $this->assertSame('0.00', $producto->fresh()->stock_actual);
    }

    public function test_cobros_y_pagos_simultaneos_no_exceden_el_saldo(): void
    {
        $usuario = User::factory()->create();
        $cliente = Cliente::create(['nombre' => 'Cliente cobro concurrente', 'ruc' => '20911111113']);
        $proveedor = Proveedor::create(['nombre' => 'Proveedor pago concurrente', 'ruc' => '20611111113']);
        $venta = Venta::create([
            'cliente_id' => $cliente->id, 'usuario_id' => $usuario->id,
            'fecha' => now()->toDateString(), 'subtotal' => '100.00', 'igv' => '0.00',
            'total' => '100.00', 'retencion' => '0.00',
        ]);
        $compra = Compra::create([
            'proveedor_id' => $proveedor->id, 'usuario_id' => $usuario->id,
            'fecha' => now()->toDateString(), 'total' => '100.00',
        ]);

        DB::commit();

        [$primerCobro, $segundoCobro] = $this->ejecutarPar('cobro', $venta->id);
        $this->assertTrue($primerCobro['ok'], json_encode($primerCobro));
        $this->assertFalse($segundoCobro['ok'], json_encode($segundoCobro));
        $this->assertSame(1, DB::table('cobros_venta')->where('venta_id', $venta->id)->count());

        [$primerPago, $segundoPago] = $this->ejecutarPar('pago', $compra->id);
        $this->assertTrue($primerPago['ok'], json_encode($primerPago));
        $this->assertFalse($segundoPago['ok'], json_encode($segundoPago));
        $this->assertSame(1, DB::table('pagos_compra')->where('compra_id', $compra->id)->count());
    }

    /** @return array{0: array, 1: array} */
    private function ejecutarPar(string $escenario, int $registroId): array
    {
        $senal = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('erp-mysql-', true).'.gate';
        $comando = fn (string $retener) => [PHP_BINARY, base_path('tests/Support/ConcurrentMySqlWorker.php'), $escenario, (string) $registroId, $senal, $retener];
        $descriptores = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $primero = proc_open($comando('1'), $descriptores, $tuberiasPrimero, base_path());
        $this->assertIsResource($primero);

        try {
            $limite = microtime(true) + 10;
            while (! is_file($senal) && microtime(true) < $limite) {
                usleep(20000);
            }
            $this->assertFileExists($senal, 'El primer proceso no adquirió el bloqueo a tiempo.');

            $segundo = proc_open($comando('0'), $descriptores, $tuberiasSegundo, base_path());
            $this->assertIsResource($segundo);

            return [
                $this->resultadoProceso($primero, $tuberiasPrimero),
                $this->resultadoProceso($segundo, $tuberiasSegundo),
            ];
        } finally {
            if (is_file($senal)) {
                unlink($senal);
            }
        }
    }

    private function resultadoProceso($proceso, array $tuberias): array
    {
        $salida = stream_get_contents($tuberias[1]);
        $errores = stream_get_contents($tuberias[2]);
        fclose($tuberias[1]);
        fclose($tuberias[2]);
        $codigo = proc_close($proceso);
        $this->assertSame(0, $codigo, $errores);

        return json_decode($salida, true, 512, JSON_THROW_ON_ERROR);
    }
}
