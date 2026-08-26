<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraCliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Support\Database\MySqlSchema;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $isMySql = MySqlSchema::isMySql();
        $anioActual = (int) date('Y');
        $anio = (int) $request->input('anio', $anioActual);
        $mes = $request->filled('mes') ? (int) $request->input('mes') : null;
        $rangoRapido = $request->input('rango', null);

        // Ajustar año y mes si se usa filtro rápido
        if ($rangoRapido === 'este_mes') {
            $anio = (int) date('Y');
            $mes = (int) date('m');
        } elseif ($rangoRapido === 'este_anio') {
            $anio = (int) date('Y');
            $mes = null;
        }

        // Años disponibles para selector
        $yearSql = $isMySql ? 'DISTINCT YEAR(fecha) as anio' : 'DISTINCT CAST(strftime("%Y", fecha) AS INTEGER) as anio';
        $aniosVentas = Venta::selectRaw($yearSql)
            ->whereNotNull('fecha')
            ->pluck('anio')
            ->map(fn ($a) => (int) $a)
            ->filter(fn ($a) => $a > 2000)
            ->all();

        $aniosDisponibles = array_unique(array_merge([$anioActual, $anioActual - 1], $aniosVentas));
        sort($aniosDisponibles);

        // 1. Query base para ventas del periodo
        $ventasQuery = Venta::query();
        if ($anio > 0) {
            $ventasQuery->whereYear('fecha', $anio);
        }
        if ($mes && $mes >= 1 && $mes <= 12) {
            $ventasQuery->whereMonth('fecha', $mes);
        }

        $totalVentas = (float) (clone $ventasQuery)->sum('total');
        $subtotalVentas = (float) (clone $ventasQuery)->sum('subtotal');
        $cantidadVentas = (int) (clone $ventasQuery)->count();

        // Margen y Ganancia en Soles y %
        $gananciaVentas = (float) (VentaDetalle::whereHas('venta', function ($q) use ($anio, $mes) {
            if ($anio > 0) {
                $q->whereYear('fecha', $anio);
            }
            if ($mes && $mes >= 1 && $mes <= 12) {
                $q->whereMonth('fecha', $mes);
            }
        })->selectRaw('SUM((precio_venta_unit - precio_compra_unit) * cantidad) as ganancia_total')
          ->value('ganancia_total') ?? 0);

        $margenPct = $subtotalVentas > 0 ? round(($gananciaVentas / $subtotalVentas) * 100, 1) : 0;

        // 2. Cotizaciones y Operaciones Comerciales
        $cotizacionesQuery = Cotizacion::query();
        if ($anio > 0) {
            $cotizacionesQuery->whereYear('fecha', $anio);
        }
        if ($mes && $mes >= 1 && $mes <= 12) {
            $cotizacionesQuery->whereMonth('fecha', $mes);
        }

        $totalCotizaciones = (int) (clone $cotizacionesQuery)->count();
        $cotizacionesPendientes = (int) (clone $cotizacionesQuery)->where('estado', 'pendiente')->count();
        $cotizacionesAprobadas = (int) (clone $cotizacionesQuery)->where('estado', 'aprobada')->count();
        $cotizacionesFacturadas = (int) (clone $cotizacionesQuery)->where('estado', 'facturada')->count();
        $cotizacionesRechazadas = (int) (clone $cotizacionesQuery)->where('estado', 'rechazada')->count();

        $tasaConversion = $totalCotizaciones > 0 
            ? round((($cotizacionesAprobadas + $cotizacionesFacturadas) / $totalCotizaciones) * 100, 1) 
            : 0;

        // Órdenes de compra de clientes pendientes
        $ocClientesPendientes = OrdenCompraCliente::where('estado', 'pendiente')->count();
        $ocProveedoresPendientes = OrdenCompra::where('estado', 'pendiente')->count();

        // 3. Gráfico 1: Ventas Mensuales (Ene - Dic)
        $ventasMensualesData = array_fill(1, 12, 0.0);
        $monthSql = $isMySql 
            ? 'MONTH(fecha) as mes_num, SUM(total) as monto_mes' 
            : 'CAST(strftime("%m", fecha) AS INTEGER) as mes_num, SUM(total) as monto_mes';

        $ventasPorMes = Venta::whereYear('fecha', $anio)
            ->selectRaw($monthSql)
            ->groupBy('mes_num')
            ->get();

        foreach ($ventasPorMes as $vpm) {
            $mIndex = (int) $vpm->mes_num;
            if ($mIndex >= 1 && $mIndex <= 12) {
                $ventasMensualesData[$mIndex] = round((float) $vpm->monto_mes, 2);
            }
        }

        // 4. Gráfico 2: Ventas por Producto (Top 7)
        $topProductos = VentaDetalle::select('producto_id', DB::raw('SUM(cantidad * precio_venta_unit) as total_monto'), DB::raw('SUM(cantidad) as total_cantidad'))
            ->whereHas('venta', function ($q) use ($anio, $mes) {
                if ($anio > 0) {
                    $q->whereYear('fecha', $anio);
                }
                if ($mes && $mes >= 1 && $mes <= 12) {
                    $q->whereMonth('fecha', $mes);
                }
            })
            ->groupBy('producto_id')
            ->orderByDesc('total_monto')
            ->limit(7)
            ->with('producto')
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->producto->nombre ?? 'Producto #' . $item->producto_id,
                    'sku' => $item->producto->sku ?? '—',
                    'monto' => round((float) $item->total_monto, 2),
                    'cantidad' => (float) $item->total_cantidad,
                ];
            });

        // 5. Gráfico 3: Medios / Formas de Pago
        $formasPago = Cotizacion::whereNotNull('forma_pago')
            ->where('forma_pago', '!=', '')
            ->select('forma_pago', DB::raw('count(*) as total'))
            ->groupBy('forma_pago')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // 6. Gráfico 4: Ventas Diarias del Mes
        $mesParaDiario = $mes ?: (int) date('m');
        $diasEnMes = Carbon::createFromDate($anio, $mesParaDiario, 1)->daysInMonth;
        $ventasDiariasData = array_fill(1, $diasEnMes, 0.0);

        $daySql = $isMySql 
            ? 'DAY(fecha) as dia_num, SUM(total) as monto_dia' 
            : 'CAST(strftime("%d", fecha) AS INTEGER) as dia_num, SUM(total) as monto_dia';

        $ventasPorDia = Venta::whereYear('fecha', $anio)
            ->whereMonth('fecha', $mesParaDiario)
            ->selectRaw($daySql)
            ->groupBy('dia_num')
            ->get();

        foreach ($ventasPorDia as $vpd) {
            $dIndex = (int) $vpd->dia_num;
            if ($dIndex >= 1 && $dIndex <= $diasEnMes) {
                $ventasDiariasData[$dIndex] = round((float) $vpd->monto_dia, 2);
            }
        }

        // 7. Gráfico 5: Distribución por Tipo de Cotización
        $tiposCotizacion = Cotizacion::select('tipo_cotizacion', DB::raw('count(*) as total'))
            ->whereYear('fecha', $anio)
            ->groupBy('tipo_cotizacion')
            ->pluck('total', 'tipo_cotizacion')
            ->all();

        $tipoProductosCount = (int) ($tiposCotizacion['producto'] ?? 0);
        $tipoServiciosCount = (int) ($tiposCotizacion['servicio'] ?? 0);

        // 8. Top 5 Clientes
        $topClientes = Venta::select('cliente_id', DB::raw('SUM(total) as total_comprado'), DB::raw('count(*) as cantidad_compras'))
            ->whereYear('fecha', $anio)
            ->groupBy('cliente_id')
            ->orderByDesc('total_comprado')
            ->limit(5)
            ->with('cliente')
            ->get();

        // 9. Alertas de Stock Bajo
        $productosStockBajo = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy('stock_actual', 'asc')
            ->limit(6)
            ->get();

        // 10. Últimas Ventas Recientes
        $ultimasVentas = Venta::with('cliente', 'usuario')
            ->latest('created_at')
            ->limit(5)
            ->get();

        // Nombres de meses en español
        $mesesNombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        return view('dashboard', compact(
            'anio',
            'mes',
            'rangoRapido',
            'aniosDisponibles',
            'mesesNombres',
            'totalVentas',
            'subtotalVentas',
            'cantidadVentas',
            'gananciaVentas',
            'margenPct',
            'totalCotizaciones',
            'cotizacionesPendientes',
            'cotizacionesAprobadas',
            'cotizacionesFacturadas',
            'cotizacionesRechazadas',
            'tasaConversion',
            'ocClientesPendientes',
            'ocProveedoresPendientes',
            'ventasMensualesData',
            'topProductos',
            'formasPago',
            'ventasDiariasData',
            'diasEnMes',
            'mesParaDiario',
            'tipoProductosCount',
            'tipoServiciosCount',
            'topClientes',
            'productosStockBajo',
            'ultimasVentas'
        ));
    }
}
