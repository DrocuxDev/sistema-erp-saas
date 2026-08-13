<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->string('tabla', 50);
            $table->unsignedBigInteger('registro_id');
            $table->foreignId('usuario_id')->constrained('users');
            $table->enum('accion', ['creacion', 'edicion', 'anulacion']);
            $table->string('detalle', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['tabla', 'registro_id'], 'idx_auditoria_tabla_registro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
