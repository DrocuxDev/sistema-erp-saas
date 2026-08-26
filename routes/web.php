<?php

use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\OrdenCompraClienteController;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\SerieDocumentoController;
use App\Http\Controllers\DashboardController;
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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Proveedores
    Route::resource('proveedores', ProveedorController::class)->middleware('modulo:proveedores');

    // Clientes
    Route::resource('clientes', ClienteController::class)->middleware('modulo:clientes');

    // Productos
    Route::middleware('modulo:productos')->group(function () {
        Route::resource('productos', ProductoController::class);
        Route::post('productos/{producto}/proveedores', [ProductoController::class, 'addProveedor'])->name('productos.proveedores.add');
        Route::delete('productos/{producto}/proveedores/{proveedor}', [ProductoController::class, 'removeProveedor'])->name('productos.proveedores.remove');
        Route::post('productos/{producto}/presentaciones', [ProductoController::class, 'addPresentacion'])->name('productos.presentaciones.add');
        Route::delete('productos/{producto}/presentaciones/{presentacion}', [ProductoController::class, 'removePresentacion'])->name('productos.presentaciones.remove');
    });

    // Inventario
    Route::middleware('modulo:inventario')->group(function () {
        Route::get('/inventario/kardex', [MovimientoInventarioController::class, 'kardex'])->name('inventario.kardex');
        Route::get('/inventario/ajuste', [MovimientoInventarioController::class, 'createAjuste'])->name('inventario.ajuste.create');
        Route::post('/inventario/ajuste', [MovimientoInventarioController::class, 'storeAjuste'])->name('inventario.ajuste.store');
    });

    // Órdenes de Compra de Cliente
    Route::middleware('modulo:ordenes_compra_cliente')->group(function () {
        Route::get('ordenes-compra-cliente/{ordenesCompraCliente}/archivo', [OrdenCompraClienteController::class, 'archivo'])->name('ordenes-compra-cliente.archivo');
        Route::resource('ordenes-compra-cliente', OrdenCompraClienteController::class);
    });

    // Cotizaciones
    Route::middleware('modulo:cotizaciones')->group(function () {
        Route::get('cotizaciones/{cotizacion}/pdf', [CotizacionController::class, 'pdf'])->name('cotizaciones.pdf');
        Route::get('cotizaciones/{cotizacion}/pdf/descargar', [CotizacionController::class, 'pdfDescargar'])->name('cotizaciones.pdf.descargar');
        Route::patch('cotizaciones/{cotizacion}/aprobar', [CotizacionController::class, 'aprobar'])->name('cotizaciones.aprobar');
        Route::resource('cotizaciones', CotizacionController::class)
            ->parameters(['cotizaciones' => 'cotizacion']);
    });

    // Ventas
    Route::middleware('modulo:ventas')->group(function () {
        Route::get('ventas/{venta}/pdf', [VentaController::class, 'pdf'])->name('ventas.pdf');
        Route::get('ventas/{venta}/pdf/descargar', [VentaController::class, 'descargarPdf'])->name('ventas.pdf.descargar');
        Route::resource('ventas', VentaController::class)
            ->parameters(['ventas' => 'venta']);
    });

    // Órdenes de Compra a Proveedores
    Route::middleware('modulo:compras')->group(function () {
        Route::get('ordenes-compra/{orden_compra}/pdf', [OrdenCompraController::class, 'pdf'])->name('ordenes-compra.pdf');
        Route::get('ordenes-compra/{orden_compra}/pdf/descargar', [OrdenCompraController::class, 'descargarPdf'])->name('ordenes-compra.pdf.descargar');
        Route::patch('ordenes-compra/{orden_compra}/aprobar', [OrdenCompraController::class, 'aprobar'])->name('ordenes-compra.aprobar');
        Route::patch('ordenes-compra/{orden_compra}/anular', [OrdenCompraController::class, 'anular'])->name('ordenes-compra.anular');
        Route::resource('ordenes-compra', OrdenCompraController::class)
            ->parameters(['ordenes-compra' => 'orden_compra']);
    });

    // Administración de Usuarios y Roles
    Route::middleware('modulo:usuarios')->group(function () {
        Route::patch('users/{user}/toggle-activo', [UserController::class, 'toggleActivo'])->name('users.toggle-activo');
        Route::resource('users', UserController::class);
        Route::resource('roles', RolController::class);
    });

    // Configuración y Series de Documentos
    Route::middleware('modulo:configuracion')->group(function () {
        Route::patch('series-documentos/{series_documento}/toggle-activo', [SerieDocumentoController::class, 'toggleActivo'])->name('series-documentos.toggle-activo');
        Route::resource('series-documentos', SerieDocumentoController::class);
        Route::get('/configuracion', [ConfiguracionController::class, 'edit'])->name('configuracion.edit');
        Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
    });

    // Auditoría
    Route::get('/auditoria', [AuditoriaController::class, 'index'])
        ->middleware('modulo:auditoria')
        ->name('auditoria.index');
});

require __DIR__.'/auth.php';
