<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar serie, correlativo, código, subtotal e IGV a ventas
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('serie', 10)->default('V001')->after('orden_compra_cliente_id');
            $table->unsignedInteger('correlativo')->default(0)->after('serie');
            $table->string('codigo', 30)->nullable()->after('correlativo');
            $table->decimal('subtotal', 12, 2)->default(0)->after('fecha');
            $table->decimal('igv', 12, 2)->default(0)->after('subtotal');
            $table->index(['serie', 'correlativo'], 'idx_ventas_serie_correlativo');
        });

        // 2. Agregar campos corporativos a ordenes_compra a proveedores
        Schema::table('ordenes_compra', function (Blueprint $table) {
            $table->string('serie', 10)->default('OC01')->after('usuario_id');
            $table->unsignedInteger('correlativo')->default(0)->after('serie');
            $table->string('codigo', 30)->nullable()->after('correlativo');
            $table->decimal('subtotal', 12, 2)->default(0)->after('fecha');
            $table->decimal('igv', 12, 2)->default(0)->after('subtotal');
            $table->decimal('total', 12, 2)->default(0)->after('igv');
            $table->string('forma_pago', 150)->nullable()->default('30 días crédito / Factura')->after('observaciones');
            $table->string('plazo_entrega', 150)->nullable()->default('3 a 5 días hábiles')->after('forma_pago');
            $table->string('lugar_entrega', 255)->nullable()->default('Almacén Central JHARDSYSTEX')->after('plazo_entrega');
            $table->index(['serie', 'correlativo'], 'idx_ordenes_compra_serie_correlativo');
        });

        // 3. Actualizar ventas existentes si hubiera alguna
        $ventas = DB::table('ventas')->orderBy('id')->get();
        $corrVenta = 1;
        foreach ($ventas as $v) {
            $subtotal = round($v->total / 1.18, 2);
            $igv = round($v->total - $subtotal, 2);
            $codigo = 'V001-' . str_pad($corrVenta, 6, '0', STR_PAD_LEFT);
            DB::table('ventas')->where('id', $v->id)->update([
                'serie' => 'V001',
                'correlativo' => $corrVenta,
                'codigo' => $codigo,
                'subtotal' => $subtotal,
                'igv' => $igv,
            ]);
            $corrVenta++;
        }
        if ($corrVenta > 1) {
            DB::table('series_documentos')
                ->where('tipo_documento', 'venta')
                ->where('serie', 'V001')
                ->update(['correlativo_actual' => $corrVenta - 1]);
        }

        // 4. Actualizar ordenes de compra existentes si hubiera
        $ordenesCompra = DB::table('ordenes_compra')->orderBy('id')->get();
        $corrOC = 1;
        foreach ($ordenesCompra as $oc) {
            $codigo = 'OC01-' . str_pad($corrOC, 6, '0', STR_PAD_LEFT);
            DB::table('ordenes_compra')->where('id', $oc->id)->update([
                'serie' => 'OC01',
                'correlativo' => $corrOC,
                'codigo' => $codigo,
            ]);
            $corrOC++;
        }
        if ($corrOC > 1) {
            DB::table('series_documentos')
                ->where('tipo_documento', 'orden_compra')
                ->where('serie', 'OC01')
                ->update(['correlativo_actual' => $corrOC - 1]);
        }
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('idx_ventas_serie_correlativo');
            $table->dropColumn(['serie', 'correlativo', 'codigo', 'subtotal', 'igv']);
        });

        Schema::table('ordenes_compra', function (Blueprint $table) {
            $table->dropIndex('idx_ordenes_compra_serie_correlativo');
            $table->dropColumn(['serie', 'correlativo', 'codigo', 'subtotal', 'igv', 'total', 'forma_pago', 'plazo_entrega', 'lugar_entrega']);
        });
    }
};
