<?php

namespace Database\Seeders;

use App\Models\Auditoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\ComprobanteVenta;
use App\Models\Configuracion;
use App\Models\Cotizacion;
use App\Models\CotizacionDetalle;
use App\Models\CotizacionResumen;
use App\Models\GuiaRemision;
use App\Models\GuiaRemisionDetalle;
use App\Models\MovimientoInventario;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraCliente;
use App\Models\OrdenCompraDetalle;
use App\Models\Producto;
use App\Models\ProductoPresentacionCompra;
use App\Models\ProductoProveedor;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\CotizacionResumenCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Desactivar llaves foráneas para limpiar de forma segura
        Schema::disableForeignKeyConstraints();

        GuiaRemisionDetalle::truncate();
        GuiaRemision::truncate();
        ComprobanteVenta::truncate();
        MovimientoInventario::truncate();
        CompraDetalle::truncate();
        Compra::truncate();
        OrdenCompraDetalle::truncate();
        OrdenCompra::truncate();
        VentaDetalle::truncate();
        Venta::truncate();
        CotizacionResumen::truncate();
        CotizacionDetalle::truncate();
        Cotizacion::truncate();
        OrdenCompraCliente::truncate();
        ProductoPresentacionCompra::truncate();
        ProductoProveedor::truncate();
        Producto::truncate();
        Proveedor::truncate();
        Cliente::truncate();
        Auditoria::truncate();
        User::truncate();

        Schema::enableForeignKeyConstraints();

        // 2. Garantizar Configuración General activa
        $configuracion = Configuracion::firstOrCreate(
            ['id' => 1],
            [
                'nombre' => 'General',
                'pct_igv' => 18.00,
                'pct_retencion' => 3.00,
                'pct_comision_venta' => 5.00,
                'pct_comision_ganancia' => 10.00,
                'activo' => true,
            ]
        );

        // 3. Crear 5 Usuarios
        $usuarios = [
            [
                'name' => 'Carlos Administrador',
                'email' => 'admin@erp.local',
                'password' => Hash::make('password'),
                'rol' => 'administrador',
            ],
            [
                'name' => 'María Gerente TI',
                'email' => 'maria.gerencia@erp.local',
                'password' => Hash::make('password'),
                'rol' => 'administrador',
            ],
            [
                'name' => 'Juan Vendedor Senior',
                'email' => 'juan.vendedor@erp.local',
                'password' => Hash::make('password'),
                'rol' => 'trabajador',
            ],
            [
                'name' => 'Ana Ejecutiva Comercial',
                'email' => 'ana.ventas@erp.local',
                'password' => Hash::make('password'),
                'rol' => 'trabajador',
            ],
            [
                'name' => 'Luis Soporte Técnico',
                'email' => 'luis.soporte@erp.local',
                'password' => Hash::make('password'),
                'rol' => 'trabajador',
            ],
        ];

        $usersCreados = [];
        foreach ($usuarios as $u) {
            $usersCreados[] = User::create($u);
        }

        // 4. Crear 5 Clientes
        $clientes = [
            [
                'nombre' => 'OPERADORA SURPERU S.A.',
                'razon_social' => 'OPERADORA SURPERU SOCIEDAD ANONIMA',
                'ruc' => '20514896321',
                'direccion' => 'Av. Javier Prado Este 4200, Santiago de Surco, Lima',
                'contacto' => 'Ing. Fernando Ramos (Jefe de Infraestructura)',
                'telefono' => '+51 987 654 321',
                'email' => 'compras@surperu.com.pe',
                'condicion_pago' => '30 días crédito',
            ],
            [
                'nombre' => 'BANCO FINANCIERO DEL SUR',
                'razon_social' => 'BANCO FINANCIERO DEL SUR S.A.',
                'ruc' => '20100456789',
                'direccion' => 'Av. Rivera Navarrete 501, San Isidro, Lima',
                'contacto' => 'Lic. Claudia Morales (Gerencia de Operaciones)',
                'telefono' => '+51 912 345 678',
                'email' => 'adquisiciones@bancodelsur.pe',
                'condicion_pago' => '15 días crédito',
            ],
            [
                'nombre' => 'MINERA ANDINA CENTRAL S.A.C.',
                'razon_social' => 'MINERA ANDINA CENTRAL S.A.C.',
                'ruc' => '20601598741',
                'direccion' => 'Calle Las Begonias 441, Piso 9, San Isidro',
                'contacto' => 'Ing. Roberto Chávez (Superintendente TI)',
                'telefono' => '+51 945 612 378',
                'email' => 'proyectos.ti@mineraandina.com',
                'condicion_pago' => '30 días crédito',
            ],
            [
                'nombre' => 'LOGÍSTICA INTEGRAL PACÍFICO',
                'razon_social' => 'LOGÍSTICA INTEGRAL PACÍFICO S.A.',
                'ruc' => '20456789123',
                'direccion' => 'Av. Elmer Faucett 2800, Callao',
                'contacto' => 'Srta. Patricia Del Solar (Jefa de Logística)',
                'telefono' => '+51 998 877 665',
                'email' => 'contacto@logpacifico.pe',
                'condicion_pago' => 'Al contado',
            ],
            [
                'nombre' => 'CLÍNICA ESPECIALIZADA SAN RAFAEL',
                'razon_social' => 'CLÍNICA SAN RAFAEL S.A.C.',
                'ruc' => '20369852147',
                'direccion' => 'Av. Guardia Civil 380, San Borja, Lima',
                'contacto' => 'Dr. Manuel Benavides (Administración General)',
                'telefono' => '+51 954 123 987',
                'email' => 'mbenavides@clinicasanrafael.pe',
                'condicion_pago' => '30 días crédito',
            ],
        ];

        $clientesCreados = [];
        foreach ($clientes as $c) {
            $clientesCreados[] = Cliente::create($c);
        }

        // 5. Crear 5 Proveedores
        $proveedores = [
            [
                'nombre' => 'DELTRON PERÚ S.A.',
                'razon_social' => 'GRUPO DELTRON SOCIEDAD ANONIMA',
                'ruc' => '20212345678',
                'direccion' => 'Av. Raúl Ferrero 105, La Molina, Lima',
                'contacto' => 'Asesor Mayorista Hardware',
                'telefono' => '(01) 415-0100',
                'email' => 'ventas@deltron.com.pe',
            ],
            [
                'nombre' => 'INGRAM MICRO PERÚ',
                'razon_social' => 'INGRAM MICRO PERU S.A.C.',
                'ruc' => '20498765432',
                'direccion' => 'Av. República de Panamá 3535, San Isidro, Lima',
                'contacto' => 'Ejecutivo de Canales Enterprise',
                'telefono' => '(01) 618-9000',
                'email' => 'canales@ingrammicro.com',
            ],
            [
                'nombre' => 'TECH DATA / TD SYNNEX PERÚ',
                'razon_social' => 'TD SYNNEX PERU S.A.C.',
                'ruc' => '20556677889',
                'direccion' => 'Av. Canaval y Moreyra 480, San Isidro',
                'contacto' => 'Especialista en Networking y Servidores',
                'telefono' => '(01) 710-5400',
                'email' => 'ventas.pe@tdsynnex.com',
            ],
            [
                'nombre' => 'FIBER STORE LATAM S.A.C.',
                'razon_social' => 'FIBER STORE LATAM SOCIEDAD ANONIMA CERRADA',
                'ruc' => '20603344556',
                'direccion' => 'Jr. Paruro 1240, Cercado de Lima',
                'contacto' => 'Ing. David Salazar (Conectividad Óptica)',
                'telefono' => '(01) 427-8899',
                'email' => 'cotizaciones@fiberstore.pe',
            ],
            [
                'nombre' => 'GLOBAL SERVICES & CABLES PERÚ',
                'razon_social' => 'GLOBAL SERVICES TI PERU S.A.C.',
                'ruc' => '20609988771',
                'direccion' => 'Av. Argentina 215, Centro Comercial Bellavista, Callao',
                'contacto' => 'Área de Suministros Eléctricos y Redes',
                'telefono' => '(01) 561-2244',
                'email' => 'contacto@globalservices.pe',
            ],
        ];

        $proveedoresCreados = [];
        foreach ($proveedores as $p) {
            $proveedoresCreados[] = Proveedor::create($p);
        }

        // 6. Crear 5 Productos con stock y vincularlos a proveedores
        $productos = [
            [
                'sku' => 'SRV-DL380-G10',
                'nombre' => 'Servidor HPE ProLiant DL380 Gen10 Xeon Silver 4210R 32GB RAM',
                'unidad_medida' => 'UND',
                'stock_actual' => 8.00,
                'stock_minimo' => 2.00,
                'puede_venderse' => true,
                'puede_comprarse' => true,
                'precio_compra' => 8500.00,
                'precio_venta' => 11200.00,
                'proveedor_idx' => 1, // Ingram Micro
            ],
            [
                'sku' => 'SW-C9200L-24P',
                'nombre' => 'Switch Cisco Catalyst 9200L 24 Puertos Gigabit PoE+ 4x1G Uplink',
                'unidad_medida' => 'UND',
                'stock_actual' => 15.00,
                'stock_minimo' => 3.00,
                'puede_venderse' => true,
                'puede_comprarse' => true,
                'precio_compra' => 4200.00,
                'precio_venta' => 5800.00,
                'proveedor_idx' => 2, // TD Synnex
            ],
            [
                'sku' => 'UPS-APC-3KVA',
                'nombre' => 'UPS APC Smart-UPS Online 3000VA / 2700W Rackmount 2U 230V',
                'unidad_medida' => 'UND',
                'stock_actual' => 10.00,
                'stock_minimo' => 2.00,
                'puede_venderse' => true,
                'puede_comprarse' => true,
                'precio_compra' => 3400.00,
                'precio_venta' => 4650.00,
                'proveedor_idx' => 0, // Deltron
            ],
            [
                'sku' => 'FIB-OM4-100M',
                'nombre' => 'Bobina Fibra Óptica OM4 Multimodo 12 Hilos LSZH Interior/Exterior',
                'unidad_medida' => 'MTR',
                'stock_actual' => 1200.00,
                'stock_minimo' => 300.00,
                'puede_venderse' => true,
                'puede_comprarse' => true,
                'precio_compra' => 12.50,
                'precio_venta' => 18.00,
                'proveedor_idx' => 3, // Fiber Store
            ],
            [
                'sku' => 'SRV-INST-REDES',
                'nombre' => 'Servicio de Instalación, Fusión Óptica y Certificación Fluke de Red',
                'unidad_medida' => 'SRV',
                'stock_actual' => 50.00,
                'stock_minimo' => 5.00,
                'puede_venderse' => true,
                'puede_comprarse' => true,
                'precio_compra' => 1500.00,
                'precio_venta' => 2600.00,
                'proveedor_idx' => 4, // Global Services
            ],
        ];

        $productosCreados = [];
        foreach ($productos as $pData) {
            $provIdx = $pData['proveedor_idx'];
            $pCompra = $pData['precio_compra'];
            unset($pData['proveedor_idx'], $pData['precio_compra'], $pData['precio_venta']);

            $prod = Producto::create($pData);
            $productosCreados[] = $prod;

            // Relación con proveedor
            ProductoProveedor::create([
                'producto_id' => $prod->id,
                'proveedor_id' => $proveedoresCreados[$provIdx]->id,
                'precio_compra' => $pCompra,
                'fecha_actualizacion' => now()->toDateString(),
            ]);
        }

        // 7. Crear 5 Órdenes de Compra de Cliente (OC Cliente)
        $ocsClienteData = [
            [
                'cliente_id' => $clientesCreados[0]->id,
                'usuario_id' => $usersCreados[2]->id,
                'numero_oc' => 'OC-SURPERU-2026-001',
                'fecha_recepcion' => '2026-08-10',
                'estado' => 'procesada',
                'observaciones' => 'Equipamiento de Servidores para Datacenter Principal',
            ],
            [
                'cliente_id' => $clientesCreados[1]->id,
                'usuario_id' => $usersCreados[3]->id,
                'numero_oc' => 'OC-BANCO-TI-889',
                'fecha_recepcion' => '2026-08-12',
                'estado' => 'procesada',
                'observaciones' => 'Renovación de Switches de Core y Distribución',
            ],
            [
                'cliente_id' => $clientesCreados[2]->id,
                'usuario_id' => $usersCreados[0]->id,
                'numero_oc' => 'OC-MINERA-9942',
                'fecha_recepcion' => '2026-08-15',
                'estado' => 'procesada',
                'observaciones' => 'Tendido y Fusión de Fibra Óptica en Campamento',
            ],
            [
                'cliente_id' => $clientesCreados[3]->id,
                'usuario_id' => $usersCreados[2]->id,
                'numero_oc' => 'OC-LOGPAC-2026-44',
                'fecha_recepcion' => '2026-08-18',
                'estado' => 'pendiente',
                'observaciones' => 'Protección Eléctrica UPS para Racks de Almacén',
            ],
            [
                'cliente_id' => $clientesCreados[4]->id,
                'usuario_id' => $usersCreados[3]->id,
                'numero_oc' => 'OC-CLINICA-SR-551',
                'fecha_recepcion' => '2026-08-20',
                'estado' => 'pendiente',
                'observaciones' => 'Mantenimiento Preventivo y Certificación de Cableado',
            ],
        ];

        $ocsClienteCreadas = [];
        foreach ($ocsClienteData as $oc) {
            $ocsClienteCreadas[] = OrdenCompraCliente::create($oc);
        }

        // 8. Crear 5 Cotizaciones con el nuevo estándar (C001-0 a C001-4, versioning, portadas A/B y desglose)
        $cotizacionesInfo = [
            [
                'correlativo' => 0,
                'codigo' => 'C001-0',
                'version' => 1,
                'tipo_cotizacion' => 'producto',
                'cliente_id' => $clientesCreados[0]->id,
                'usuario_id' => $usersCreados[2]->id,
                'orden_compra_cliente_id' => $ocsClienteCreadas[0]->id,
                'fecha' => '2026-08-10',
                'estado' => 'aprobada',
                'asunto' => 'COTIZACION PROYECTO DATACENTER SURPERU',
                'representante' => 'Ing. Fernando Ramos',
                'observaciones' => 'Entrega garantizada con soporte oficial de fábrica.',
                'plazo_entrega' => '3 días hábiles tras recepción de O/C',
                'forma_pago' => '30 días calendario previa conformidad y factura',
                'logistica' => 'Entrega en Datacenter Surco / Lima Metropolitana',
                'validez_oferta' => '15 días calendario',
                'detalles' => [
                    [
                        'producto_id' => $productosCreados[0]->id,
                        'proveedor_id' => $proveedoresCreados[1]->id,
                        'descripcion' => 'Servidor HPE DL380 Gen10 Xeon 4210R 32GB',
                        'unidad_medida' => 'UND',
                        'cantidad' => 2.00,
                        'precio_venta_unit' => 11200.00,
                        'precio_compra_unit' => 8500.00,
                        'igv_compra' => 0.00,
                    ],
                    [
                        'producto_id' => $productosCreados[2]->id,
                        'proveedor_id' => $proveedoresCreados[0]->id,
                        'descripcion' => 'UPS APC Smart-UPS Online 3KVA Rackmount',
                        'unidad_medida' => 'UND',
                        'cantidad' => 2.00,
                        'precio_venta_unit' => 4650.00,
                        'precio_compra_unit' => 3400.00,
                        'igv_compra' => 0.00,
                    ],
                ],
            ],
            [
                'correlativo' => 1,
                'codigo' => 'C001-1',
                'version' => 2, // Demostración de control de versión
                'tipo_cotizacion' => 'producto',
                'cliente_id' => $clientesCreados[1]->id,
                'usuario_id' => $usersCreados[3]->id,
                'orden_compra_cliente_id' => $ocsClienteCreadas[1]->id,
                'fecha' => '2026-08-12',
                'estado' => 'facturada',
                'asunto' => 'RENOVACION DE SWITCHES CORE CISCO CATALYST',
                'representante' => 'Lic. Claudia Morales',
                'observaciones' => 'Incluye licenciamiento Network Essentials.',
                'plazo_entrega' => '2 días hábiles',
                'forma_pago' => '15 días factura',
                'logistica' => 'Sede Central San Isidro',
                'validez_oferta' => '15 días calendario',
                'detalles' => [
                    [
                        'producto_id' => $productosCreados[1]->id,
                        'proveedor_id' => $proveedoresCreados[2]->id,
                        'descripcion' => 'Switch Cisco Catalyst 9200L 24P PoE+',
                        'unidad_medida' => 'UND',
                        'cantidad' => 3.00,
                        'precio_venta_unit' => 5800.00,
                        'precio_compra_unit' => 4200.00,
                        'igv_compra' => 0.00,
                    ],
                ],
            ],
            [
                'correlativo' => 2,
                'codigo' => 'C001-2',
                'version' => 1,
                'tipo_cotizacion' => 'servicio',
                'cliente_id' => $clientesCreados[2]->id,
                'usuario_id' => $usersCreados[0]->id,
                'orden_compra_cliente_id' => $ocsClienteCreadas[2]->id,
                'fecha' => '2026-08-15',
                'estado' => 'aprobada',
                'asunto' => 'PROYECTO TENDIDO Y FUSION FIBRA OPTICA MINERA',
                'representante' => 'Ing. Roberto Chávez',
                'observaciones' => 'Personal técnico cuenta con SCTR y pases mina.',
                'plazo_entrega' => '5 a 7 días en mina',
                'forma_pago' => '30 días crédito',
                'logistica' => 'Transporte puesto en Campamento Minero',
                'validez_oferta' => '30 días calendario',
                'detalles' => [
                    [
                        'producto_id' => $productosCreados[3]->id,
                        'proveedor_id' => $proveedoresCreados[3]->id,
                        'descripcion' => 'Bobina Fibra Óptica OM4 12H LSZH',
                        'unidad_medida' => 'MTR',
                        'cantidad' => 500.00,
                        'precio_venta_unit' => 18.00,
                        'precio_compra_unit' => 12.50,
                        'igv_compra' => 0.00,
                    ],
                    [
                        'producto_id' => $productosCreados[4]->id,
                        'proveedor_id' => $proveedoresCreados[4]->id,
                        'descripcion' => 'Servicio de Fusión y Certificación Fluke',
                        'unidad_medida' => 'SRV',
                        'cantidad' => 1.00,
                        'precio_venta_unit' => 2600.00,
                        'precio_compra_unit' => 1500.00,
                        'igv_compra' => 0.00,
                    ],
                ],
            ],
            [
                'correlativo' => 3,
                'codigo' => 'C001-3',
                'version' => 1,
                'tipo_cotizacion' => 'producto',
                'cliente_id' => $clientesCreados[3]->id,
                'usuario_id' => $usersCreados[2]->id,
                'orden_compra_cliente_id' => $ocsClienteCreadas[3]->id,
                'fecha' => '2026-08-18',
                'estado' => 'pendiente',
                'asunto' => 'SUMINISTRO DE UPS PARA SISTEMA DE ALMACEN',
                'representante' => 'Srta. Patricia Del Solar',
                'observaciones' => 'Equipos en stock para entrega inmediata.',
                'plazo_entrega' => 'Inmediato / 24 horas',
                'forma_pago' => 'Al contado contra entrega',
                'logistica' => 'Almacén Callao',
                'validez_oferta' => '15 días calendario',
                'detalles' => [
                    [
                        'producto_id' => $productosCreados[2]->id,
                        'proveedor_id' => $proveedoresCreados[0]->id,
                        'descripcion' => 'UPS APC Smart-UPS Online 3KVA 2U',
                        'unidad_medida' => 'UND',
                        'cantidad' => 1.00,
                        'precio_venta_unit' => 4650.00,
                        'precio_compra_unit' => 3400.00,
                        'igv_compra' => 0.00,
                    ],
                ],
            ],
            [
                'correlativo' => 4,
                'codigo' => 'C001-4',
                'version' => 1,
                'tipo_cotizacion' => 'servicio',
                'cliente_id' => $clientesCreados[4]->id,
                'usuario_id' => $usersCreados[3]->id,
                'orden_compra_cliente_id' => $ocsClienteCreadas[4]->id,
                'fecha' => '2026-08-20',
                'estado' => 'pendiente',
                'asunto' => 'MANTENIMIENTO PREVENTIVO Y CERTIFICACION CLINICA',
                'representante' => 'Dr. Manuel Benavides',
                'observaciones' => 'Trabajo programado en horario nocturno no invasivo.',
                'plazo_entrega' => 'Fin de semana coordinado',
                'forma_pago' => '30 días factura',
                'logistica' => 'Sede San Borja',
                'validez_oferta' => '15 días calendario',
                'detalles' => [
                    [
                        'producto_id' => $productosCreados[4]->id,
                        'proveedor_id' => $proveedoresCreados[4]->id,
                        'descripcion' => 'Servicio Integral de Certificación y Peinado de Racks',
                        'unidad_medida' => 'SRV',
                        'cantidad' => 2.00,
                        'precio_venta_unit' => 2600.00,
                        'precio_compra_unit' => 1500.00,
                        'igv_compra' => 0.00,
                    ],
                ],
            ],
        ];

        foreach ($cotizacionesInfo as $cotData) {
            $detalles = $cotData['detalles'];
            unset($cotData['detalles']);

            $cotizacion = Cotizacion::create([
                'serie' => 'C001',
                'correlativo' => $cotData['correlativo'],
                'codigo' => $cotData['codigo'],
                'version' => $cotData['version'],
                'tipo_cotizacion' => $cotData['tipo_cotizacion'],
                'cliente_id' => $cotData['cliente_id'],
                'usuario_id' => $cotData['usuario_id'],
                'orden_compra_cliente_id' => $cotData['orden_compra_cliente_id'],
                'fecha' => $cotData['fecha'],
                'estado' => $cotData['estado'],
                'asunto' => $cotData['asunto'],
                'representante' => $cotData['representante'],
                'observaciones' => $cotData['observaciones'],
                'plazo_entrega' => $cotData['plazo_entrega'],
                'forma_pago' => $cotData['forma_pago'],
                'logistica' => $cotData['logistica'],
                'validez_oferta' => $cotData['validez_oferta'],
            ]);

            // Actualizar relación en la orden de compra del cliente
            OrdenCompraCliente::where('id', $cotData['orden_compra_cliente_id'])->update([
                'cotizacion_id' => $cotizacion->id,
            ]);

            $lineasCollection = collect();
            foreach ($detalles as $det) {
                CotizacionDetalle::create([
                    'cotizacion_id' => $cotizacion->id,
                    'producto_id' => $det['producto_id'],
                    'proveedor_id' => $det['proveedor_id'],
                    'descripcion' => $det['descripcion'],
                    'unidad_medida' => $det['unidad_medida'],
                    'cantidad' => $det['cantidad'],
                    'precio_venta_unit' => $det['precio_venta_unit'],
                    'precio_compra_unit' => $det['precio_compra_unit'],
                    'igv_compra' => $det['igv_compra'],
                ]);

                $lineasCollection->push([
                    'cantidad' => $det['cantidad'],
                    'precio_venta_unit' => $det['precio_venta_unit'],
                    'precio_compra_unit' => $det['precio_compra_unit'],
                    'igv_compra' => $det['igv_compra'],
                ]);
            }

            // Calcular y guardar resumen financiero
            $resumen = CotizacionResumenCalculator::calcular($lineasCollection, $configuracion, 0);

            CotizacionResumen::create([
                'cotizacion_id' => $cotizacion->id,
                'configuracion_id' => $configuracion->id,
                'subtotal' => $resumen['subtotal'],
                'igv' => $resumen['igv'],
                'retencion' => $resumen['retencion'],
                'compra_activa' => $resumen['compra_activa'],
                'compra_fija' => $resumen['compra_fija'],
                'no_utilizable' => $resumen['no_utilizable'],
                'comision_venta' => $resumen['comision_venta'],
                'comision_ganancia' => $resumen['comision_ganancia'],
            ]);
        }

        // 9. Crear 5 Órdenes de Compra a Proveedor (Fase 8 / Compras a Proveedor)
        $ocsProveedor = [
            [
                'proveedor_id' => $proveedoresCreados[1]->id, // Ingram Micro
                'usuario_id' => $usersCreados[0]->id,
                'fecha' => '2026-08-11',
                'estado' => 'aprobada',
                'observaciones' => 'Abastecimiento para proyecto Surperu (Servidores DL380)',
                'detalles' => [
                    [
                        'producto_id' => $productosCreados[0]->id,
                        'cantidad' => 2.00,
                        'precio_compra_unit' => 8500.00,
                    ],
                ],
            ],
            [
                'proveedor_id' => $proveedoresCreados[2]->id, // TD Synnex
                'usuario_id' => $usersCreados[0]->id,
                'fecha' => '2026-08-13',
                'estado' => 'recibida',
                'observaciones' => 'Compra de Switches Cisco Catalyst 9200L',
                'detalles' => [
                    [
                        'producto_id' => $productosCreados[1]->id,
                        'cantidad' => 3.00,
                        'precio_compra_unit' => 4200.00,
                    ],
                ],
            ],
            [
                'proveedor_id' => $proveedoresCreados[0]->id, // Deltron
                'usuario_id' => $usersCreados[1]->id,
                'fecha' => '2026-08-14',
                'estado' => 'aprobada',
                'observaciones' => 'Reposición de stock de UPS APC Online',
                'detalles' => [
                    [
                        'producto_id' => $productosCreados[2]->id,
                        'cantidad' => 2.00,
                        'precio_compra_unit' => 3400.00,
                    ],
                ],
            ],
            [
                'proveedor_id' => $proveedoresCreados[3]->id, // Fiber Store
                'usuario_id' => $usersCreados[0]->id,
                'fecha' => '2026-08-16',
                'estado' => 'pendiente',
                'observaciones' => 'Adquisición de bobinas de fibra óptica OM4',
                'detalles' => [
                    [
                        'producto_id' => $productosCreados[3]->id,
                        'cantidad' => 500.00,
                        'precio_compra_unit' => 12.50,
                    ],
                ],
            ],
            [
                'proveedor_id' => $proveedoresCreados[4]->id, // Global Services
                'usuario_id' => $usersCreados[1]->id,
                'fecha' => '2026-08-19',
                'estado' => 'pendiente',
                'observaciones' => 'Subcontratación de servicio de fusión en mina',
                'detalles' => [
                    [
                        'producto_id' => $productosCreados[4]->id,
                        'cantidad' => 1.00,
                        'precio_compra_unit' => 1500.00,
                    ],
                ],
            ],
        ];

        foreach ($ocsProveedor as $ocp) {
            $detalles = $ocp['detalles'];
            unset($ocp['detalles']);

            $orden = OrdenCompra::create($ocp);
            foreach ($detalles as $d) {
                OrdenCompraDetalle::create([
                    'orden_compra_id' => $orden->id,
                    'producto_id' => $d['producto_id'],
                    'cantidad' => $d['cantidad'],
                    'precio_compra_unit' => $d['precio_compra_unit'],
                ]);
            }
        }
    }
}
