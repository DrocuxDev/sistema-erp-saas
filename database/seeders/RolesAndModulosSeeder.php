<?php

namespace Database\Seeders;

use App\Models\Modulo;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndModulosSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Catálogo Oficial de Módulos del Sistema ERP
        $modulos = [
            [
                'codigo' => 'dashboard',
                'nombre' => 'Dashboard Principal',
                'categoria' => 'General',
                'icono' => 'chart-bar',
                'ruta_base' => 'dashboard',
                'orden' => 1,
            ],
            [
                'codigo' => 'proveedores',
                'nombre' => 'Proveedores',
                'categoria' => 'Maestros',
                'icono' => 'truck',
                'ruta_base' => 'proveedores',
                'orden' => 2,
            ],
            [
                'codigo' => 'clientes',
                'nombre' => 'Clientes',
                'categoria' => 'Maestros',
                'icono' => 'user-group',
                'ruta_base' => 'clientes',
                'orden' => 3,
            ],
            [
                'codigo' => 'productos',
                'nombre' => 'Productos',
                'categoria' => 'Maestros',
                'icono' => 'cube',
                'ruta_base' => 'productos',
                'orden' => 4,
            ],
            [
                'codigo' => 'inventario',
                'nombre' => 'Inventario y Kardex',
                'categoria' => 'Maestros',
                'icono' => 'clipboard-document-list',
                'ruta_base' => 'inventario',
                'orden' => 5,
            ],
            [
                'codigo' => 'ordenes_compra_cliente',
                'nombre' => 'Órdenes de Compra Clientes',
                'categoria' => 'Ventas',
                'icono' => 'document-text',
                'ruta_base' => 'ordenes-compra-cliente',
                'orden' => 6,
            ],
            [
                'codigo' => 'cotizaciones',
                'nombre' => 'Cotizaciones',
                'categoria' => 'Ventas',
                'icono' => 'document-duplicate',
                'ruta_base' => 'cotizaciones',
                'orden' => 7,
            ],
            [
                'codigo' => 'ventas',
                'nombre' => 'Ventas',
                'categoria' => 'Ventas',
                'icono' => 'shopping-cart',
                'ruta_base' => 'ventas',
                'orden' => 8,
            ],
            [
                'codigo' => 'comprobantes',
                'nombre' => 'Comprobantes y Guías',
                'categoria' => 'Ventas',
                'icono' => 'receipt-percent',
                'ruta_base' => 'comprobantes',
                'orden' => 9,
            ],
            [
                'codigo' => 'compras',
                'nombre' => 'Compras a Proveedores',
                'categoria' => 'Compras',
                'icono' => 'shopping-bag',
                'ruta_base' => 'compras',
                'orden' => 10,
            ],
            [
                'codigo' => 'usuarios',
                'nombre' => 'Gestión de Usuarios y Accesos',
                'categoria' => 'Administración',
                'icono' => 'users',
                'ruta_base' => 'users',
                'orden' => 11,
            ],
            [
                'codigo' => 'configuracion',
                'nombre' => 'Configuración del Sistema',
                'categoria' => 'Administración',
                'icono' => 'cog-6-tooth',
                'ruta_base' => 'configuracion',
                'orden' => 12,
            ],
            [
                'codigo' => 'auditoria',
                'nombre' => 'Auditoría y Trazabilidad',
                'categoria' => 'Administración',
                'icono' => 'shield-check',
                'ruta_base' => 'auditoria',
                'orden' => 13,
            ],
        ];

        $modulosGuardados = [];
        foreach ($modulos as $item) {
            $modulosGuardados[$item['codigo']] = Modulo::updateOrCreate(
                ['codigo' => $item['codigo']],
                $item
            );
        }

        // 2. Roles Estándar del ERP
        $rolesData = [
            [
                'nombre' => 'SUPERADMIN',
                'slug' => 'superadmin',
                'descripcion' => 'Acceso total y configuración general del ERP',
                'es_sistema' => true,
                'modulos' => array_keys($modulosGuardados),
            ],
            [
                'nombre' => 'GERENCIA',
                'slug' => 'gerencia',
                'descripcion' => 'Supervisión comercial, reportes, cotizaciones y ventas',
                'es_sistema' => false,
                'modulos' => ['dashboard', 'proveedores', 'clientes', 'productos', 'inventario', 'ordenes_compra_cliente', 'cotizaciones', 'ventas', 'comprobantes', 'compras', 'auditoria'],
            ],
            [
                'nombre' => 'ADMINISTRACION',
                'slug' => 'administracion',
                'descripcion' => 'Gestión administrativa, maestros y emisión de documentos',
                'es_sistema' => false,
                'modulos' => ['dashboard', 'proveedores', 'clientes', 'productos', 'inventario', 'ordenes_compra_cliente', 'cotizaciones', 'ventas', 'comprobantes', 'compras', 'usuarios'],
            ],
            [
                'nombre' => 'VENTAS / COMERCIAL',
                'slug' => 'ventas',
                'descripcion' => 'Gestión de clientes, cotizaciones y emisión de pedidos/ventas',
                'es_sistema' => false,
                'modulos' => ['dashboard', 'clientes', 'productos', 'ordenes_compra_cliente', 'cotizaciones', 'ventas'],
            ],
            [
                'nombre' => 'SOPORTE TÉCNICO',
                'slug' => 'soporte_tecnico',
                'descripcion' => 'Atención técnica, consultas de productos y cotizaciones técnicas',
                'es_sistema' => false,
                'modulos' => ['dashboard', 'productos', 'inventario', 'cotizaciones'],
            ],
            [
                'nombre' => 'TESORERIA',
                'slug' => 'tesoreria',
                'descripcion' => 'Revisión de comprobantes, cobros y ventas',
                'es_sistema' => false,
                'modulos' => ['dashboard', 'ventas', 'comprobantes', 'compras'],
            ],
        ];

        $rolesGuardados = [];
        foreach ($rolesData as $rData) {
            $rol = Rol::updateOrCreate(
                ['slug' => $rData['slug']],
                [
                    'nombre' => $rData['nombre'],
                    'descripcion' => $rData['descripcion'],
                    'es_sistema' => $rData['es_sistema'],
                ]
            );

            // Sincronizar módulos por rol
            $moduloIds = [];
            foreach ($rData['modulos'] as $cod) {
                if (isset($modulosGuardados[$cod])) {
                    $moduloIds[$modulosGuardados[$cod]->id] = [
                        'puede_ver' => true,
                        'puede_crear' => true,
                        'puede_editar' => true,
                        'puede_eliminar' => true,
                    ];
                }
            }
            $rol->modulos()->sync($moduloIds);
            $rolesGuardados[$rData['slug']] = $rol;
        }

        // 3. Usuarios Oficiales (Exactamente 3 perfiles simplificados)

        // 1. Administrador General (SuperAdmin Base Inmutable)
        User::updateOrCreate(
            ['email' => 'admin@jhardsystex.com'],
            [
                'name' => 'Administrador General',
                'username' => 'admin@jhardsystex',
                'password' => Hash::make('password'),
                'rol' => 'administrador',
                'rol_id' => $rolesGuardados['superadmin']->id,
                'punto_emision' => 'OFICINA',
                'is_superadmin' => true,
                'activo' => true,
            ]
        );

        // 2. Ejecutivo de Ventas
        User::updateOrCreate(
            ['email' => 'ventas@jhardsystex.com'],
            [
                'name' => 'Ejecutivo de Ventas',
                'username' => 'ventas@jhardsystex',
                'password' => Hash::make('password'),
                'rol' => 'trabajador',
                'rol_id' => $rolesGuardados['ventas']->id,
                'punto_emision' => 'OFICINA',
                'is_superadmin' => false,
                'activo' => true,
            ]
        );

        // 3. Soporte / Logística
        User::updateOrCreate(
            ['email' => 'soporte@jhardsystex.com'],
            [
                'name' => 'Soporte / Logística',
                'username' => 'soporte@jhardsystex',
                'password' => Hash::make('password'),
                'rol' => 'trabajador',
                'rol_id' => $rolesGuardados['soporte_tecnico']->id,
                'punto_emision' => 'OFICINA',
                'is_superadmin' => false,
                'activo' => true,
            ]
        );
    }
}
