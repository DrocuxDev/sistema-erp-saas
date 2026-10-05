<?php

use App\Support\Database\MySqlSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (MySqlSchema::isMySql()) {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_resumen_valida_subtotal');
            DB::unprepared("CREATE TRIGGER trg_resumen_valida_subtotal BEFORE INSERT ON cotizacion_resumen FOR EACH ROW
                BEGIN
                    DECLARE suma_real DECIMAL(12,2);
                    DECLARE incluye_igv BOOLEAN;
                    SELECT precio_incluye_igv INTO incluye_igv FROM cotizaciones WHERE id = NEW.cotizacion_id;
                    SELECT COALESCE(SUM(precio_venta_total), 0) INTO suma_real FROM cotizacion_detalle WHERE cotizacion_id = NEW.cotizacion_id;
                    IF ABS((CASE WHEN incluye_igv THEN NEW.subtotal + NEW.igv ELSE NEW.subtotal END) - suma_real) > 0.01 THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Importe de cotizacion no coincide con sus lineas';
                    END IF;
                END");
        }

        foreach (['cotizaciones', 'ventas', 'ordenes_compra'] as $tabla) {
            $duplicado = DB::table($tabla)->select('serie', 'correlativo')
                ->groupBy('serie', 'correlativo')->havingRaw('COUNT(*) > 1')->first();
            if ($duplicado) {
                throw new RuntimeException("Hay correlativos duplicados en {$tabla}; concílielos antes de crear el índice único.");
            }
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                $table->unique(['serie', 'correlativo'], "uq_{$tabla}_serie_correlativo");
            });
        }

        $origenes = [
            'cotizacion' => ['cotizaciones', 'correlativo'],
            'venta' => ['ventas', 'correlativo'],
            'orden_compra' => ['ordenes_compra', 'correlativo'],
            'factura' => ['comprobantes_venta', 'numero'],
            'boleta' => ['comprobantes_venta', 'numero'],
            'nota_venta' => ['comprobantes_venta', 'numero'],
            'guia_remision' => ['guias_remision', 'numero'],
        ];
        foreach (DB::table('series_documentos')->get() as $serie) {
            $origen = $origenes[$serie->tipo_documento] ?? null;
            if (! $origen) {
                continue;
            }
            $query = DB::table($origen[0])->where('serie', $serie->serie);
            if (in_array($serie->tipo_documento, ['factura', 'boleta', 'nota_venta'], true)) {
                $query->where('tipo', $serie->tipo_documento);
            }
            $maximo = (int) $query->max($origen[1]);
            if ($maximo > (int) $serie->correlativo_actual) {
                DB::table('series_documentos')->where('id', $serie->id)->update(['correlativo_actual' => $maximo]);
            }
        }
    }

    public function down(): void
    {
        foreach (['cotizaciones', 'ventas', 'ordenes_compra'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                $table->dropUnique("uq_{$tabla}_serie_correlativo");
            });
        }
        if (MySqlSchema::isMySql()) {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_resumen_valida_subtotal');
            DB::unprepared("CREATE TRIGGER trg_resumen_valida_subtotal BEFORE INSERT ON cotizacion_resumen FOR EACH ROW
                BEGIN
                    DECLARE suma_real DECIMAL(12,2);
                    SELECT COALESCE(SUM(precio_venta_total), 0) INTO suma_real FROM cotizacion_detalle WHERE cotizacion_id = NEW.cotizacion_id;
                    IF ABS(NEW.subtotal - suma_real) > 0.01 THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'subtotal no coincide con la suma real de cotizacion_detalle';
                    END IF;
                END");
        }
    }
};
