<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Atributos comerciales/logísticos del producto.
     * Todo nullable salvo se_fabrica (bandera con default).
     * Los campos de lista (tipo_*, seguimiento, condicion, envase)
     * son VARCHAR + validación `in:` en el controlador, no ENUM,
     * para poder ampliar opciones sin migrar. No se crean tablas
     * de catálogo: el sistema no tiene maestros para estos conceptos.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('tipo_producto', 30)->nullable()->after('categoria');
            $table->string('tipo_existencia', 50)->nullable()->after('tipo_producto');
            $table->decimal('precio_compra', 12, 2)->nullable()->after('tipo_existencia');
            $table->decimal('precio_venta', 12, 2)->nullable()->after('precio_compra');
            $table->string('marca', 100)->nullable()->after('precio_venta');
            $table->string('modelo', 100)->nullable()->after('marca');
            $table->decimal('stock_maximo', 12, 2)->nullable()->after('stock_minimo');
            $table->decimal('peso_bruto', 12, 3)->nullable()->after('stock_maximo');
            $table->string('seguimiento', 20)->nullable()->after('peso_bruto');
            $table->string('condicion', 30)->nullable()->after('seguimiento');
            $table->string('envase', 50)->nullable()->after('condicion');
            $table->string('tipo_area_produccion', 50)->nullable()->after('envase');
            $table->boolean('se_fabrica')->default(false)->after('tipo_area_produccion');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_producto', 'tipo_existencia', 'precio_compra', 'precio_venta',
                'marca', 'modelo', 'stock_maximo', 'peso_bruto', 'seguimiento',
                'condicion', 'envase', 'tipo_area_produccion', 'se_fabrica',
            ]);
        });
    }
};
