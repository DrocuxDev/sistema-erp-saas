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
        Schema::create('devoluciones_compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('idempotency_key')->unique();
            $table->date('fecha');
            $table->text('motivo');
            $table->decimal('total', 12, 2);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['compra_id', 'fecha']);
        });

        Schema::create('devolucion_compra_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devolucion_compra_id')->constrained('devoluciones_compra');
            $table->foreignId('compra_detalle_id')->constrained('compra_detalle');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_compra_unit', 12, 2);
            $table->unique(['devolucion_compra_id', 'compra_detalle_id'], 'uq_devolucion_compra_linea');
            $table->index('compra_detalle_id');
        });

        if (MySqlSchema::isMySql()) {
            DB::statement("ALTER TABLE movimientos_inventario MODIFY COLUMN referencia_tipo ENUM('compra','venta','ajuste','guia_remision','orden_compra','devolucion_venta','devolucion_compra') NOT NULL");
        } else {
            Schema::table('movimientos_inventario', function (Blueprint $table) {
                $table->enum('referencia_tipo', ['compra', 'venta', 'ajuste', 'guia_remision', 'orden_compra', 'devolucion_venta', 'devolucion_compra'])->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::table('movimientos_inventario')->where('referencia_tipo', 'devolucion_compra')->exists()) {
            throw new RuntimeException('No se puede revertir esta migración mientras existan movimientos de devoluciones de compra.');
        }

        if (MySqlSchema::isMySql()) {
            DB::statement("ALTER TABLE movimientos_inventario MODIFY COLUMN referencia_tipo ENUM('compra','venta','ajuste','guia_remision','orden_compra','devolucion_venta') NOT NULL");
        } else {
            Schema::table('movimientos_inventario', function (Blueprint $table) {
                $table->enum('referencia_tipo', ['compra', 'venta', 'ajuste', 'guia_remision', 'orden_compra', 'devolucion_venta'])->change();
            });
        }

        Schema::dropIfExists('devolucion_compra_detalle');
        Schema::dropIfExists('devoluciones_compra');
    }
};
