<x-app-layout>
    <div class="p-4 sm:p-6 space-y-6">

        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <span>Ventas</span>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Registro de Ventas</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                    Registro de Ventas Facturadas (Serie V001)
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Historial de ventas y pedidos confirmados con descuento automático de inventario.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('ventas.create') }}" class="px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl font-semibold text-xs sm:text-sm flex items-center gap-2 shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva Venta Directa
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-2xl text-xs sm:text-sm flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filtros -->
        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-4">
            <form method="GET" action="{{ route('ventas.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-8">
                    <select name="cliente_id" class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                        <option value="">Todos los clientes...</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->razon_social ?: $cliente->nombre }} (RUC: {{ $cliente->ruc ?: '—' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-4 flex items-center gap-2">
                    <button type="submit" class="w-full px-3 py-2.5 bg-slate-900 hover:bg-slate-800 text-white dark:bg-cyan-600 dark:hover:bg-cyan-500 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Filtrar
                    </button>
                    @if(request()->has('cliente_id'))
                        <a href="{{ route('ventas.index') }}" class="p-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs transition-colors" title="Limpiar Filtros">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabla -->
        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-900 dark:bg-slate-950 text-white text-xs font-semibold border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="px-4 py-3.5">Código Venta</th>
                            <th class="px-4 py-3.5">Cliente</th>
                            <th class="px-4 py-3.5">Fecha</th>
                            <th class="px-4 py-3.5">Vendedor</th>
                            <th class="px-4 py-3.5 text-right">Total Facturado</th>
                            <th class="px-4 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse($ventas as $v)
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3.5 font-mono font-bold text-cyan-600 dark:text-cyan-400">
                                    <a href="{{ route('ventas.show', $v) }}" class="hover:underline">
                                        {{ $v->codigo_formateado }}
                                    </a>
                                </td>
                                <td class="px-4 py-3.5 font-semibold text-gray-900 dark:text-white">
                                    {{ $v->cliente->razon_social ?: $v->cliente->nombre }}
                                    <div class="text-[10px] font-mono text-gray-400">RUC: {{ $v->cliente->ruc ?: '—' }}</div>
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300">
                                    {{ $v->fecha?->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300">
                                    {{ $v->usuario->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono font-bold text-gray-900 dark:text-white text-sm">
                                    S/ {{ number_format($v->total, 2) }}
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-action-button icon="view" color="cyan" label="Ver detalle de venta" :href="route('ventas.show', $v)" />
                                        <x-action-button icon="pdf" color="slate" label="Ver PDF" :href="route('ventas.pdf', $v)" target="_blank" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-gray-400 italic">
                                    No se encontraron ventas registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($ventas->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/60">
                    {{ $ventas->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>