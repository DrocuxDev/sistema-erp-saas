<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-cyan-700 dark:text-cyan-400 mb-1">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Panel Ejecutivo de Operaciones y Ventas</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-3">
                    Panel comercial
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Monitoreo en tiempo real de facturación, márgenes, rentabilidad, cotizaciones y stock.
                </p>
            </div>

            <!-- Filtros de Fecha / Periodo -->
            <form method="GET" action="{{ route('dashboard') }}" id="filtro-form" class="flex flex-wrap items-center gap-2">
                <div class="flex items-center bg-white dark:bg-card-bg p-1 rounded-xl border border-gray-200 dark:border-gray-700/80 shadow-2xs">
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
                   class="px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $rangoRapido === 'este_mes' || ($mes == (int)date('m') && $anio == (int)date('Y')) ? 'bg-slate-900 dark:bg-cyan-600 text-white' : 'bg-white dark:bg-card-bg text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700/80 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                    Este Mes
                </a>
                <a href="{{ route('dashboard', ['rango' => 'este_anio']) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $rangoRapido === 'este_anio' || (is_null($mes) && $anio == (int)date('Y')) ? 'bg-slate-900 dark:bg-cyan-600 text-white' : 'bg-white dark:bg-card-bg text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700/80 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                    Año {{ date('Y') }}
                </a>
            </form>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="page-container space-y-6">

            <!-- 1. HEADER DE MÉTRICAS CLAVE (KPIs EJECUTIVOS CON JERARQUÍA REAL) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                
                <!-- KPI 1: VENTAS TOTALES (Métrica principal ancla) -->
                <div class="bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/90 dark:border-gray-700/70 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 block">
                            Ventas Totales Facturadas
                        </span>
                        <span class="text-[10px] font-mono font-medium px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                            Serie V001
                        </span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-white mt-2 font-mono">
                        S/ {{ number_format($totalVentas, 2) }}
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 pt-2.5">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $cantidadVentas }}</span>
                        <span>transacciones concluidas</span>
                    </div>
                </div>

                <!-- KPI 2: MARGEN BRUTO S/ -->
                <div class="bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/90 dark:border-gray-700/70 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 block">
                            Margen Bruto (Ganancia)
                        </span>
                        <span class="text-[10px] font-mono font-medium px-2 py-0.5 rounded bg-cyan-50 dark:bg-cyan-950/50 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800/60">
                            Utilidad
                        </span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold tracking-tight text-cyan-600 dark:text-cyan-400 mt-2 font-mono">
                        S/ {{ number_format($gananciaVentas, 2) }}
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 pt-2.5">
                        <span>Costo de Adquisición: <strong class="text-gray-800 dark:text-gray-200 font-mono">S/ {{ number_format(max(0, $subtotalVentas - $gananciaVentas), 2) }}</strong></span>
                    </div>
                </div>

                <!-- KPI 3: MARGEN % / RENTABILIDAD -->
                <div class="bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/90 dark:border-gray-700/70 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 block">
                            Rentabilidad Comercial
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $margenPct >= 15 ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60' }}">
                            {{ $margenPct >= 15 ? 'Óptima' : 'Estándar' }}
                        </span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400 mt-2 font-mono">
                        {{ number_format($margenPct, 1) }}%
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 pt-2.5">
                        <span>Margen promedio ponderado</span>
                    </div>
                </div>

                <!-- KPI 4: CONVERSIÓN & COTIZACIONES (Sin púrpura de IA) -->
                <div class="bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/90 dark:border-gray-700/70 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 block">
                            Conversión de Cotizaciones
                        </span>
                        <span class="text-[10px] font-mono font-medium px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            C001 V001
                        </span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-white mt-2 font-mono">
                        {{ number_format($tasaConversion, 1) }}%
                    </div>
                    <div class="mt-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 pt-2.5">
                        <span>{{ $cotizacionesPendientes }} en curso</span>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $totalCotizaciones }} registradas</span>
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
                        <a href="{{ route('productos.index') }}" class="text-xs text-cyan-700 dark:text-cyan-400 font-medium hover:underline">
                            Ver catálogo
                        </a>
                    </div>
                    <div class="h-64 sm:h-72 w-full relative">
                        <canvas id="chartTopProductos"></canvas>
                    </div>
                </div>

                <!-- GRÁFICO 4: DISTRIBUCIÓN POR TIPO DE NEGOCIO (PIE) -->
                <div class="bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/90 dark:border-gray-700/70 shadow-2xs space-y-4">
                    <div class="border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-500"></span>
                            Líneas de Negocio
                        </h3>
                        <p class="text-xs text-gray-500">Equipamiento y servicios TI</p>
                    </div>
                    <div class="h-64 sm:h-72 w-full relative flex items-center justify-center">
                        <canvas id="chartLineasNegocio"></canvas>
                    </div>
                </div>

                <!-- GRÁFICO 5: EVOLUCIÓN DIARIA DE VENTAS (ÁREA CHART) -->
                <div class="lg:col-span-3 bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/90 dark:border-gray-700/70 shadow-2xs space-y-4">
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
                <div class="bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/90 dark:border-gray-700/70 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                            Top Clientes del Año
                        </h3>
                        <a href="{{ route('clientes.index') }}" class="text-xs text-cyan-700 dark:text-cyan-400 font-medium hover:underline">Ver directorio</a>
                    </div>
                    <div class="space-y-3">
                        @php $maxComprado = $topClientes->max('total_comprado') ?: 1; @endphp
                        @forelse($topClientes as $idx => $tc)
                            @php 
                                $porcentaje = round(((float)$tc->total_comprado / (float)$maxComprado) * 100, 0); 
                            @endphp
                            <div class="space-y-1 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-gray-800 dark:text-gray-200 truncate max-w-[200px]">
                                        {{ $idx + 1 }}. {{ $tc->cliente->nombre ?? 'Cliente #' . $tc->cliente_id }}
                                    </span>
                                    <span class="font-mono font-bold text-gray-900 dark:text-white">
                                        S/ {{ number_format($tc->total_comprado, 2) }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-700/60 h-2 rounded-full overflow-hidden">
                                    <div class="bg-cyan-600 dark:bg-cyan-500 h-full rounded-full" style="width: {{ $porcentaje }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 py-6 text-center">No hay registros de clientes en este periodo.</p>
                        @endforelse
                    </div>
                </div>

                <!-- ALERTAS DE STOCK CRÍTICO (Tratamiento diferenciado de alerta operativa) -->
                <div class="bg-white dark:bg-card-bg rounded-2xl p-5 border border-rose-200 dark:border-rose-900/40 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-rose-100 dark:border-rose-900/30 pb-3">
                        <h3 class="text-sm font-bold text-rose-950 dark:text-rose-200 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            Alertas de existencias bajas
                        </h3>
                        <a href="{{ route('inventario.kardex') }}" class="text-xs text-rose-700 dark:text-rose-300 font-medium hover:underline">Ver Kardex</a>
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
                <div class="bg-white dark:bg-card-bg rounded-2xl p-5 border border-gray-200/90 dark:border-gray-700/70 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Últimas Ventas Emitidas
                        </h3>
                        <a href="{{ route('ventas.index') }}" class="text-xs text-cyan-700 dark:text-cyan-400 font-medium hover:underline">Ver todas</a>
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
                                '#3B82F6', // Blue
                                '#64748B', // Slate
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
                        labels: ['Equipamiento', 'Servicios TI'],
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
