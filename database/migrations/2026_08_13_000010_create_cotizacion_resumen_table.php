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
        Schema::create('cotizacion_resumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->unique()->constrained('cotizaciones')->cascadeOnDelete();
            $table->foreignId('configuracion_id')->constrained('configuracion');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('igv', 12, 2);
            $table->decimal('retencion', 12, 2);
            $table->decimal('compra_activa', 12, 2);
            $table->decimal('compra_fija', 12, 2);
            $table->decimal('no_utilizable', 12, 2)->default(0);
            $table->decimal('comision_venta', 12, 2);
            $table->decimal('comision_ganancia', 12, 2);
            $table->timestamp('created_at')->useCurrent();
        });

        if (MySqlSchema::isMySql()) {
            DB::statement('ALTER TABLE cotizacion_resumen ADD total_facturar DECIMAL(12,2) GENERATED ALWAYS AS ((subtotal + igv) - retencion) STORED');
            DB::statement('ALTER TABLE cotizacion_resumen ADD ganancia DECIMAL(12,2) GENERATED ALWAYS AS (subtotal - compra_fija) STORED');
            DB::statement('ALTER TABLE cotizacion_resumen ADD comision_total DECIMAL(12,2) GENERATED ALWAYS AS (comision_venta + comision_ganancia) STORED');
            DB::statement('ALTER TABLE cotizacion_resumen ADD saldo_a_favor DECIMAL(12,2) GENERATED ALWAYS AS (((subtotal + igv) - retencion) - compra_fija) STORED');
            DB::statement('ALTER TABLE cotizacion_resumen ADD rentabilidad DECIMAL(12,2) GENERATED ALWAYS AS (((((subtotal + igv) - retencion) - compra_fija) - comision_venta) - comision_ganancia) STORED');

            DB::unprepared('
                CREATE TRIGGER trg_resumen_valida_subtotal BEFORE INSERT ON cotizacion_resumen FOR EACH ROW
                BEGIN
                    DECLARE suma_real DECIMAL(12,2);
                    SELECT COALESCE(SUM(precio_venta_total), 0) INTO suma_real
                    FROM cotizacion_detalle WHERE cotizacion_id = NEW.cotizacion_id;
                    IF ABS(NEW.subtotal - suma_real) > 0.01 THEN
                        SIGNAL SQLSTATE \'45000\' SET MESSAGE_TEXT = \'subtotal no coincide con la suma real de cotizacion_detalle\';
                    END IF;
                END
            ');
        }
    }

    public function down(): void
    {
        if (MySqlSchema::isMySql()) {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_resumen_valida_subtotal');
        }

        Schema::dropIfExists('cotizacion_resumen');
    }
};
