<x-app-layout>
    <div class="p-4 sm:p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1"><span>Compras</span><span>/</span><span class="text-gray-900 dark:text-white">Recepción de Mercadería</span></div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                    Recepción de Compras
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cada recepción incrementa el stock vía kardex (entradas).</p>
            </div>
            <a href="{{ route('compras.create') }}" class="px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl font-semibold text-xs sm:text-sm flex items-center gap-2 shadow-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Nueva Recepción</a>
        </div>

        @if(session('success'))<div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-2xl text-xs sm:text-sm">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 rounded-2xl text-xs sm:text-sm">{{ session('error') }}</div>@endif

        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-4">
            <form method="GET" action="{{ route('compras.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-9">
                    <select name="proveedor_id" class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white"><option value="">Todos los proveedores...</option>@foreach($proveedores as $p)<option value="{{ $p->id }}" {{ request('proveedor_id') == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>@endforeach</select>
                </div>
                <div class="sm:col-span-3"><button type="submit" class="w-full px-3 py-2.5 bg-slate-900 dark:bg-cyan-600 text-white rounded-xl text-xs font-bold">Filtrar</button></div>
            </form>
        </div>

        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 overflow-hidden">
            <div class="overflow-x-auto"><table class="w-full text-xs text-left">
                <thead class="bg-slate-900 dark:bg-slate-950 text-white text-xs font-semibold"><tr><th class="px-4 py-3.5">ID</th><th class="px-4 py-3.5">Proveedor</th><th class="px-4 py-3.5">O/C Origen</th><th class="px-4 py-3.5">Fecha</th><th class="px-4 py-3.5 text-right">Total</th><th class="px-4 py-3.5 text-right">Acciones</th></tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @forelse($compras as $c)
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3.5 font-mono font-bold text-cyan-600"><a href="{{ route('compras.show', $c) }}" class="hover:underline">#{{ $c->id }}</a></td>
                            <td class="px-4 py-3.5 font-semibold">{{ $c->proveedor->nombre ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-gray-500">{{ $c->ordenCompra->codigo_formateado ?? 'Compra directa' }}</td>
                            <td class="px-4 py-3.5 text-gray-500">{{ $c->fecha?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold">S/ {{ number_format($c->total, 2) }}</td>
                            <td class="px-4 py-3.5 text-right"><div class="flex items-center justify-end gap-2"><x-action-button icon="view" color="cyan" label="Ver detalle de compra" :href="route('compras.show', $c)" /></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-10 text-gray-400 italic">No hay recepciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
            @if($compras->hasPages())<div class="px-6 py-4 border-t">{{ $compras->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
