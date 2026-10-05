<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            // A. Portada
            $table->string('portada_imagen_path', 255)->nullable()->after('logistica_default');
            $table->boolean('incluir_portada')->default(false)->after('portada_imagen_path');
            // B. Identidad
            $table->string('logo_path', 255)->nullable()->after('incluir_portada');
            $table->string('color_principal', 7)->default('#0076A8')->after('logo_path');
            $table->string('color_tabla_encabezado', 7)->default('#111111')->after('color_principal');
            // C. Textos predefinidos ({empresa} y {cliente} como variables)
            $table->text('texto_saludo')->nullable()->after('color_tabla_encabezado');
            $table->text('condiciones_adicionales_default')->nullable()->after('texto_saludo');
            $table->text('texto_firma')->nullable()->after('condiciones_adicionales_default');
            // D. Titular único de las cuentas bancarias
            $table->string('titular_cuenta', 255)->nullable()->after('texto_firma');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->dropColumn([
                'portada_imagen_path',
                'incluir_portada',
                'logo_path',
                'color_principal',
                'color_tabla_encabezado',
                'texto_saludo',
                'condiciones_adicionales_default',
                'texto_firma',
                'titular_cuenta',
            ]);
        });
    }
};
