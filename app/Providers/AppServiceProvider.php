<?php

namespace App\Providers;

use App\Models\Cliente;
use App\Models\CobroVenta;
use App\Models\Compra;
use App\Models\ComprobanteVenta;
use App\Models\Cotizacion;
use App\Models\DevolucionVenta;
use App\Models\DevolucionCompra;
use App\Models\GuiaRemision;
use App\Models\MovimientoInventario;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraCliente;
use App\Models\PagoCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\SerieDocumento;
use App\Models\User;
use App\Models\Venta;
use App\Observers\AuditoriaObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([Cotizacion::class, Venta::class, DevolucionVenta::class, CobroVenta::class, Compra::class, DevolucionCompra::class, PagoCompra::class, ComprobanteVenta::class, GuiaRemision::class, OrdenCompra::class, OrdenCompraCliente::class, User::class, Rol::class, Producto::class, Cliente::class, Proveedor::class, MovimientoInventario::class, SerieDocumento::class] as $modelo) {
            $modelo::observe(AuditoriaObserver::class);
        }
    }
}
