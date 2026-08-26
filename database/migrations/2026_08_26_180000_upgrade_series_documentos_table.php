<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('series_documentos', function (Blueprint $table) {
            $table->string('tipo_documento', 50)->change();
            $table->string('descripcion', 150)->nullable()->after('serie');
            $table->unsignedSmallInteger('longitud_correlativo')->default(6)->after('correlativo_actual');
            $table->boolean('es_predeterminado')->default(true)->after('longitud_correlativo');
            $table->boolean('activo')->default(true)->after('es_predeterminado');
            $table->timestamps();
        });

        // Obtener el correlativo máximo actual de cotizaciones si existen
        $maxCot = 0;
        if (Schema::hasTable('cotizaciones')) {
            $maxCot = (int) DB::table('cotizaciones')->where('serie', 'C001')->max('correlativo');
        }

        $seriesIniciales = [
            [
                'tipo_documento' => 'cotizacion',
                'serie' => 'C001',
                'descripcion' => 'Serie Principal de Cotizaciones',
                'correlativo_actual' => $maxCot,
                'longitud_correlativo' => 6,
                'es_predeterminado' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo_documento' => 'venta',
                'serie' => 'V001',
                'descripcion' => 'Serie Principal de Ventas Directas',
                'correlativo_actual' => 0,
                'longitud_correlativo' => 6,
                'es_predeterminado' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo_documento' => 'orden_compra',
                'serie' => 'OC01',
                'descripcion' => 'Serie Principal de Órdenes de Compra a Proveedores',
                'correlativo_actual' => 0,
                'longitud_correlativo' => 6,
                'es_predeterminado' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo_documento' => 'orden_compra_cliente',
                'serie' => 'OCC01',
                'descripcion' => 'Serie Principal de O/C Recibidas de Clientes',
                'correlativo_actual' => 0,
                'longitud_correlativo' => 6,
                'es_predeterminado' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo_documento' => 'factura',
                'serie' => 'F001',
                'descripcion' => 'Serie Principal de Facturación Electrónica',
                'correlativo_actual' => 0,
                'longitud_correlativo' => 8,
                'es_predeterminado' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo_documento' => 'boleta',
                'serie' => 'B001',
                'descripcion' => 'Serie Principal de Boletas de Venta',
                'correlativo_actual' => 0,
                'longitud_correlativo' => 8,
                'es_predeterminado' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo_documento' => 'nota_venta',
                'serie' => 'NV01',
                'descripcion' => 'Serie Principal de Notas de Venta Internas',
                'correlativo_actual' => 0,
                'longitud_correlativo' => 6,
                'es_predeterminado' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo_documento' => 'guia_remision',
                'serie' => 'T001',
                'descripcion' => 'Serie Principal de Guías de Remisión Remitente',
                'correlativo_actual' => 0,
                'longitud_correlativo' => 8,
                'es_predeterminado' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($seriesIniciales as $s) {
            DB::table('series_documentos')->updateOrInsert(
                ['tipo_documento' => $s['tipo_documento'], 'serie' => $s['serie']],
                $s
            );
        }
    }

    public function down(): void
    {
        Schema::table('series_documentos', function (Blueprint $table) {
            $table->dropColumn(['descripcion', 'longitud_correlativo', 'es_predeterminado', 'activo', 'created_at', 'updated_at']);
        });
    }
};
