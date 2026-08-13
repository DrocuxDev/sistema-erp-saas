<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('usuario_id')->nullable()->constrained('users');
            $table->foreignId('orden_compra_cliente_id')->nullable()->constrained('ordenes_compra_cliente');
            $table->date('fecha');
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada', 'facturada'])->default('pendiente');
            $table->string('observaciones', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('fecha', 'idx_cotizaciones_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
