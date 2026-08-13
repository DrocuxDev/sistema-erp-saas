<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series_documentos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_documento', ['factura', 'boleta', 'nota_venta', 'guia_remision']);
            $table->string('serie', 10);
            $table->integer('correlativo_actual')->default(0);
            $table->unique(['tipo_documento', 'serie'], 'uq_serie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series_documentos');
    }
};
