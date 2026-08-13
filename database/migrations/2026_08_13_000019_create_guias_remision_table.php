<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guias_remision', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->nullable()->constrained('ventas');
            $table->foreignId('orden_compra_id')->nullable()->constrained('ordenes_compra');
            $table->foreignId('usuario_id')->nullable()->constrained('users');
            $table->string('serie', 10);
            $table->integer('numero');
            $table->date('fecha_traslado');
            $table->enum('motivo_traslado', ['venta', 'compra', 'traslado_interno', 'otro']);
            $table->string('punto_partida', 255)->nullable();
            $table->string('punto_llegada', 255)->nullable();
            $table->string('transportista', 150)->nullable();
            $table->string('placa_vehiculo', 20)->nullable();
            $table->enum('estado', ['emitida', 'anulada'])->default('emitida');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['serie', 'numero'], 'uq_guia');
            $table->index('fecha_traslado', 'idx_guias_remision_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guias_remision');
    }
};
