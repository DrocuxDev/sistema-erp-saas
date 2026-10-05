<?php

use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ComprobanteVentaController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\DevolucionVentaController;
use App\Http\Controllers\DevolucionCompraController;
use App\Http\Controllers\CobroVentaController;
use App\Http\Controllers\PagoCompraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuiaRemisionController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\OrdenCompraClienteController;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\SerieDocumentoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }

    return redirect('/login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'modulo:dashboard'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/mi-perfil', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Proveedores (parameters explícito: el singularizador inglés genera {proveedore} y rompe el binding con $proveedor)
    Route::resource('proveedores', ProveedorController::class)
        ->parameters(['proveedores' => 'proveedor'])
        ->middleware('modulo:proveedores');

    // Clientes
    Route::resource('clientes', ClienteController::class)->middleware('modulo:clientes');

    // Productos
    Route::middleware('modulo:productos')->group(function () {
        Route::resource('productos', ProductoController::class);
        Route::post('productos/{producto}/proveedores', [ProductoController::class, 'addProveedor'])->name('productos.proveedores.add');
        Route::delete('productos/{producto}/proveedores/{proveedor}', [ProductoController::class, 'removeProveedor'])->name('productos.proveedores.remove');
        Route::post('productos/{producto}/presentaciones', [ProductoController::class, 'addPresentacion'])->name('productos.presentaciones.add');
        Route::delete('productos/{producto}/presentaciones/{presentacion}', [ProductoController::class, 'removePresentacion'])->name('productos.presentaciones.remove');
        Route::patch('productos/{producto}/presentaciones/{presentacion}/preferida', [ProductoController::class, 'preferPresentacion'])->name('productos.presentaciones.prefer');
        Route::post('productos/{producto}/presentaciones-venta', [ProductoController::class, 'addPresentacionVenta'])->name('productos.presentaciones-venta.add');
        Route::delete('productos/{producto}/presentaciones-venta/{presentacion}', [ProductoController::class, 'removePresentacionVenta'])->name('productos.presentaciones-venta.remove');
        Route::patch('productos/{producto}/presentaciones-venta/{presentacion}/preferida', [ProductoController::class, 'preferPresentacionVenta'])->name('productos.presentaciones-venta.prefer');
    });

    // Inventario
    Route::middleware('modulo:inventario')->group(function () {
        Route::get('/inventario/kardex', [MovimientoInventarioController::class, 'kardex'])->name('inventario.kardex');
        Route::get('/inventario/ajuste', [MovimientoInventarioController::class, 'createAjuste'])->middleware('accion:ajustar_inventario')->name('inventario.ajuste.create');
        Route::post('/inventario/ajuste', [MovimientoInventarioController::class, 'storeAjuste'])->middleware('accion:ajustar_inventario')->name('inventario.ajuste.store');
    });

    // Órdenes de Compra de Cliente
    Route::middleware('modulo:ordenes_compra_cliente')->group(function () {
        Route::get('ordenes-compra-cliente/{ordenesCompraCliente}/archivo', [OrdenCompraClienteController::class, 'archivo'])->name('ordenes-compra-cliente.archivo');
        Route::resource('ordenes-compra-cliente', OrdenCompraClienteController::class)
            ->parameters(['ordenes-compra-cliente' => 'ordenesCompraCliente']);
    });

    // Cotizaciones
    Route::middleware('modulo:cotizaciones')->group(function () {
        Route::get('productos-buscar', [ProductoController::class, 'search'])->name('productos.buscar');
        Route::post('productos-rapido', [ProductoController::class, 'quickStore'])->name('productos.rapido');
        Route::get('cotizaciones/{cotizacion}/pdf', [CotizacionController::class, 'pdf'])->name('cotizaciones.pdf');
        Route::get('cotizaciones/{cotizacion}/pdf/descargar', [CotizacionController::class, 'pdfDescargar'])->name('cotizaciones.pdf.descargar');
        Route::patch('cotizaciones/{cotizacion}/aprobar', [CotizacionController::class, 'aprobar'])->middleware('accion:aprobar')->name('cotizaciones.aprobar');
        Route::resource('cotizaciones', CotizacionController::class)
            ->parameters(['cotizaciones' => 'cotizacion']);
    });

    // Ventas
    Route::middleware('modulo:ventas')->group(function () {
        Route::get('ventas/{venta}/cobros/crear', [CobroVentaController::class, 'create'])->middleware('accion:finanzas')->name('ventas.cobros.create');
        Route::post('ventas/{venta}/cobros', [CobroVentaController::class, 'store'])->middleware('accion:finanzas')->name('ventas.cobros.store');
        Route::patch('ventas/{venta}/cobros/{cobro}/anular', [CobroVentaController::class, 'anular'])->middleware('accion:finanzas')->name('ventas.cobros.anular');
        Route::get('ventas/{venta}/devoluciones/crear', [DevolucionVentaController::class, 'create'])->middleware('accion:anular')->name('ventas.devoluciones.create');
        Route::post('ventas/{venta}/devoluciones', [DevolucionVentaController::class, 'store'])->middleware('accion:anular')->name('ventas.devoluciones.store');
        Route::get('ventas/{venta}/pdf', [VentaController::class, 'pdf'])->name('ventas.pdf');
        Route::get('ventas/{venta}/pdf/descargar', [VentaController::class, 'descargarPdf'])->name('ventas.pdf.descargar');
        Route::resource('ventas', VentaController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['ventas' => 'venta']);
    });

    // Comprobantes de Venta (Facturas / Boletas / Notas de Venta)
    Route::middleware('modulo:comprobantes')->group(function () {
        Route::get('comprobantes/{comprobante}/pdf', [ComprobanteVentaController::class, 'pdf'])->name('comprobantes.pdf');
        Route::get('comprobantes/{comprobante}/pdf/descargar', [ComprobanteVentaController::class, 'descargarPdf'])->name('comprobantes.pdf.descargar');
        Route::patch('comprobantes/{comprobante}/anular', [ComprobanteVentaController::class, 'anular'])->middleware('accion:anular')->name('comprobantes.anular');
        Route::resource('comprobantes', ComprobanteVentaController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['comprobantes' => 'comprobante']);

        Route::get('guias-remision/{guia_remision}/pdf', [GuiaRemisionController::class, 'pdf'])->name('guias-remision.pdf');
        Route::get('guias-remision/{guia_remision}/pdf/descargar', [GuiaRemisionController::class, 'descargarPdf'])->name('guias-remision.pdf.descargar');
        Route::patch('guias-remision/{guia_remision}/anular', [GuiaRemisionController::class, 'anular'])->middleware('accion:anular')->name('guias-remision.anular');
        Route::resource('guias-remision', GuiaRemisionController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['guias-remision' => 'guia_remision']);
    });

    // Órdenes de Compra a Proveedores
    Route::middleware('modulo:compras')->group(function () {
        Route::get('compras/{compra}/pagos/crear', [PagoCompraController::class, 'create'])->middleware('accion:finanzas')->name('compras.pagos.create');
        Route::post('compras/{compra}/pagos', [PagoCompraController::class, 'store'])->middleware('accion:finanzas')->name('compras.pagos.store');
        Route::patch('compras/{compra}/pagos/{pago}/anular', [PagoCompraController::class, 'anular'])->middleware('accion:finanzas')->name('compras.pagos.anular');
        Route::get('compras/{compra}/devoluciones/crear', [DevolucionCompraController::class, 'create'])->middleware('accion:anular')->name('compras.devoluciones.create');
        Route::post('compras/{compra}/devoluciones', [DevolucionCompraController::class, 'store'])->middleware('accion:anular')->name('compras.devoluciones.store');
        Route::get('ordenes-compra/{orden_compra}/pdf', [OrdenCompraController::class, 'pdf'])->name('ordenes-compra.pdf');
        Route::get('ordenes-compra/{orden_compra}/pdf/descargar', [OrdenCompraController::class, 'descargarPdf'])->name('ordenes-compra.pdf.descargar');
        Route::patch('ordenes-compra/{orden_compra}/aprobar', [OrdenCompraController::class, 'aprobar'])->middleware('accion:aprobar')->name('ordenes-compra.aprobar');
        Route::patch('ordenes-compra/{orden_compra}/anular', [OrdenCompraController::class, 'anular'])->middleware('accion:anular')->name('ordenes-compra.anular');
        Route::resource('ordenes-compra', OrdenCompraController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['ordenes-compra' => 'orden_compra']);

        Route::resource('compras', CompraController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['compras' => 'compra']);
    });

    // Administración de Usuarios y Roles
    Route::middleware('modulo:usuarios')->group(function () {
        Route::patch('users/{user}/toggle-activo', [UserController::class, 'toggleActivo'])->name('users.toggle-activo');
        Route::resource('users', UserController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('roles', RolController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->middleware('superadmin');
    });

    // Configuración y Series de Documentos
    Route::middleware('modulo:configuracion')->group(function () {
        Route::patch('series-documentos/{series_documento}/toggle-activo', [SerieDocumentoController::class, 'toggleActivo'])->name('series-documentos.toggle-activo');
        Route::resource('series-documentos', SerieDocumentoController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::get('/configuracion', [ConfiguracionController::class, 'edit'])->name('configuracion.edit');
        Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
        Route::get('/configuracion/pdf-ejemplo', [ConfiguracionController::class, 'pdfEjemplo'])->name('configuracion.pdf-ejemplo');
        Route::delete('/configuracion/logo', [ConfiguracionController::class, 'destroyLogo'])->name('configuracion.logo.destroy');
        Route::delete('/configuracion/portada', [ConfiguracionController::class, 'destroyPortada'])->name('configuracion.portada.destroy');
        Route::post('/configuracion/cuentas', [ConfiguracionController::class, 'storeCuenta'])->name('configuracion.cuentas.store');
        Route::put('/configuracion/cuentas/{cuenta}', [ConfiguracionController::class, 'updateCuenta'])->name('configuracion.cuentas.update');
        Route::delete('/configuracion/cuentas/{cuenta}', [ConfiguracionController::class, 'destroyCuenta'])->name('configuracion.cuentas.destroy');
    });

    // Auditoría
    Route::get('/auditoria', [AuditoriaController::class, 'index'])
        ->middleware('modulo:auditoria')
        ->name('auditoria.index');
});

require __DIR__.'/auth.php';
