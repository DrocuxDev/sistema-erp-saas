<x-app-layout>
    <div class="p-4 sm:p-6 space-y-6 max-w-6xl mx-auto">

        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('cotizaciones.index') }}" class="hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">Cotizaciones</a>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">{{ $cotizacion->codigo_formateado }}</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex flex-wrap items-center gap-2.5">
                    <span class="font-mono text-cyan-600 dark:text-cyan-400">{{ $cotizacion->codigo_formateado }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 border border-slate-300 dark:border-slate-700">
                        v{{ $cotizacion->version_formateada }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $cotizacion->tipo_cotizacion === 'servicio' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300' }}">
                        {{ $cotizacion->tipo_cotizacion === 'servicio' ? '🛠️ Servicio TI' : '📦 Producto TI' }}
                    </span>
                    @php
                        $estadoClass = match($cotizacion->estado) {
                            'aprobada'  => 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-800',
                            'rechazada' => 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-900/40 dark:text-rose-300 dark:border-rose-800',
                            'facturada' => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-800',
                            default     => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-800',
                        };
                    @endphp
                    <span class="px-3 py-0.5 rounded-full text-xs font-bold uppercase border {{ $estadoClass }}">
                        {{ $cotizacion->estado }}
                    </span>
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $cotizacion->asunto ?: 'Cotización comercial' }} — <strong class="text-gray-700 dark:text-gray-300">{{ $cotizacion->cliente->nombre ?? '—' }}</strong>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($cotizacion->estado === 'pendiente')
                    <a href="{{ route('cotizaciones.edit', $cotizacion) }}"
                       class="px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Editar (v{{ sprintf('%03d', ($cotizacion->version ?: 1) + 1) }})
                    </a>
                    <form action="{{ route('cotizaciones.aprobar', $cotizacion) }}" method="POST" onsubmit="return confirm('¿Aprobar esta cotización?')">
                        @csrf @method('PATCH')
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Aprobar
                        </button>
                    </form>
                @endif
                @if($cotizacion->estado === 'aprobada' && \Illuminate\Support\Facades\Route::has('ventas.create'))
                    <a href="{{ route('ventas.create', ['cotizacion_id' => $cotizacion->id]) }}"
                       class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl text-xs font-bold shadow-md transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        Generar Venta
                    </a>
                @endif
                <a href="{{ route('cotizaciones.index') }}"
                   class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm transition-all">
                    ← Volver
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-2xl text-xs flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 rounded-2xl text-xs flex items-center gap-2.5">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Card principal con pestañas -->
        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 overflow-hidden">

            <!-- Header Banner -->
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-5 text-white">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                    <div class="border-r border-slate-700">
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">Documento</div>
                        <div class="text-sm font-bold mt-0.5">COTIZACIÓN</div>
                    </div>
                    <div class="border-r border-slate-700">
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">Código</div>
                        <div class="text-sm font-bold text-cyan-400 mt-0.5 font-mono">{{ $cotizacion->codigo_formateado }}</div>
                    </div>
                    <div class="border-r border-slate-700">
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">Versión</div>
                        <div class="text-sm font-bold text-emerald-400 mt-0.5 font-mono">v{{ $cotizacion->version_formateada }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">Total</div>
                        <div class="text-sm font-bold text-cyan-300 mt-0.5 font-mono">
                            S/ {{ number_format($cotizacion->resumen->total_facturar ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                <div class="px-6 flex space-x-1 whitespace-nowrap">
                    <button class="tab-btn py-3.5 px-4 border-b-2 border-cyan-500 text-cyan-600 dark:text-cyan-400 font-bold text-xs uppercase tracking-wider" onclick="showTab('datos', this)">
                        Datos del Proyecto
                    </button>
                    <button class="tab-btn py-3.5 px-4 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-semibold text-xs uppercase tracking-wider" onclick="showTab('items', this)">
                        Ítems ({{ $cotizacion->detalles->count() }})
                    </button>
                    <button class="tab-btn py-3.5 px-4 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-semibold text-xs uppercase tracking-wider" onclick="showTab('financiero', this)">
                        Resumen Financiero
                    </button>
                    <button class="tab-btn py-3.5 px-4 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-semibold text-xs uppercase tracking-wider" onclick="showTab('pdf', this)">
                        Visor PDF
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-6">

                <!-- Tab: Datos del Proyecto -->
                <div id="datos" class="tab-content">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
                        <div class="space-y-2">
                            <p class="font-bold text-gray-400 uppercase tracking-wider text-[10px]">Cliente / Razón Social</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $cotizacion->cliente->nombre ?? '—' }}</p>
                            @if($cotizacion->cliente->razon_social && $cotizacion->cliente->razon_social !== $cotizacion->cliente->nombre)
                                <p class="text-gray-500">{{ $cotizacion->cliente->razon_social }}</p>
                            @endif
                            <p class="text-gray-600 dark:text-gray-400">RUC: <span class="font-mono font-bold text-gray-900 dark:text-white">{{ $cotizacion->cliente->ruc ?? '—' }}</span></p>
                            @if($cotizacion->cliente->direccion)
                                <p class="text-gray-500">{{ $cotizacion->cliente->direccion }}</p>
                            @endif
                        </div>
                        <div class="space-y-2">
                            <p class="font-bold text-gray-400 uppercase tracking-wider text-[10px]">Representante & Proyecto</p>
                            <p class="text-gray-700 dark:text-gray-300">Atención: <strong class="text-gray-900 dark:text-white">{{ $cotizacion->representante ?: ($cotizacion->cliente->contacto ?: '—') }}</strong></p>
                            <p class="text-gray-700 dark:text-gray-300">Asunto: <strong class="text-cyan-600 dark:text-cyan-400">{{ $cotizacion->asunto ?: 'Cotización TI' }}</strong></p>
                            <p class="text-gray-600 dark:text-gray-400">Fecha: <span class="font-semibold text-gray-900 dark:text-white">{{ $cotizacion->fecha?->format('d/m/Y') }}</span></p>
                            <p class="text-gray-600 dark:text-gray-400">Registrado por: <span class="font-semibold text-gray-900 dark:text-white">{{ $cotizacion->usuario->name ?? '—' }}</span></p>
                        </div>
                        <div class="space-y-3">
                            @if($cotizacion->ordenCompraCliente)
                                <div class="bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-200 dark:border-cyan-800 rounded-xl p-3">
                                    <span class="text-gray-500 text-[10px] uppercase font-bold">O/C Cliente Asociada:</span>
                                    <a href="{{ route('ordenes-compra-cliente.show', $cotizacion->ordenCompraCliente) }}" class="text-cyan-600 dark:text-cyan-400 hover:underline font-bold font-mono block mt-0.5">
                                        {{ $cotizacion->ordenCompraCliente->numero_oc }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Términos y Condiciones -->
                    <div class="border-t border-gray-100 dark:border-gray-700/60 pt-5 mt-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs bg-gray-50/70 dark:bg-darker-bg/50 p-4 rounded-xl">
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block">Plazo de Entrega</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $cotizacion->plazo_entrega ?: '2 a 5 días hábiles' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block">Forma de Pago</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $cotizacion->forma_pago ?: '30 días calendario' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block">Logística</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $cotizacion->logistica ?: 'Lima Metropolitana' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block">Validez Oferta</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $cotizacion->validez_oferta ?: '15 días' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tab: Ítems -->
                <div id="items" class="tab-content hidden">
                    <div class="overflow-x-auto -mx-6 -mb-6">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-900 text-white uppercase text-[10px] tracking-wider font-semibold">
                                <tr>
                                    <th class="px-3 py-3 w-[5%] text-center">#</th>
                                    <th class="px-4 py-3 w-[30%]">Producto / Descripción</th>
                                    <th class="px-3 py-3 w-[14%]">Proveedor</th>
                                    <th class="px-3 py-3 w-[8%] text-center">Cant.</th>
                                    <th class="px-3 py-3 w-[11%] text-right bg-blue-900/40">P. Venta Unit</th>
                                    <th class="px-3 py-3 w-[12%] text-right bg-blue-900/60">Total Venta</th>
                                    <th class="px-3 py-3 w-[10%] text-right bg-slate-800">P. Compra</th>
                                    <th class="px-3 py-3 w-[10%] text-right bg-emerald-950/60 text-emerald-300">Ganancia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700/60">
                                @foreach($cotizacion->detalles as $idx => $detalle)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-darker-bg/50 transition-colors">
                                        <td class="px-3 py-3 text-center font-mono font-bold text-gray-500">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                        <td class="px-4 py-3">
                                            <p class="font-bold text-gray-900 dark:text-white">{{ $detalle->producto->nombre ?? $detalle->descripcion }}</p>
                                            @if(!empty($detalle->descripcion) && $detalle->descripcion !== ($detalle->producto->nombre ?? ''))
                                                <p class="text-[11px] text-gray-500">{{ $detalle->descripcion }}</p>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-gray-700 dark:text-gray-300 font-medium">{{ $detalle->proveedor->nombre ?? '—' }}</td>
                                        <td class="px-3 py-3 text-center font-mono font-semibold">{{ number_format($detalle->cantidad, 2) }} {{ $detalle->unidad_medida ?: 'UND' }}</td>
                                        <td class="px-3 py-3 text-right font-mono font-semibold text-blue-700 dark:text-blue-300 bg-blue-50/30 dark:bg-blue-950/20">
                                            S/ {{ number_format($detalle->precio_venta_unit, 2) }}
                                        </td>
                                        <td class="px-3 py-3 text-right font-mono font-bold text-blue-900 dark:text-blue-200 bg-blue-50/60 dark:bg-blue-950/30">
                                            S/ {{ number_format($detalle->precio_venta_total, 2) }}
                                        </td>
                                        <td class="px-3 py-3 text-right font-mono text-gray-600 dark:text-gray-400 bg-slate-50/50 dark:bg-slate-900/30">
                                            S/ {{ number_format($detalle->precio_compra_unit, 2) }}
                                        </td>
                                        <td class="px-3 py-3 text-right font-mono font-bold bg-emerald-50/40 dark:bg-emerald-950/20 {{ $detalle->ganancia_linea >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600' }}">
                                            S/ {{ number_format($detalle->ganancia_linea, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Resumen Financiero -->
                <div id="financiero" class="tab-content hidden">
                    @if($cotizacion->resumen)
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Totales Comerciales -->
                        <div class="bg-gray-50 dark:bg-darker-bg rounded-xl p-5 border border-gray-200 dark:border-gray-700/60 space-y-3">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider border-b border-gray-200 dark:border-gray-700 pb-3 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                                Resumen de Venta / Facturación Comercial
                            </h4>
                            <div class="space-y-2.5 text-xs font-mono">
                                <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                                    <span class="font-sans">Subtotal (Base Imponible)</span>
                                    <span class="font-bold text-gray-900 dark:text-white text-sm">S/ {{ number_format($cotizacion->resumen->subtotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                                    <span class="font-sans">I.G.V. ({{ number_format($cotizacion->resumen->pct_igv ?? 18, 2) }}%)</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">S/ {{ number_format($cotizacion->resumen->igv, 2) }}</span>
                                </div>
                                @if($cotizacion->resumen->retencion > 0)
                                <div class="flex justify-between items-center text-rose-600">
                                    <span class="font-sans">Retención ({{ number_format($cotizacion->resumen->pct_retencion ?? 3, 2) }}%)</span>
                                    <span>- S/ {{ number_format($cotizacion->resumen->retencion, 2) }}</span>
                                </div>
                                @endif
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex justify-between items-center">
                                    <span class="font-sans font-bold text-cyan-600 dark:text-cyan-400 text-base">Total a Facturar</span>
                                    <span class="font-bold text-cyan-600 dark:text-cyan-400 text-xl">S/ {{ number_format($cotizacion->resumen->total_facturar, 2) }}</span>
                                </div>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-700/60 pt-3 mt-3">
                                <p class="text-[10px] text-gray-400 font-sans">Tasas aplicadas a este documento: IGV {{ number_format($cotizacion->resumen->pct_igv ?? 18, 2) }}% · Ret. {{ number_format($cotizacion->resumen->pct_retencion ?? 3, 2) }}% · Com. Venta {{ number_format($cotizacion->resumen->pct_comision_venta ?? 5, 2) }}% · Com. Ganancia {{ number_format($cotizacion->resumen->pct_comision_ganancia ?? 10, 2) }}%</p>
                            </div>
                        </div>

                        <!-- Análisis Interno (Admin Only) -->
                        @if(auth()->user()->rol === 'administrador')
                        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-xl p-5 shadow-lg space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-cyan-400 border-b border-slate-700 pb-3 flex items-center justify-between">
                                <span>Análisis Interno de Rentabilidad</span>
                                <span class="px-2 py-0.5 rounded bg-cyan-950 text-cyan-300 text-[10px]">ADMIN</span>
                            </h4>
                            <div class="grid grid-cols-2 gap-3 text-xs font-mono pt-1">
                                <div>
                                    <span class="text-slate-400 font-sans block text-[11px]">Compra Fija:</span>
                                    <span class="font-semibold text-slate-200">S/ {{ number_format($cotizacion->resumen->compra_fija, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-sans block text-[11px]">Ganancia Bruta:</span>
                                    <span class="font-bold text-emerald-400 text-sm">S/ {{ number_format($cotizacion->resumen->ganancia, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-sans block text-[11px]">Comisión Venta:</span>
                                    <span class="text-slate-200">S/ {{ number_format($cotizacion->resumen->comision_venta, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-sans block text-[11px]">Comisión Ganancia:</span>
                                    <span class="text-slate-200">S/ {{ number_format($cotizacion->resumen->comision_ganancia, 2) }}</span>
                                </div>
                            </div>
                            <div class="border-t border-slate-700 pt-3 flex justify-between items-center">
                                <span class="font-sans font-bold text-emerald-400 text-sm">Rentabilidad Neta:</span>
                                <span class="font-mono font-bold text-lg {{ $cotizacion->resumen->rentabilidad >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                    S/ {{ number_format($cotizacion->resumen->rentabilidad, 2) }}
                                </span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">No hay resumen financiero disponible para esta cotización.</p>
                    @endif
                </div>

                <!-- Tab: Visor PDF -->
                <div id="pdf" class="tab-content hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Documento PDF Oficial</h3>
                            <p class="text-xs text-gray-500">Visualización interactiva del documento con zoom, impresión y descarga.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('cotizaciones.pdf', $cotizacion) }}" target="_blank"
                               class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Abrir en Pestaña
                            </a>
                            <a href="{{ route('cotizaciones.pdf.descargar', $cotizacion) }}"
                               class="px-3 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Descargar PDF
                            </a>
                        </div>
                    </div>
                    <div class="w-full bg-slate-900 rounded-xl overflow-hidden p-1 shadow-inner">
                        <object data="{{ route('cotizaciones.pdf', $cotizacion) }}#pagemode=none&view=FitH"
                                type="application/pdf" width="100%" height="750px" class="w-full h-[750px] rounded-lg">
                            <iframe src="{{ route('cotizaciones.pdf', $cotizacion) }}#pagemode=none&view=FitH"
                                    width="100%" height="750px" class="w-full h-[750px] rounded-lg border-none">
                                <p class="text-sm text-white p-4">
                                    Tu navegador no soporta la visualización directa de PDF.
                                    <a href="{{ route('cotizaciones.pdf', $cotizacion) }}" class="text-cyan-400 underline font-bold" target="_blank">Descárgalo aquí</a>.
                                </p>
                            </iframe>
                        </object>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        function showTab(tabName, element) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            document.getElementById(tabName).classList.remove('hidden');
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-cyan-500', 'text-cyan-600', 'dark:text-cyan-400', 'font-bold');
                btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'font-semibold');
            });
            element.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'font-semibold');
            element.classList.add('border-cyan-500', 'text-cyan-600', 'dark:text-cyan-400', 'font-bold');
        }
    </script>
</x-app-layout>
