<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('usuario_id')->nullable()->constrained('users');
            $table->date('fecha');
            $table->enum('estado', ['pendiente', 'aprobada', 'recibida', 'anulada'])->default('pendiente');
            $table->string('observaciones', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('fecha', 'idx_ordenes_compra_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_compra');
    }
};
