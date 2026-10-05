<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Foto opcional (ruta en disco `public`) para productos,
     * proveedores y clientes. Nullable: sin foto todo sigue igual.
     */
    public function up(): void
    {
        foreach (['productos', 'proveedores', 'clientes'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->string('imagen', 255)->nullable()->after('nombre');
            });
        }
    }

    public function down(): void
    {
        foreach (['productos', 'proveedores', 'clientes'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropColumn('imagen');
            });
        }
    }
};
