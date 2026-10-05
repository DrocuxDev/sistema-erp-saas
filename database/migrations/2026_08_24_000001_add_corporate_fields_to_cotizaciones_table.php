<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->string('serie', 10)->default('C001')->after('orden_compra_cliente_id');
            $table->unsignedInteger('correlativo')->default(0)->after('serie');
            $table->string('codigo', 30)->nullable()->after('correlativo');
            $table->unsignedInteger('version')->default(1)->after('codigo');
            $table->enum('tipo_cotizacion', ['producto', 'servicio'])->default('producto')->after('version');
            $table->string('asunto', 255)->nullable()->after('tipo_cotizacion');
            $table->string('representante', 150)->nullable()->after('asunto');
            $table->string('plazo_entrega', 150)->nullable()->default('2 a 5 días hábiles tras recepción de O/C')->after('observaciones');
            $table->string('forma_pago', 150)->nullable()->default('30 días calendario previa conformidad y factura')->after('plazo_entrega');
            $table->string('logistica', 255)->nullable()->default('Entrega en instalaciones del cliente / Lima Metropolitana')->after('forma_pago');
            $table->string('validez_oferta', 100)->nullable()->default('15 días calendario')->after('logistica');

            $table->index(['serie', 'correlativo'], 'idx_cotizaciones_serie_correlativo');
        });

        // Actualizar registros existentes para asignarles código y serie
        $cotizaciones = DB::table('cotizaciones')->orderBy('id')->get();
        $correlativo = 0;
        foreach ($cotizaciones as $cot) {
            DB::table('cotizaciones')->where('id', $cot->id)->update([
                'serie' => 'C001',
                'correlativo' => $correlativo,
                'codigo' => 'C001-'.$correlativo,
                'version' => $cot->version ?? 1,
                'tipo_cotizacion' => 'producto',
                'asunto' => 'COTIZACIÓN DE EQUIPOS Y SERVICIOS TI',
            ]);
            $correlativo++;
        }
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropIndex('idx_cotizaciones_serie_correlativo');
            $table->dropColumn([
                'serie',
                'correlativo',
                'codigo',
                'version',
                'tipo_cotizacion',
                'asunto',
                'representante',
                'plazo_entrega',
                'forma_pago',
                'logistica',
                'validez_oferta',
            ]);
        });
    }
};
