<x-app-layout>
    <div class="p-4 sm:p-6 space-y-6">

        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <span>Ventas</span>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Órdenes de Compra de Clientes</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                    Órdenes de Compra de Clientes (O/C Entrantes)
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Recepción y registro de pedidos formales enviados por los clientes para cotizar o facturar.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('ordenes-compra-cliente.create') }}" class="px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl font-semibold text-xs sm:text-sm flex items-center gap-2 shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Registrar O/C Cliente
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
            <form method="GET" action="{{ route('ordenes-compra-cliente.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-5">
                    <select name="cliente_id" class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                        <option value="">Todos los clientes...</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->razon_social ?: $cliente->nombre }} (RUC: {{ $cliente->ruc ?: '—' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-4">
                    <select name="estado" class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                        <option value="">Todos los estados</option>
                        <option value="pendiente"  {{ request('estado') === 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                        <option value="procesada"  {{ request('estado') === 'procesada'  ? 'selected' : '' }}>Procesada</option>
                        <option value="anulada"    {{ request('estado') === 'anulada'    ? 'selected' : '' }}>Anulada</option>
                    </select>
                </div>

                <div class="sm:col-span-3 flex items-center gap-2">
                    <button type="submit" class="w-full px-3 py-2.5 bg-slate-900 hover:bg-slate-800 text-white dark:bg-cyan-600 dark:hover:bg-cyan-500 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Filtrar
                    </button>
                    @if(request()->hasAny(['cliente_id', 'estado']))
                        <a href="{{ route('ordenes-compra-cliente.index') }}" class="p-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs transition-colors" title="Limpiar Filtros">
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
                            <th class="px-4 py-3.5">N° O/C Cliente</th>
                            <th class="px-4 py-3.5">Cliente Emisor</th>
                            <th class="px-4 py-3.5">Fecha Recepción</th>
                            <th class="px-4 py-3.5 text-center">Estado</th>
                            <th class="px-4 py-3.5 text-center">Adjunto</th>
                            <th class="px-4 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse($ordenes as $orden)
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3.5 font-mono font-bold text-cyan-600 dark:text-cyan-400">
                                    <a href="{{ route('ordenes-compra-cliente.show', $orden) }}" class="hover:underline">
                                        {{ $orden->numero_oc }}
                                    </a>
                                </td>
                                <td class="px-4 py-3.5 font-semibold text-gray-900 dark:text-white">
                                    {{ $orden->cliente->razon_social ?: $orden->cliente->nombre }}
                                    <div class="text-[10px] font-mono text-gray-400">RUC: {{ $orden->cliente->ruc ?: '—' }}</div>
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300">
                                    {{ $orden->fecha_recepcion?->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @php
                                        $badgeClass = match($orden->estado) {
                                            'procesada' => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800',
                                            'anulada'   => 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border-rose-300 dark:border-rose-800',
                                            default     => 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-300 dark:border-amber-800',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase border {{ $badgeClass }}">
                                        {{ $orden->estado }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($orden->archivo)
                                        <a href="{{ route('ordenes-compra-cliente.archivo', $orden) }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-cyan-50 dark:bg-cyan-950/40 text-cyan-700 dark:text-cyan-300 hover:bg-cyan-100 font-semibold text-[11px] transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                            Ver Archivo
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-[11px]">Sin archivo</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-action-button icon="view" color="cyan" label="Ver detalle de orden de cliente" :href="route('ordenes-compra-cliente.show', $orden)" />
                                        <x-action-button icon="edit" color="amber" label="Editar orden de cliente" :href="route('ordenes-compra-cliente.edit', $orden)" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-gray-400 italic">
                                    No se encontraron órdenes de compra de clientes registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($ordenes->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/60">
                    {{ $ordenes->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>