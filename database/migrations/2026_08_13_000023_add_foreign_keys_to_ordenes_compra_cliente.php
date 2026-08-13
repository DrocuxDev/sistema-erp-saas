<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_compra_cliente', function (Blueprint $table) {
            $table->foreign('cotizacion_id')->references('id')->on('cotizaciones');
            $table->foreign('venta_id')->references('id')->on('ventas');
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_compra_cliente', function (Blueprint $table) {
            $table->dropForeign(['cotizacion_id']);
            $table->dropForeign(['venta_id']);
        });
    }
};
