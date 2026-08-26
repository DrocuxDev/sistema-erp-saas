<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-cyan-600 dark:text-cyan-400 uppercase tracking-wider mb-1">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Panel Ejecutivo de Inteligencia Comercial</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white flex items-center gap-3">
                    Dashboard de Ventas y Operaciones
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Monitoreo en tiempo real de facturación, márgenes, rentabilidad, cotizaciones y stock.
                </p>
            </div>

            <!-- Filtros de Fecha / Periodo -->
            <form method="GET" action="{{ route('dashboard') }}" id="filtro-form" class="flex flex-wrap items-center gap-2">
                <div class="flex items-center bg-white dark:bg-card-bg p-1 rounded-xl border border-gray-200 dark:border-gray-700/80 shadow-sm">
                    <select name="anio" onchange="document.getElementById('filtro-form').submit()"
                            class="bg-transparent border-0 text-xs font-bold text-gray-800 dark:text-gray-200 focus:ring-0 cursor-pointer py-1.5 pl-3 pr-8">
                        @foreach($aniosDisponibles as $a)
                            <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }} class="dark:bg-slate-900">Año {{ $a }}</option>
                        @endforeach
                    </select>

                    <span class="text-gray-300 dark:text-gray-600">|</span>

                    <select name="mes" onchange="document.getElementById('filtro-form').submit()"
                            class="bg-transparent border-0 text-xs font-semibold text-gray-700 dark:text-gray-300 focus:ring-0 cursor-pointer py-1.5 pl-3 pr-8">
                        <option value="" {{ is_null($mes) ? 'selected' : '' }} class="dark:bg-slate-900">Todos los Meses</option>
                        @foreach($mesesNombres as $numMes => $nomMes)
                            <option value="{{ $numMes }}" {{ $mes == $numMes ? 'selected' : '' }} class="dark:bg-slate-900">{{ $nomMes }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Botones de Acceso Rápido -->
                <a href="{{ route('dashboard', ['rango' => 'este_mes']) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $rangoRapido === 'este_mes' || ($mes == (int)date('m') && $anio == (int)date('Y')) ? 'bg-cyan-600 text-white shadow-md shadow-cyan-600/20' : 'bg-white dark:bg-card-bg text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700/80 hover:bg-gray-50' }}">
                    Este Mes
                </a>
                <a href="{{ route('dashboard', ['rango' => 'este_anio']) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $rangoRapido === 'este_anio' || (is_null($mes) && $anio == (int)date('Y')) ? 'bg-cyan-600 text-white shadow-md shadow-cyan-600/20' : 'bg-white dark:bg-card-bg text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700/80 hover:bg-gray-50' }}">
                    Año {{ date('Y') }}
                </a>
            </form>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- 1. HEADER DE MÉTRICAS CLAVE (KPIs ESTILO PLANILLA EXCEL / BUSINESS INTELLIGENCE) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                
                <!-- KPI 1: VENTAS TOTALES -->
                <div class="relative overflow-hidden bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/60 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider block">
                                Ventas Totales
                            </span>
                            <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white mt-1 font-mono">
                                S/ {{ number_format($totalVentas, 2) }}
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/60 flex items-center justify-center text-rose-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 pt-2.5">
                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ $cantidadVentas }}</span> ventas concretadas
                        <span class="text-gray-300 dark:text-gray-600">•</span>
                        <span>Serie V001</span>
                    </div>
                </div>

                <!-- KPI 2: MARGEN BRUTO S/ -->
                <div class="relative overflow-hidden bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/60 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-cyan-600 dark:text-cyan-400 uppercase tracking-wider block">
                                Margen S/ (Ganancia)
                            </span>
                            <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white mt-1 font-mono">
                                S/ {{ number_format($gananciaVentas, 2) }}
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-cyan-50 dark:bg-cyan-950/50 border border-cyan-200 dark:border-cyan-800/60 flex items-center justify-center text-cyan-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 pt-2.5">
                        <span>Costo Compra: <strong class="text-gray-800 dark:text-gray-200 font-mono">S/ {{ number_format(max(0, $subtotalVentas - $gananciaVentas), 2) }}</strong></span>
                    </div>
                </div>

                <!-- KPI 3: MARGEN % / RENTABILIDAD -->
                <div class="relative overflow-hidden bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/60 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">
                                Margen Promedio %
                            </span>
                            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 font-mono">
                                {{ number_format($margenPct, 1) }}%
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/60 flex items-center justify-center text-emerald-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 pt-2.5">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $margenPct >= 15 ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300' }}">
                            {{ $margenPct >= 15 ? 'Rentabilidad Óptima' : 'Margen Estándar' }}
                        </span>
                    </div>
                </div>

                <!-- KPI 4: CONVERSIÓN & COTIZACIONES -->
                <div class="relative overflow-hidden bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/60 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider block">
                                Conversión Cotizaciones
                            </span>
                            <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white mt-1 font-mono">
                                {{ number_format($tasaConversion, 1) }}%
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-800/60 flex items-center justify-center text-purple-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 pt-2.5">
                        <span>{{ $cotizacionesPendientes }} pendientes</span>
                        <span class="text-cyan-600 dark:text-cyan-400 font-semibold">{{ $totalCotizaciones }} emitidas</span>
                    </div>
                </div>
            </div>

            <!-- 2. GRID PRINCIPAL DE GRÁFICOS INTERACTIVOS (CHART.JS) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- GRÁFICO 1: VENTAS MENSUALES (COLUMNAS / BARRAS) -->
                <div class="lg:col-span-2 bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/60 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                Facturación Mensual (Año {{ $anio }})
                            </h3>
                            <p class="text-xs text-gray-500">Monto total facturado por mes en Soles (S/)</p>
                        </div>
                        <span class="text-xs font-mono font-bold text-cyan-600 dark:text-cyan-400 px-3 py-1 rounded-lg bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-200 dark:border-cyan-800/60">
                            Total: S/ {{ number_format($totalVentas, 2) }}
                        </span>
                    </div>
                    <div class="h-64 sm:h-72 w-full relative">
                        <canvas id="chartVentasMensuales"></canvas>
                    </div>
                </div>

                <!-- GRÁFICO 2: MEDIOS Y FORMAS DE PAGO (DONUT) -->
                <div class="bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/60 shadow-sm space-y-4">
                    <div class="border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                            Condiciones y Medios de Pago
                        </h3>
                        <p class="text-xs text-gray-500">Distribución de formas de pago pactadas</p>
                    </div>
                    <div class="h-64 sm:h-72 w-full relative flex items-center justify-center">
                        <canvas id="chartMediosPago"></canvas>
                    </div>
                </div>

                <!-- GRÁFICO 3: VENTAS POR PRODUCTO (BARRAS HORIZONTALES - TOP 7) -->
                <div class="lg:col-span-2 bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/60 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                Top Productos por Facturación
                            </h3>
                            <p class="text-xs text-gray-500">Bienes con mayor volumen y valor monetario comercializado</p>
                        </div>
                        <a href="{{ route('productos.index') }}" class="text-xs text-cyan-600 dark:text-cyan-400 font-semibold hover:underline">
                            Ver catálogo →
                        </a>
                    </div>
                    <div class="h-64 sm:h-72 w-full relative">
                        <canvas id="chartTopProductos"></canvas>
                    </div>
                </div>

                <!-- GRÁFICO 4: DISTRIBUCIÓN POR TIPO DE NEGOCIO (PIE) -->
                <div class="bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/60 shadow-sm space-y-4">
                    <div class="border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                            Líneas de Negocio
                        </h3>
                        <p class="text-xs text-gray-500">Hardware / Equipamiento vs Servicios TI</p>
                    </div>
                    <div class="h-64 sm:h-72 w-full relative flex items-center justify-center">
                        <canvas id="chartLineasNegocio"></canvas>
                    </div>
                </div>

                <!-- GRÁFICO 5: EVOLUCIÓN DIARIA DE VENTAS (ÁREA CHART) -->
                <div class="lg:col-span-3 bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/60 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                                Tendencia Diaria de Ventas ({{ $mesesNombres[$mesParaDiario] }} {{ $anio }})
                            </h3>
                            <p class="text-xs text-gray-500">Comportamiento día a día de las operaciones de venta</p>
                        </div>
                        <span class="text-xs text-gray-400 font-mono">Días 1 al {{ $diasEnMes }}</span>
                    </div>
                    <div class="h-56 sm:h-64 w-full relative">
                        <canvas id="chartVentasDiarias"></canvas>
                    </div>
                </div>

            </div>

            <!-- 3. SECCIÓN INFERIOR: INTELIGENCIA DE NEGOCIO Y ALERTAS OPERATIVAS -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- TOP CLIENTES -->
                <div class="bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/60 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                            Top Clientes del Año
                        </h3>
                        <a href="{{ route('clientes.index') }}" class="text-xs text-cyan-600 dark:text-cyan-400 font-semibold hover:underline">Ver todos</a>
                    </div>
                    <div class="space-y-3">
                        @php $maxComprado = $topClientes->max('total_comprado') ?: 1; @endphp
                        @forelse($topClientes as $idx => $tc)
                            @php 
                                $porcentaje = round(((float)$tc->total_comprado / (float)$maxComprado) * 100, 0); 
                            @endphp
                            <div class="space-y-1 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-gray-800 dark:text-gray-200 truncate max-w-[200px]">
                                        {{ $idx + 1 }}. {{ $tc->cliente->nombre ?? 'Cliente #' . $tc->cliente_id }}
                                    </span>
                                    <span class="font-mono font-bold text-gray-900 dark:text-white">
                                        S/ {{ number_format($tc->total_comprado, 2) }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-700/60 h-2 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-cyan-500 to-emerald-500 h-full rounded-full" style="width: {{ $porcentaje }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 py-6 text-center">No hay registros de clientes en este periodo.</p>
                        @endforelse
                    </div>
                </div>

                <!-- ALERTAS DE STOCK CRÍTICO -->
                <div class="bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/60 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                            Alertas de Stock Bajo
                        </h3>
                        <a href="{{ route('inventario.kardex') }}" class="text-xs text-rose-600 dark:text-rose-400 font-semibold hover:underline">Kardex →</a>
                    </div>
                    <div class="space-y-2.5">
                        @forelse($productosStockBajo as $pb)
                            <div class="p-2.5 rounded-xl bg-rose-50/60 dark:bg-rose-950/30 border border-rose-200/70 dark:border-rose-800/50 flex items-center justify-between text-xs">
                                <div class="min-w-0 pr-2">
                                    <p class="font-bold text-gray-900 dark:text-white truncate">{{ $pb->nombre }}</p>
                                    <p class="text-[10px] text-gray-500 font-mono">SKU: {{ $pb->sku }}</p>
                                </div>
                                <div class="text-right shrink-0 font-mono">
                                    <span class="px-2 py-0.5 rounded-md bg-rose-600 text-white font-bold text-[11px]">{{ $pb->stock_actual }} {{ $pb->unidad_medida ?: 'UND' }}</span>
                                    <p class="text-[9px] text-gray-400 mt-0.5">mín: {{ $pb->stock_minimo }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                ✓ Todo el inventario se encuentra en niveles óptimos.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- ÚLTIMAS VENTAS REGISTRADAS -->
                <div class="bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/60 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Últimas Ventas Emitidas
                        </h3>
                        <a href="{{ route('ventas.index') }}" class="text-xs text-cyan-600 dark:text-cyan-400 font-semibold hover:underline">Ver todas →</a>
                    </div>
                    <div class="space-y-2.5">
                        @forelse($ultimasVentas as $uv)
                            <a href="{{ route('ventas.show', $uv) }}" class="block p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-darker-bg/60 border border-gray-100 dark:border-gray-700/60 transition-all text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono font-bold text-cyan-600 dark:text-cyan-400">{{ $uv->codigo_formateado }}</span>
                                    <span class="font-mono font-bold text-gray-900 dark:text-white">S/ {{ number_format($uv->total, 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-gray-500 mt-1">
                                    <span class="truncate max-w-[170px]">{{ $uv->cliente->nombre ?? '—' }}</span>
                                    <span>{{ $uv->fecha?->format('d/m/Y') }}</span>
                                </div>
                            </a>
                        @empty
                            <p class="text-xs text-gray-400 py-6 text-center">No hay ventas registradas aún.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- 4. INICIALIZACIÓN DE LOS 5 GRÁFICOS CHART.JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') {
                console.error('Chart.js no está cargado');
                return;
            }

            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#94A3B8' : '#64748B';
            const gridColor = isDark ? 'rgba(51, 65, 85, 0.35)' : 'rgba(226, 232, 240, 0.8)';

            // 1. VENTAS MENSUALES (BAR CHART)
            const ctxVentas = document.getElementById('chartVentasMensuales')?.getContext('2d');
            if (ctxVentas) {
                const mesesLabels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];
                const dataVentasMes = {!! json_encode(array_values($ventasMensualesData)) !!};

                new Chart(ctxVentas, {
                    type: 'bar',
                    data: {
                        labels: mesesLabels,
                        datasets: [{
                            label: 'Ventas (S/)',
                            data: dataVentasMes,
                            backgroundColor: 'rgba(244, 63, 94, 0.85)', // Rose Neón
                            hoverBackgroundColor: 'rgba(225, 29, 72, 1)',
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        return ' Total: S/ ' + Number(ctx.raw || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: textColor, font: { size: 11, weight: 'bold' } }
                            },
                            y: {
                                grid: { color: gridColor },
                                ticks: {
                                    color: textColor,
                                    font: { size: 10 },
                                    callback: function(v) { return 'S/ ' + (v >= 1000 ? (v/1000) + 'k' : v); }
                                }
                            }
                        }
                    }
                });
            }

            // 2. MEDIOS / FORMAS DE PAGO (DONUT CHART)
            const ctxMedios = document.getElementById('chartMediosPago')?.getContext('2d');
            if (ctxMedios) {
                const fpLabels = {!! json_encode($formasPago->pluck('forma_pago')->all()) !!};
                const fpTotals = {!! json_encode($formasPago->pluck('total')->all()) !!};

                const fallbackLabels = ['Contado', 'Crédito 30 días', 'Crédito 15 días', 'Transferencia'];
                const fallbackTotals = [45, 30, 15, 10];

                new Chart(ctxMedios, {
                    type: 'doughnut',
                    data: {
                        labels: fpLabels.length ? fpLabels : fallbackLabels,
                        datasets: [{
                            data: fpTotals.length ? fpTotals : fallbackTotals,
                            backgroundColor: [
                                '#06B6D4', // Cyan Neón
                                '#10B981', // Emerald
                                '#F59E0B', // Amber
                                '#8B5CF6', // Purple
                                '#EC4899', // Pink
                            ],
                            borderWidth: 2,
                            borderColor: isDark ? '#111C3A' : '#FFFFFF'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: textColor, font: { size: 10, weight: '600' }, boxWidth: 12 }
                            }
                        }
                    }
                });
            }

            // 3. TOP PRODUCTOS (HORIZONTAL BAR CHART)
            const ctxProd = document.getElementById('chartTopProductos')?.getContext('2d');
            if (ctxProd) {
                const topProd = {!! json_encode($topProductos) !!};
                const pLabels = topProd.length ? topProd.map(p => p.nombre.length > 25 ? p.nombre.substring(0, 25) + '...' : p.nombre) : ['Sin ventas en periodo'];
                const pMontos = topProd.length ? topProd.map(p => p.monto) : [0];

                new Chart(ctxProd, {
                    type: 'bar',
                    data: {
                        labels: pLabels,
                        datasets: [{
                            axis: 'y',
                            label: 'Total Facturado (S/)',
                            data: pMontos,
                            backgroundColor: 'rgba(16, 185, 129, 0.85)', // Emerald
                            hoverBackgroundColor: 'rgba(5, 150, 105, 1)',
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        return ' Facturado: S/ ' + Number(ctx.raw || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: gridColor },
                                ticks: {
                                    color: textColor,
                                    font: { size: 10 },
                                    callback: function(v) { return 'S/ ' + (v >= 1000 ? (v/1000) + 'k' : v); }
                                }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: textColor, font: { size: 11, weight: '600' } }
                            }
                        }
                    }
                });
            }

            // 4. LÍNEAS DE NEGOCIO (PIE CHART)
            const ctxLineas = document.getElementById('chartLineasNegocio')?.getContext('2d');
            if (ctxLineas) {
                const countProd = {{ $tipoProductosCount }};
                const countServ = {{ $tipoServiciosCount }};
                const hasData = (countProd + countServ) > 0;

                new Chart(ctxLineas, {
                    type: 'pie',
                    data: {
                        labels: ['Equipamiento / Hardware', 'Servicios TI'],
                        datasets: [{
                            data: hasData ? [countProd, countServ] : [70, 30],
                            backgroundColor: ['#10B981', '#F43F5E'],
                            borderWidth: 2,
                            borderColor: isDark ? '#111C3A' : '#FFFFFF'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: textColor, font: { size: 10, weight: '600' }, boxWidth: 12 }
                            }
                        }
                    }
                });
            }

            // 5. VENTAS DIARIAS (AREA CHART)
            const ctxDiarias = document.getElementById('chartVentasDiarias')?.getContext('2d');
            if (ctxDiarias) {
                const diasArray = Array.from({ length: {{ $diasEnMes }} }, (_, i) => 'Día ' + (i + 1));
                const dataDiaria = {!! json_encode(array_values($ventasDiariasData)) !!};

                new Chart(ctxDiarias, {
                    type: 'line',
                    data: {
                        labels: diasArray,
                        datasets: [{
                            label: 'Venta Diaria (S/)',
                            data: dataDiaria,
                            fill: true,
                            backgroundColor: 'rgba(6, 182, 212, 0.15)', // Cyan soft fill
                            borderColor: '#06B6D4', // Cyan Neón
                            borderWidth: 2.5,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#06B6D4',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        return ' Venta: S/ ' + Number(ctx.raw || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: textColor, font: { size: 10 } }
                            },
                            y: {
                                grid: { color: gridColor },
                                ticks: {
                                    color: textColor,
                                    font: { size: 10 },
                                    callback: function(v) { return 'S/ ' + (v >= 1000 ? (v/1000) + 'k' : v); }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
