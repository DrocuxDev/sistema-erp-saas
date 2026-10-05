<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cobros_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('idempotency_key')->unique();
            $table->enum('tipo', ['cobro', 'reembolso']);
            $table->date('fecha');
            $table->decimal('monto', 12, 2);
            $table->enum('medio', ['efectivo', 'transferencia', 'deposito', 'tarjeta', 'otro']);
            $table->string('referencia', 100)->nullable();
            $table->text('observacion')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['venta_id', 'fecha']);
        });

        Schema::create('pagos_compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('idempotency_key')->unique();
            $table->enum('tipo', ['pago', 'reintegro']);
            $table->date('fecha');
            $table->decimal('monto', 12, 2);
            $table->enum('medio', ['efectivo', 'transferencia', 'deposito', 'tarjeta', 'otro']);
            $table->string('referencia', 100)->nullable();
            $table->text('observacion')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['compra_id', 'fecha']);
        });
    }

    public function down(): void
    {
        if (DB::table('cobros_venta')->exists() || DB::table('pagos_compra')->exists()) {
            throw new RuntimeException('No se puede revertir la migración mientras existan cobros o pagos registrados.');
        }

        Schema::dropIfExists('pagos_compra');
        Schema::dropIfExists('cobros_venta');
    }
};
