<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->decimal('pct_igv', 5, 2)->default(18.00);
            $table->decimal('pct_retencion', 5, 2)->default(3.00);
            $table->decimal('pct_comision_venta', 5, 2)->default(5.00);
            $table->decimal('pct_comision_ganancia', 5, 2)->default(10.00);
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
