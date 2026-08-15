<?php

use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\OrdenCompraClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('proveedores', ProveedorController::class);
    Route::resource('clientes', ClienteController::class);
    Route::resource('productos', ProductoController::class);
    Route::post('productos/{producto}/proveedores', [ProductoController::class, 'addProveedor'])->name('productos.proveedores.add');
    Route::delete('productos/{producto}/proveedores/{proveedor}', [ProductoController::class, 'removeProveedor'])->name('productos.proveedores.remove');
    Route::post('productos/{producto}/presentaciones', [ProductoController::class, 'addPresentacion'])->name('productos.presentaciones.add');
    Route::delete('productos/{producto}/presentaciones/{presentacion}', [ProductoController::class, 'removePresentacion'])->name('productos.presentaciones.remove');

    Route::get('/inventario/kardex', [MovimientoInventarioController::class, 'kardex'])->name('inventario.kardex');
    Route::get('/inventario/ajuste', [MovimientoInventarioController::class, 'createAjuste'])->name('inventario.ajuste.create');
    Route::post('/inventario/ajuste', [MovimientoInventarioController::class, 'storeAjuste'])->name('inventario.ajuste.store');

    Route::resource('ordenes-compra-cliente', OrdenCompraClienteController::class);

    Route::get('cotizaciones/{cotizacion}/pdf', [CotizacionController::class, 'pdf'])->name('cotizaciones.pdf');
    Route::patch('cotizaciones/{cotizacion}/aprobar', [CotizacionController::class, 'aprobar'])->name('cotizaciones.aprobar');
    Route::resource('cotizaciones', CotizacionController::class);

    Route::resource('users', UserController::class)
        ->middleware('rol:administrador');

    Route::get('/configuracion', [ConfiguracionController::class, 'edit'])
        ->middleware('rol:administrador')
        ->name('configuracion.edit');

    Route::put('/configuracion', [ConfiguracionController::class, 'update'])
        ->middleware('rol:administrador')
        ->name('configuracion.update');

    Route::get('/auditoria', [AuditoriaController::class, 'index'])
        ->middleware('rol:administrador')
        ->name('auditoria.index');
});

require __DIR__.'/auth.php';
