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
        Schema::create('orden_compra_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_compra_id')->constrained('ordenes_compra')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('presentacion_compra_id')->nullable()->constrained('producto_presentaciones_compra');
            $table->decimal('cantidad_presentacion', 10, 2)->nullable();
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_compra_unit', 12, 2);
        });

        if (MySqlSchema::isMySql()) {
            DB::statement('ALTER TABLE orden_compra_detalle ADD subtotal DECIMAL(12,2) GENERATED ALWAYS AS (cantidad * precio_compra_unit) STORED');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_compra_detalle');
    }
};
