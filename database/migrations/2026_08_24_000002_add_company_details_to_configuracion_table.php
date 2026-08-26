<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->string('razon_social', 255)->nullable()->default('JHARDSYSTEX S.A.C.')->after('nombre');
            $table->string('ruc', 20)->nullable()->default('20601245781')->after('razon_social');
            $table->string('direccion', 255)->nullable()->default('Av. Principal N° 450 Int. 302, Lima')->after('ruc');
            $table->string('telefono', 100)->nullable()->default('(+51) 987 654 321 / (01) 748-2300')->after('direccion');
            $table->string('email', 150)->nullable()->default('ventas@jhardsystex.com | info@jhardsystex.com')->after('telefono');
            $table->string('sitio_web', 150)->nullable()->default('www.jhardsystex.com')->after('email');
            $table->text('cuentas_bancarias')->nullable()->after('sitio_web');
            $table->string('plazo_entrega_default', 150)->nullable()->default('2 a 5 días hábiles tras O/C')->after('cuentas_bancarias');
            $table->string('forma_pago_default', 150)->nullable()->default('30 días calendario previa conformidad y factura')->after('plazo_entrega_default');
            $table->string('validez_oferta_default', 100)->nullable()->default('15 días calendario')->after('forma_pago_default');
            $table->string('logistica_default', 255)->nullable()->default('Entrega en instalaciones del cliente / Lima Metropolitana')->after('validez_oferta_default');
        });

        // Actualizar registro existente
        DB::table('configuracion')->update([
            'razon_social' => 'JHARDSYSTEX S.A.C.',
            'ruc' => '20601245781',
            'direccion' => 'Av. Principal N° 450 Int. 302, Lima',
            'telefono' => '(+51) 987 654 321 - (01) 748-2300',
            'email' => 'ventas@jhardsystex.com | info@jhardsystex.com',
            'sitio_web' => 'www.jhardsystex.com',
            'cuentas_bancarias' => "• BCP Soles: 193-98765432-0-11 | CCI: 002-193-009876543201-14\n• BBVA Soles: 0011-0123-0100045678 | CCI: 011-123-000100045678-75\n• Titular: JHARDSYSTEX S.A.C.",
            'plazo_entrega_default' => '2 a 5 días hábiles tras O/C',
            'forma_pago_default' => '30 días calendario previa conformidad y factura',
            'validez_oferta_default' => '15 días calendario',
            'logistica_default' => 'Entrega en instalaciones del cliente / Lima Metropolitana',
        ]);
    }

    public function down(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->dropColumn([
                'razon_social',
                'ruc',
                'direccion',
                'telefono',
                'email',
                'sitio_web',
                'cuentas_bancarias',
                'plazo_entrega_default',
                'forma_pago_default',
                'validez_oferta_default',
                'logistica_default',
            ]);
        });
    }
};
