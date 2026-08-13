<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_proveedor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->decimal('precio_compra', 12, 2);
            $table->date('fecha_actualizacion');
            $table->unique(['producto_id', 'proveedor_id'], 'uq_producto_proveedor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_proveedor');
    }
};
