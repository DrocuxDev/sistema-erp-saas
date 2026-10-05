<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // El PDF exige serie + correlativo de 8 dígitos (C001-00000001).
        DB::table('series_documentos')
            ->where('tipo_documento', 'cotizacion')
            ->update(['longitud_correlativo' => 8]);
    }

    public function down(): void
    {
        DB::table('series_documentos')
            ->where('tipo_documento', 'cotizacion')
            ->update(['longitud_correlativo' => 6]);
    }
};
