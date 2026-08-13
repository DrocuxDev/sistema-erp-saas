<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->nullable()->constrained('cotizaciones');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('usuario_id')->nullable()->constrained('users');
            $table->foreignId('orden_compra_cliente_id')->nullable()->constrained('ordenes_compra_cliente');
            $table->date('fecha');
            $table->decimal('total', 12, 2);
            $table->timestamp('created_at')->useCurrent();
            $table->index('fecha', 'idx_ventas_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
