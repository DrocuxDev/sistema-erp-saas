<x-app-layout>
    <div class="p-4 sm:p-6 space-y-6 max-w-5xl mx-auto">

        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('ordenes-compra-cliente.index') }}" class="hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">O/C de Clientes</a>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">{{ $orden->numero_oc }}</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="font-mono text-cyan-600 dark:text-cyan-400">{{ $orden->numero_oc }}</span>
                    @php
                        $badgeClass = match($orden->estado) {
                            'procesada' => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800',
                            'anulada'   => 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border-rose-300 dark:border-rose-800',
                            default     => 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-300 dark:border-amber-800',
                        };
                    @endphp
                    <span class="px-3 py-0.5 rounded-full text-xs font-bold uppercase border {{ $badgeClass }}">
                        {{ $orden->estado }}
                    </span>
                </h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('ordenes-compra-cliente.edit', $orden) }}"
                   class="px-3.5 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold transition-all">
                    Editar
                </a>
                <a href="{{ route('ordenes-compra-cliente.index') }}"
                   class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all">
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

        <!-- Card principal con pestañas -->
        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 overflow-hidden">

            <!-- Header Banner -->
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-5 text-white">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                    <div class="border-r border-slate-700">
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">Documento</div>
                        <div class="text-sm font-bold mt-0.5">O/C DE CLIENTE</div>
                    </div>
                    <div class="border-r border-slate-700">
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">N° O/C</div>
                        <div class="text-sm font-bold text-cyan-400 mt-0.5 font-mono">{{ $orden->numero_oc }}</div>
                    </div>
                    <div class="border-r border-slate-700">
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">Cliente</div>
                        <div class="text-xs font-bold mt-0.5 truncate">{{ $orden->cliente->razon_social ?: $orden->cliente->nombre }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">Fecha Recepción</div>
                        <div class="text-sm font-bold mt-0.5">{{ $orden->fecha_recepcion?->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                <div class="px-6 flex space-x-1 whitespace-nowrap">
                    <button class="tab-btn py-3.5 px-4 border-b-2 border-cyan-500 text-cyan-600 dark:text-cyan-400 font-bold text-xs uppercase tracking-wider" onclick="showTab('datos', this)">
                        Datos de la Orden
                    </button>
                    @if($orden->archivo)
                    <button class="tab-btn py-3.5 px-4 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-semibold text-xs uppercase tracking-wider" onclick="showTab('adjunto', this)">
                        Documento Adjunto
                    </button>
                    @endif
                    @if($orden->cotizacionGenerada || $orden->ventaGenerada)
                    <button class="tab-btn py-3.5 px-4 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-semibold text-xs uppercase tracking-wider" onclick="showTab('derivados', this)">
                        Docs. Derivados
                    </button>
                    @endif
                    @if($orden->estado === 'pendiente')
                    <button class="tab-btn py-3.5 px-4 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-semibold text-xs uppercase tracking-wider" onclick="showTab('acciones', this)">
                        Acciones
                    </button>
                    @endif
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-6">

                <!-- Tab: Datos de la Orden -->
                <div id="datos" class="tab-content">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block mb-1">Cliente Emisor</span>
                            <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $orden->cliente->razon_social ?: $orden->cliente->nombre }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">RUC: {{ $orden->cliente->ruc ?: '—' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block mb-1">N° O/C del Cliente</span>
                            <p class="font-bold font-mono text-cyan-600 dark:text-cyan-400 text-sm">{{ $orden->numero_oc }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block mb-1">Fecha de Recepción</span>
                            <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm">{{ $orden->fecha_recepcion?->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $orden->fecha_recepcion?->diffForHumans() }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block mb-1">Registrado por</span>
                            <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm">{{ $orden->usuario->name ?? '—' }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $orden->created_at?->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    @if($orden->observaciones)
                        <div class="pt-5 mt-5 border-t border-gray-100 dark:border-gray-700/60">
                            <span class="text-gray-400 text-[10px] uppercase font-bold block mb-1">Observaciones / Requerimientos</span>
                            <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed bg-gray-50 dark:bg-darker-bg p-3 rounded-xl">{{ $orden->observaciones }}</p>
                        </div>
                    @endif
                </div>

                <!-- Tab: Documento Adjunto -->
                @if($orden->archivo)
                <div id="adjunto" class="tab-content hidden">
                    <div class="text-center space-y-4">
                        <div class="bg-gray-50 dark:bg-darker-bg rounded-xl p-8 border border-gray-200 dark:border-gray-700/60">
                            <svg class="w-16 h-16 mx-auto text-cyan-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Documento adjunto por el cliente</p>
                            <p class="text-xs text-gray-500 mb-4">Archivo original de la orden de compra enviada por el cliente.</p>
                            <a href="{{ route('ordenes-compra-cliente.archivo', $orden) }}" target="_blank"
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold shadow-md shadow-cyan-600/20 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                Ver / Descargar Documento Adjunto
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Tab: Documentos Derivados -->
                @if($orden->cotizacionGenerada || $orden->ventaGenerada)
                <div id="derivados" class="tab-content hidden">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Documentos Comerciales Generados a partir de esta O/C
                    </h4>
                    <div class="flex flex-wrap gap-3">
                        @if($orden->cotizacionGenerada)
                            <a href="{{ route('cotizaciones.show', $orden->cotizacionGenerada) }}"
                               class="px-5 py-3 bg-cyan-50 dark:bg-cyan-950/50 border border-cyan-200 dark:border-cyan-800 text-cyan-800 dark:text-cyan-300 rounded-xl text-xs font-bold flex items-center gap-2 hover:bg-cyan-100 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Cotización: {{ $orden->cotizacionGenerada->codigo_formateado }}
                            </a>
                        @endif
                        @if($orden->ventaGenerada)
                            <a href="{{ route('ventas.show', $orden->ventaGenerada) }}"
                               class="px-5 py-3 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-xl text-xs font-bold flex items-center gap-2 hover:bg-emerald-100 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                Venta: {{ $orden->ventaGenerada->codigo_formateado }}
                            </a>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Tab: Acciones -->
                @if($orden->estado === 'pendiente')
                <div id="acciones" class="tab-content hidden">
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Procesar Orden de Compra</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Genera una cotización formal o procesa una venta directa asociada a esta O/C.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('cotizaciones.create', ['orden_compra_cliente_id' => $orden->id]) }}"
                               class="px-5 py-3 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Generar Cotización
                            </a>
                            <a href="{{ route('ventas.create', ['orden_compra_cliente_id' => $orden->id]) }}"
                               class="px-5 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                Generar Venta Directa
                            </a>
                        </div>
                    </div>
                </div>
                @endif

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