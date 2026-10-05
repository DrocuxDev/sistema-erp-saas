<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Espejo de producto_presentaciones_compra para el lado venta.
     * Tabla separada (no discriminador): la de compra está referenciada
     * por FK desde orden_compra_detalle y compra_detalle, así que
     * reutilizarla para venta contaminaría esas relaciones.
     * Solo informativa/conversión: no se cablea al flujo de ventas.
     */
    public function up(): void
    {
        Schema::create('producto_presentaciones_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('unidad_medida', 20);
            $table->decimal('equivalencia', 12, 4);
            $table->boolean('preferida')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['producto_id', 'unidad_medida'], 'uq_producto_presentacion_venta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_presentaciones_venta');
    }
};
