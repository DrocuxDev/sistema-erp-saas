<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditoria', function (Blueprint $table) {
            $table->string('accion', 30)->change();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('auditoria', function (Blueprint $table) {
            $table->dropColumn(['datos_anteriores', 'datos_nuevos']);
        });
    }
};
