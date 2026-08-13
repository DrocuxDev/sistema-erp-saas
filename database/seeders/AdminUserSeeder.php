<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@erp.local'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'rol' => 'administrador',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'trabajador@erp.local'],
            [
                'name' => 'Trabajador',
                'password' => Hash::make('password'),
                'rol' => 'trabajador',
            ]
        );
    }
}
