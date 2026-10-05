<?php

use App\Http\Controllers\ComprobanteVentaController;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\SerieDocumento;
use App\Models\User;
use App\Models\Venta;
use App\Services\DevolucionCompraService;
use App\Services\DevolucionVentaService;
use App\Services\InventarioService;
use App\Services\RegistroFinancieroService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (getenv('RUN_MYSQL_INTEGRATION_TESTS') !== '1' || DB::connection()->getDriverName() !== 'mysql' ||
    ! str_ends_with((string) DB::connection()->getDatabaseName(), '_test')) {
    fwrite(STDERR, 'La prueba concurrente requiere una base MySQL de pruebas.');
    exit(2);
}

[$script, $escenario, $registroId, $senal, $retener] = $argv;

try {
    DB::statement('SET innodb_lock_wait_timeout = 5');
    $resultado = DB::transaction(function () use ($escenario, $registroId, $senal, $retener) {
        if ($escenario === 'cotizacion') {
            $codigo = SerieDocumento::siguienteCodigo('cotizacion')['codigo'];
            if ($retener === '1') {
                file_put_contents($senal, 'bloqueado');
                usleep(800000);
            }

            return $codigo;
        }

        if ($retener === '1') {
            match ($escenario) {
                'stock' => Producto::whereKey($registroId)->lockForUpdate()->firstOrFail(),
                'comprobante' => Venta::whereKey($registroId)->lockForUpdate()->firstOrFail(),
                'devolucion' => Venta::whereKey($registroId)->lockForUpdate()->firstOrFail(),
                'devolucion_compra' => Compra::whereKey($registroId)->lockForUpdate()->firstOrFail(),
                'cobro' => Venta::whereKey($registroId)->lockForUpdate()->firstOrFail(),
                'pago' => Compra::whereKey($registroId)->lockForUpdate()->firstOrFail(),
            };
            file_put_contents($senal, 'bloqueado');
            usleep(800000);
        }

        return match ($escenario) {
            'stock' => tap(app(InventarioService::class)->registrar(
                (int) $registroId, 'salida', '1.00', 'ajuste', null, now()->toDateString()
            ), fn () => null),
            'comprobante' => app(ComprobanteVentaController::class)->store(Request::create('/comprobantes', 'POST', [
                'venta_id' => $registroId,
                'tipo' => 'factura',
                'fecha_emision' => now()->toDateString(),
            ]))->getTargetUrl(),
            'devolucion' => app(DevolucionVentaService::class)->registrar(
                Venta::findOrFail($registroId),
                [
                    'idempotency_key' => (string) Str::uuid(),
                    'fecha' => now()->toDateString(),
                    'motivo' => 'Prueba de concurrencia',
                    'lineas' => [[
                        'venta_detalle_id' => Venta::findOrFail($registroId)->detalles()->firstOrFail()->id,
                        'cantidad' => '1.00',
                    ]],
                ],
                User::query()->value('id')
            )->id,
            'devolucion_compra' => app(DevolucionCompraService::class)->registrar(
                Compra::findOrFail($registroId),
                [
                    'idempotency_key' => (string) Str::uuid(),
                    'fecha' => now()->toDateString(),
                    'motivo' => 'Prueba de concurrencia',
                    'lineas' => [[
                        'compra_detalle_id' => Compra::findOrFail($registroId)->detalles()->firstOrFail()->id,
                        'cantidad' => '1.00',
                    ]],
                ],
                User::query()->value('id')
            )->id,
            'cobro' => app(RegistroFinancieroService::class)->cobro(
                Venta::findOrFail($registroId),
                [
                    'idempotency_key' => (string) Str::uuid(),
                    'tipo' => 'cobro', 'fecha' => now()->toDateString(),
                    'monto' => '100.00', 'medio' => 'efectivo',
                ],
                User::query()->value('id')
            )->id,
            'pago' => app(RegistroFinancieroService::class)->pago(
                Compra::findOrFail($registroId),
                [
                    'idempotency_key' => (string) Str::uuid(),
                    'tipo' => 'pago', 'fecha' => now()->toDateString(),
                    'monto' => '100.00', 'medio' => 'efectivo',
                ],
                User::query()->value('id')
            )->id,
        };
    });

    echo json_encode(['ok' => true, 'resultado' => $resultado], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_THROW_ON_ERROR);
}
