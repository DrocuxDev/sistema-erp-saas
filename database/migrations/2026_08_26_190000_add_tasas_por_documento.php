<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizacion_resumen', function (Blueprint $table) {
            $table->decimal('pct_igv', 5, 2)->default(18.00)->after('configuracion_id');
            $table->decimal('pct_retencion', 5, 2)->default(3.00)->after('pct_igv');
            $table->decimal('pct_comision_venta', 5, 2)->default(5.00)->after('pct_retencion');
            $table->decimal('pct_comision_ganancia', 5, 2)->default(10.00)->after('pct_comision_venta');
        });

        // Backfill existing rows with their config values
        if (\App\Support\Database\MySqlSchema::isMySql()) {
            DB::statement('
                UPDATE cotizacion_resumen cr
                JOIN configuracion c ON cr.configuracion_id = c.id
                SET cr.pct_igv = c.pct_igv,
                    cr.pct_retencion = c.pct_retencion,
                    cr.pct_comision_venta = c.pct_comision_venta,
                    cr.pct_comision_ganancia = c.pct_comision_ganancia
            ');
        }
    }

    public function down(): void
    {
        Schema::table('cotizacion_resumen', function (Blueprint $table) {
            $table->dropColumn(['pct_igv', 'pct_retencion', 'pct_comision_venta', 'pct_comision_ganancia']);
        });
    }
};
