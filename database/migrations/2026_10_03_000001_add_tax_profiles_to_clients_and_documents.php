<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->boolean('precio_incluye_igv')->default(false);
            $table->decimal('pct_igv', 5, 2)->default(18);
            $table->decimal('pct_retencion', 5, 2)->default(0);
        });

        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->boolean('precio_incluye_igv')->default(false);
            $table->text('motivo_excepcion_tributaria')->nullable();
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->boolean('precio_incluye_igv')->default(true);
            $table->decimal('pct_igv', 5, 2)->default(18);
            $table->decimal('pct_retencion', 5, 2)->default(0);
            $table->decimal('retencion', 12, 2)->default(0);
            $table->text('motivo_excepcion_tributaria')->nullable();
        });

        Schema::table('comprobantes_venta', function (Blueprint $table) {
            $table->boolean('precio_incluye_igv')->default(true);
            $table->decimal('pct_igv', 5, 2)->default(18);
            $table->decimal('pct_retencion', 5, 2)->default(0);
            $table->decimal('retencion', 12, 2)->default(0);
            $table->text('motivo_excepcion_tributaria')->nullable();
        });

        $config = DB::table('configuracion')->where('activo', true)->first();
        if ($config) {
            DB::table('clientes')->update([
                'pct_igv' => $config->pct_igv,
                'pct_retencion' => $config->pct_retencion,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('comprobantes_venta', fn (Blueprint $table) => $table->dropColumn([
            'precio_incluye_igv', 'pct_igv', 'pct_retencion', 'retencion', 'motivo_excepcion_tributaria',
        ]));
        Schema::table('ventas', fn (Blueprint $table) => $table->dropColumn([
            'precio_incluye_igv', 'pct_igv', 'pct_retencion', 'retencion', 'motivo_excepcion_tributaria',
        ]));
        Schema::table('cotizaciones', fn (Blueprint $table) => $table->dropColumn([
            'precio_incluye_igv', 'motivo_excepcion_tributaria',
        ]));
        Schema::table('clientes', fn (Blueprint $table) => $table->dropColumn([
            'precio_incluye_igv', 'pct_igv', 'pct_retencion',
        ]));
    }
};
