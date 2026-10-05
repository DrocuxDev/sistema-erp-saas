<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_bancarias', function (Blueprint $table) {
            $table->id();
            $table->string('banco', 100);
            $table->string('moneda', 10)->default('PEN');
            $table->string('numero_cuenta', 100);
            $table->string('cci', 100)->nullable();
            $table->boolean('mostrar_en_pdf')->default(true);
            $table->unsignedInteger('orden')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_bancarias');
    }
};
