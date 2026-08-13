<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_compra_cliente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->unsignedBigInteger('cotizacion_id')->nullable();
            $table->unsignedBigInteger('venta_id')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users');
            $table->string('numero_oc', 50);
            $table->string('archivo', 255)->nullable();
            $table->date('fecha_recepcion');
            $table->enum('estado', ['pendiente', 'procesada', 'anulada'])->default('pendiente');
            $table->string('observaciones', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['cliente_id', 'numero_oc'], 'uq_oc_cliente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_compra_cliente');
    }
};
