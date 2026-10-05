<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            // Copia versionada de la configuración de documentos al emitir la
            // cotización: reimprimir un documento viejo no cambia aunque la
            // configuración actual cambie después.
            $table->json('documento_snapshot')->nullable()->after('motivo_excepcion_tributaria');
            // Cláusulas libres por documento (stock/importación, garantía, etc.)
            $table->text('condiciones_adicionales')->nullable()->after('documento_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn(['documento_snapshot', 'condiciones_adicionales']);
        });
    }
};
