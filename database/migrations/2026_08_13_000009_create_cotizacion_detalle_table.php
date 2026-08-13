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
        Schema::create('cotizacion_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->string('descripcion', 255)->nullable();
            $table->string('unidad_medida', 20)->default('UND');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_venta_unit', 12, 2);
            $table->decimal('precio_compra_unit', 12, 2);
            $table->decimal('igv_compra', 12, 2)->default(0);
            $table->index('producto_id', 'idx_cotizacion_detalle_producto');
        });

        if (MySqlSchema::isMySql()) {
            DB::statement('ALTER TABLE cotizacion_detalle ADD precio_venta_total DECIMAL(12,2) GENERATED ALWAYS AS (cantidad * precio_venta_unit) STORED');
            DB::statement('ALTER TABLE cotizacion_detalle ADD precio_compra_total DECIMAL(12,2) GENERATED ALWAYS AS ((cantidad * precio_compra_unit) + igv_compra) STORED');
            DB::statement('ALTER TABLE cotizacion_detalle ADD ganancia_linea DECIMAL(12,2) GENERATED ALWAYS AS ((cantidad * precio_venta_unit) - ((cantidad * precio_compra_unit) + igv_compra)) STORED');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizacion_detalle');
    }
};
