<x-app-layout>
    <div class="p-4 sm:p-6 space-y-6">

        <!-- Encabezado de Navegación y Título -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('configuracion.edit') }}" class="hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">Configuración</a>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Series & Correlativos</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800/60">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                    </span>
                    Control de Series y Correlativos
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Administre la numeración correlativa y prefijos para cotizaciones, ventas, compras y comprobantes.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('series-documentos.create') }}" class="px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl font-semibold text-xs sm:text-sm flex items-center gap-2 shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva Serie
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-800 dark:text-rose-300 text-xs sm:text-sm flex items-center gap-2.5">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Barra de Filtros y Búsqueda -->
        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-4">
            <form method="GET" action="{{ route('series-documentos.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-4">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por serie o descripción..."
                           class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                </div>

                <div class="sm:col-span-4">
                    <select name="tipo_documento" class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                        <option value="TODOS">Todos los tipos de documento</option>
                        @foreach($tipos as $key => $label)
                            <option value="{{ $key }}" {{ request('tipo_documento') === $key ? 'selected' : '' }}>
                                {{ $label }} ({{ $totalesPorTipo[$key] ?? 0 }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2 flex items-center">
                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-gray-700 dark:text-gray-300 font-medium">
                        <input type="checkbox" name="solo_activos" value="1" {{ request('solo_activos') == '1' ? 'checked' : '' }}
                               class="rounded border-gray-300 text-cyan-600 focus:ring-cyan-500 w-4 h-4">
                        Solo Activas
                    </label>
                </div>

                <div class="sm:col-span-2 flex items-center gap-2">
                    <button type="submit" class="w-full px-3 py-2.5 bg-slate-900 hover:bg-slate-800 text-white dark:bg-cyan-600 dark:hover:bg-cyan-500 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Filtrar
                    </button>
                    @if(request()->hasAny(['q', 'tipo_documento', 'solo_activos']))
                        <a href="{{ route('series-documentos.index') }}" class="p-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs transition-colors" title="Limpiar Filtros">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabla de Series -->
        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-900 dark:bg-slate-950 text-white uppercase text-[10px] tracking-wider font-semibold border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="px-4 py-3.5">Tipo de Documento</th>
                            <th class="px-4 py-3.5">Serie / Prefijo</th>
                            <th class="px-4 py-3.5">Descripción</th>
                            <th class="px-4 py-3.5 text-center">Correlativo Actual</th>
                            <th class="px-4 py-3.5 text-center">Próximo a Emitir</th>
                            <th class="px-4 py-3.5 text-center">Longitud</th>
                            <th class="px-4 py-3.5 text-center">Estado</th>
                            <th class="px-4 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse($series as $s)
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-gray-900 dark:text-white">
                                        {{ $s->tipo_nombre }}
                                    </div>
                                    <div class="text-[10px] font-mono text-gray-400">
                                        {{ $s->tipo_documento }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-sm px-2.5 py-1 rounded-lg bg-cyan-50 dark:bg-cyan-950/80 text-cyan-800 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800/60">
                                            {{ $s->serie }}
                                        </span>
                                        @if($s->es_predeterminado)
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                Predeterminada
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300">
                                    {{ $s->descripcion ?: '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-center font-mono font-bold text-gray-700 dark:text-gray-300 text-sm">
                                    {{ $s->correlativo_actual }}
                                </td>
                                <td class="px-4 py-3.5 text-center font-mono font-bold text-emerald-600 dark:text-emerald-400 text-sm">
                                    {{ $s->proximo_codigo }}
                                </td>
                                <td class="px-4 py-3.5 text-center font-mono text-gray-500 dark:text-gray-400">
                                    {{ $s->longitud_correlativo }} dígitos
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <form action="{{ route('series-documentos.toggle-activo', $s) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold transition-all cursor-pointer {{ $s->activo ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 hover:bg-emerald-200' : 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 hover:bg-rose-200' }}">
                                            {{ $s->activo ? 'ACTIVO' : 'INACTIVO' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('series-documentos.edit', $s) }}"
                                           class="px-2.5 py-1 rounded-lg bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 hover:bg-sky-100 font-semibold transition-colors flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Editar
                                        </a>

                                        @if(!$s->es_predeterminado)
                                            <form action="{{ route('series-documentos.destroy', $s) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar la serie \'{{ $s->serie }}\'?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 transition-colors" title="Eliminar Serie">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10 text-gray-400 italic">
                                    No se encontraron series registradas con los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($series->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/60">
                    {{ $series->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
