<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_presentaciones_compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('unidad_medida', 20);
            $table->decimal('equivalencia', 12, 4);
            $table->boolean('preferida')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['producto_id', 'unidad_medida'], 'uq_producto_presentacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_presentaciones_compra');
    }
};
