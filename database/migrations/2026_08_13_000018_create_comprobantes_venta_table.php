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
        Schema::create('comprobantes_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas');
            $table->foreignId('usuario_id')->nullable()->constrained('users');
            $table->enum('tipo', ['factura', 'boleta', 'nota_venta']);
            $table->string('serie', 10);
            $table->integer('numero');
            $table->date('fecha_emision');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('igv', 12, 2);
            $table->enum('estado', ['emitido', 'anulado'])->default('emitido');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['tipo', 'serie', 'numero'], 'uq_comprobante');
            $table->index('fecha_emision', 'idx_comprobantes_venta_fecha');
        });

        if (MySqlSchema::isMySql()) {
            DB::statement('ALTER TABLE comprobantes_venta ADD total DECIMAL(12,2) GENERATED ALWAYS AS (subtotal + igv) STORED');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobantes_venta');
    }
};
