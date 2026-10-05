<?php

use App\Support\Database\MySqlSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devoluciones_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('idempotency_key')->unique();
            $table->date('fecha');
            $table->text('motivo');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('igv', 12, 2);
            $table->decimal('total', 12, 2);
            $table->decimal('retencion', 12, 2);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['venta_id', 'fecha']);
        });

        Schema::create('devolucion_venta_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devolucion_venta_id')->constrained('devoluciones_venta');
            $table->foreignId('venta_detalle_id')->constrained('venta_detalle');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_venta_unit', 12, 2);
            $table->unique(['devolucion_venta_id', 'venta_detalle_id'], 'uq_devolucion_venta_linea');
            $table->index('venta_detalle_id');
        });

        if (MySqlSchema::isMySql()) {
            DB::statement("ALTER TABLE movimientos_inventario MODIFY COLUMN referencia_tipo ENUM('compra','venta','ajuste','guia_remision','orden_compra','devolucion_venta') NOT NULL");
        } else {
            Schema::table('movimientos_inventario', function (Blueprint $table) {
                $table->enum('referencia_tipo', ['compra', 'venta', 'ajuste', 'guia_remision', 'orden_compra', 'devolucion_venta'])->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::table('movimientos_inventario')->where('referencia_tipo', 'devolucion_venta')->exists()) {
            throw new RuntimeException('No se puede revertir esta migración mientras existan movimientos de devoluciones de venta.');
        }

        if (MySqlSchema::isMySql()) {
            DB::statement("ALTER TABLE movimientos_inventario MODIFY COLUMN referencia_tipo ENUM('compra','venta','ajuste','guia_remision','orden_compra') NOT NULL");
        } else {
            Schema::table('movimientos_inventario', function (Blueprint $table) {
                $table->enum('referencia_tipo', ['compra', 'venta', 'ajuste', 'guia_remision', 'orden_compra'])->change();
            });
        }

        Schema::dropIfExists('devolucion_venta_detalle');
        Schema::dropIfExists('devoluciones_venta');
    }
};
