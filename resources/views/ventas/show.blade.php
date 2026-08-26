<x-app-layout>
    <div class="p-4 sm:p-6 space-y-6 max-w-6xl mx-auto">

        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('ventas.index') }}" class="hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">Ventas</a>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">{{ $venta->codigo_formateado }}</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="font-mono text-cyan-600 dark:text-cyan-400">{{ $venta->codigo_formateado }}</span>
                    <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                        REGISTRADA
                    </span>
                </h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('ventas.index') }}"
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

        <!-- Card principal con pestañas -->
        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 overflow-hidden">

            <!-- Header Banner -->
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-5 text-white">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                    <div class="border-r border-slate-700">
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">Documento</div>
                        <div class="text-sm font-bold mt-0.5">NOTA DE VENTA</div>
                    </div>
                    <div class="border-r border-slate-700">
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">Código</div>
                        <div class="text-sm font-bold text-cyan-400 mt-0.5 font-mono">{{ $venta->codigo_formateado }}</div>
                    </div>
                    <div class="border-r border-slate-700">
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">Cliente</div>
                        <div class="text-xs font-bold mt-0.5 truncate">{{ $venta->cliente->nombre ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">Total</div>
                        <div class="text-sm font-bold text-emerald-400 mt-0.5 font-mono">S/ {{ number_format($venta->total, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                <div class="px-6 flex space-x-1 whitespace-nowrap">
                    <button class="tab-btn py-3.5 px-4 border-b-2 border-cyan-500 text-cyan-600 dark:text-cyan-400 font-bold text-xs uppercase tracking-wider" onclick="showTab('datos', this)">
                        Datos de la Venta
                    </button>
                    <button class="tab-btn py-3.5 px-4 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-semibold text-xs uppercase tracking-wider" onclick="showTab('items', this)">
                        Productos ({{ count($detalle) }})
                    </button>
                    <button class="tab-btn py-3.5 px-4 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-semibold text-xs uppercase tracking-wider" onclick="showTab('pdf', this)">
                        Visor PDF
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-6">

                <!-- Tab: Datos de la Venta -->
                <div id="datos" class="tab-content">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block mb-1">Cliente / Destinatario</span>
                            <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $venta->cliente->razon_social ?: $venta->cliente->nombre }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">RUC/DNI: {{ $venta->cliente->ruc ?: '—' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block mb-1">Fecha de Emisión</span>
                            <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm">{{ $venta->fecha?->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $venta->fecha?->diffForHumans() }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block mb-1">Vendedor / Responsable</span>
                            <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm">{{ $venta->usuario->name ?? 'Área de Ventas' }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $venta->usuario->email ?? '' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold block mb-1">Total Facturado</span>
                            <p class="text-2xl font-bold font-mono text-cyan-600 dark:text-cyan-400">S/ {{ number_format($venta->total, 2) }}</p>
                            <p class="text-xs text-gray-400 font-mono">Subtotal: S/ {{ number_format($venta->subtotal, 2) }} | IGV: S/ {{ number_format($venta->igv, 2) }}</p>
                        </div>
                    </div>

                    @if($venta->cotizacion || $venta->ordenCompraCliente)
                        <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700/60 flex flex-wrap gap-6 text-xs">
                            @if($venta->cotizacion)
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-400 font-bold uppercase text-[10px]">Cotización Origen:</span>
                                    <a href="{{ route('cotizaciones.show', $venta->cotizacion) }}" class="text-cyan-600 dark:text-cyan-400 hover:underline font-bold font-mono">
                                        {{ $venta->cotizacion->codigo_formateado }}
                                    </a>
                                </div>
                            @endif
                            @if($venta->ordenCompraCliente)
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-400 font-bold uppercase text-[10px]">O/C Cliente:</span>
                                    <a href="{{ route('ordenes-compra-cliente.show', $venta->ordenCompraCliente) }}" class="text-cyan-600 dark:text-cyan-400 hover:underline font-semibold font-mono">
                                        {{ $venta->ordenCompraCliente->numero_oc }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Tab: Productos -->
                <div id="items" class="tab-content hidden">
                    <div class="overflow-x-auto -mx-6 -mb-6">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-900 dark:bg-slate-950 text-white uppercase text-[10px] tracking-wider font-semibold">
                                <tr>
                                    <th class="px-4 py-3 text-center w-[5%]">#</th>
                                    <th class="px-4 py-3 w-[45%]">Producto / SKU</th>
                                    <th class="px-4 py-3 text-center w-[12%]">Cantidad</th>
                                    <th class="px-4 py-3 text-right w-[15%]">P. Venta Unit</th>
                                    <th class="px-4 py-3 text-right w-[18%]">Importe Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                @foreach($detalle as $idx => $linea)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-darker-bg/50 transition-colors">
                                        <td class="px-4 py-3 text-center font-mono font-bold text-gray-400">{{ $idx + 1 }}</td>
                                        <td class="px-4 py-3">
                                            <p class="font-bold text-gray-900 dark:text-white text-xs">{{ $linea->producto_nombre }}</p>
                                            <p class="text-[11px] font-mono text-gray-400">{{ $linea->producto_sku ?? '—' }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-center font-mono font-semibold">
                                            {{ number_format($linea->cantidad, 2) }} {{ $linea->unidad_medida ?: 'UND' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono font-semibold text-gray-700 dark:text-gray-300">
                                            S/ {{ number_format($linea->precio_venta_unit, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono font-bold text-cyan-700 dark:text-cyan-300">
                                            S/ {{ number_format($linea->cantidad * $linea->precio_venta_unit, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/60 flex justify-end bg-gray-50/50 dark:bg-darker-bg/30">
                            <div class="w-72 space-y-1.5 font-mono text-xs text-right">
                                <div class="flex justify-between text-gray-500 dark:text-gray-400">
                                    <span class="font-sans">Subtotal (Base Imponible):</span>
                                    <span class="font-bold text-gray-900 dark:text-white">S/ {{ number_format($venta->subtotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-500 dark:text-gray-400">
                                    <span class="font-sans">I.G.V. (18%):</span>
                                    <span class="font-bold text-gray-900 dark:text-white">S/ {{ number_format($venta->igv, 2) }}</span>
                                </div>
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-2 flex justify-between items-center text-sm">
                                    <span class="font-sans font-bold text-cyan-600 dark:text-cyan-400">Total Venta:</span>
                                    <span class="font-bold text-cyan-600 dark:text-cyan-400 text-lg">S/ {{ number_format($venta->total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Visor PDF -->
                <div id="pdf" class="tab-content hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Documento PDF — Nota de Venta {{ $venta->codigo_formateado }}</h3>
                            <p class="text-xs text-gray-500">Visualización interactiva con zoom, impresión y descarga.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('ventas.pdf', $venta) }}" target="_blank"
                               class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Abrir en Pestaña
                            </a>
                            <a href="{{ route('ventas.pdf.descargar', $venta) }}"
                               class="px-3 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Descargar PDF
                            </a>
                        </div>
                    </div>
                    <div class="w-full bg-slate-900 rounded-xl overflow-hidden p-1 shadow-inner">
                        <object data="{{ route('ventas.pdf', $venta) }}#pagemode=none&view=FitH"
                                type="application/pdf" width="100%" height="700px" class="w-full h-[700px] rounded-lg">
                            <iframe src="{{ route('ventas.pdf', $venta) }}#pagemode=none&view=FitH"
                                    width="100%" height="700px" class="w-full h-[700px] rounded-lg border-none">
                                <p class="text-sm text-white p-4">
                                    Tu navegador no soporta la visualización directa de PDF.
                                    <a href="{{ route('ventas.pdf', $venta) }}" class="text-cyan-400 underline font-bold" target="_blank">Descárgalo aquí</a>.
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