<x-app-layout>
    <div class="p-4 sm:p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <span>Ventas</span><span>/</span><span class="text-gray-900 dark:text-white">Guías de Remisión</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                    Guías de Remisión (T001)
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Documentos de traslado de mercadería. No mueven stock, solo sustentan el traslado.</p>
            </div>
            <a href="{{ route('guias-remision.create') }}" class="px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl font-semibold text-xs sm:text-sm flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva Guía
            </a>
        </div>

        @if(session('success'))<div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-2xl text-xs sm:text-sm">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 rounded-2xl text-xs sm:text-sm">{{ session('error') }}</div>@endif

        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-4">
            <form method="GET" action="{{ route('guias-remision.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-9">
                    <select name="estado" class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white">
                        <option value="TODOS">Todos los estados...</option>
                        <option value="emitida" {{ request('estado') === 'emitida' ? 'selected' : '' }}>Emitida</option>
                        <option value="anulada" {{ request('estado') === 'anulada' ? 'selected' : '' }}>Anulada</option>
                    </select>
                </div>
                <div class="sm:col-span-3"><button type="submit" class="w-full px-3 py-2.5 bg-slate-900 dark:bg-cyan-600 text-white rounded-xl text-xs font-bold">Filtrar</button></div>
            </form>
        </div>

        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-900 dark:bg-slate-950 text-white text-xs font-semibold">
                        <tr><th class="px-4 py-3.5">Guía</th><th class="px-4 py-3.5">Venta / Destino</th><th class="px-4 py-3.5">Traslado</th><th class="px-4 py-3.5">Transportista</th><th class="px-4 py-3.5 text-right">Estado</th><th class="px-4 py-3.5 text-right">Acciones</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse($guias as $g)
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3.5 font-mono font-bold text-cyan-600 dark:text-cyan-400"><a href="{{ route('guias-remision.show', $g) }}" class="hover:underline">{{ $g->codigo_formateado }}</a></td>
                                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300">{{ $g->venta->codigo_formateado ?? 'Traslado interno' }}<div class="text-[10px] text-gray-400">{{ $g->punto_llegada ?? $g->venta->cliente->nombre ?? '' }}</div></td>
                                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300">{{ $g->fecha_traslado?->format('d/m/Y') }}<div class="text-[10px] text-gray-400">{{ $g->motivo_traslado }}</div></td>
                                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300">{{ $g->transportista ?? '—' }}<div class="text-[10px] font-mono text-gray-400">{{ $g->placa_vehiculo ?? '' }}</div></td>
                                <td class="px-4 py-3.5 text-right"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border {{ $g->estado === 'emitida' ? 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800' }}">{{ $g->estado }}</span></td>
                                <td class="px-4 py-3.5 text-right"><div class="flex items-center justify-end gap-2"><x-action-button icon="view" color="cyan" label="Ver detalle de guía" :href="route('guias-remision.show', $g)" /></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-10 text-gray-400 italic">No hay guías emitidas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($guias->hasPages())<div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/60">{{ $guias->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
