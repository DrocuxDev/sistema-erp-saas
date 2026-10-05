<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['cobros_venta', 'pagos_compra'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->enum('estado', ['registrado', 'anulado'])->default('registrado');
                $table->text('motivo_anulacion')->nullable();
                $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('anulado_en')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['cobros_venta', 'pagos_compra'] as $tabla) {
            if (DB::table($tabla)->where('estado', 'anulado')->exists()) {
                throw new RuntimeException('No se puede revertir la migración mientras existan movimientos financieros anulados.');
            }
        }

        foreach (['pagos_compra', 'cobros_venta'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropForeign(['anulado_por']);
                $table->dropColumn(['estado', 'motivo_anulacion', 'anulado_por', 'anulado_en']);
            });
        }
    }
};
