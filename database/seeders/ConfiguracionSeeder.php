<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        Configuracion::query()->firstOrCreate(
            ['nombre' => 'General'],
            [
                'pct_igv' => 18.00,
                'pct_retencion' => 3.00,
                'pct_comision_venta' => 5.00,
                'pct_comision_ganancia' => 10.00,
                'activo' => true,
            ]
        );
    }
}
