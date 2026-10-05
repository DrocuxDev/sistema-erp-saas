<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    Cotizaciones Comerciales
                </h2>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">Control de propuestas técnicas, códigos de cotización (Serie C001) y versiones</p>
            </div>
            <a href="{{ route('cotizaciones.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl shadow-2xs transition-all font-semibold text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva Cotización
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="page-container space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 rounded-2xl text-sm font-semibold">
                    ✓ {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-2xl text-sm font-semibold">
                    ✕ {{ session('error') }}
                </div>
            @endif

            {{-- Filtros y Búsqueda --}}
            <form method="GET" class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-4 sm:p-5 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Buscar (Código, Asunto, Cliente, RUC)</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Ej: C001-0, Proyecto TI, Surperu..."
                           class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white text-xs px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                    <select name="estado" class="rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white text-xs px-3 py-2">
                        <option value="">Todos los estados</option>
                        @foreach(['pendiente', 'aprobada', 'rechazada', 'facturada'] as $estado)
                            <option value="{{ $estado }}" {{ request('estado') === $estado ? 'selected' : '' }}>
                                {{ ucfirst($estado) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                    <select name="tipo_cotizacion" class="rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white text-xs px-3 py-2">
                        <option value="">Todos</option>
                        <option value="producto" {{ request('tipo_cotizacion') === 'producto' ? 'selected' : '' }}>📦 Producto</option>
                        <option value="servicio" {{ request('tipo_cotizacion') === 'servicio' ? 'selected' : '' }}>🛠️ Servicio</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Cliente</label>
                    <select name="cliente_id" class="rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white text-xs px-3 py-2 max-w-[200px]">
                        <option value="">Todos</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-cyan-600 dark:bg-cyan-500 hover:bg-cyan-700 dark:hover:bg-cyan-600 text-white rounded-xl text-xs font-semibold transition-colors shadow-2xs">
                        Filtrar
                    </button>
                    <a href="{{ route('cotizaciones.index') }}" class="px-3 py-2 bg-gray-200 dark:bg-darker-bg text-gray-700 dark:text-gray-300 rounded-xl text-xs hover:bg-gray-300 transition-colors">
                        Limpiar
                    </a>
                </div>
            </form>

            {{-- Tabla de Cotizaciones --}}
            <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-900 text-slate-200 text-xs font-medium border-b border-slate-800">
                            <tr>
                                <th class="px-4 py-3.5 w-[14%]">Código / Versión</th>
                                <th class="px-3 py-3.5 w-[10%]">Tipo</th>
                                <th class="px-4 py-3.5 w-[28%]">Cliente & Asunto</th>
                                <th class="px-3 py-3.5 w-[10%] text-center">Fecha</th>
                                <th class="px-3 py-3.5 w-[11%] text-center">Estado</th>
                                <th class="px-4 py-3.5 w-[13%] text-right">Total Facturar</th>
                                <th class="px-4 py-3.5 w-[14%] text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700/60 font-sans">
                            @forelse($cotizaciones as $cotizacion)
                                <tr class="hover:bg-gray-50 dark:hover:bg-darker-bg/50 transition-colors">
                                    {{-- Código y Versión --}}
                                    <td class="px-4 py-3.5 font-mono">
                                        <div class="font-bold text-gray-900 dark:text-white text-sm">
                                            {{ $cotizacion->codigo_formateado }}
                                        </div>
                                        <div class="text-[10px] text-gray-400 font-sans">
                                            v{{ $cotizacion->version_formateada }}
                                        </div>
                                    </td>

                                    {{-- Tipo --}}
                                    <td class="px-3 py-3.5">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold {{ $cotizacion->tipo_cotizacion === 'servicio' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-300' }}">
                                            {{ $cotizacion->tipo_cotizacion === 'servicio' ? '🛠️ Servicio' : '📦 Producto' }}
                                        </span>
                                    </td>

                                    {{-- Cliente y Asunto --}}
                                    <td class="px-4 py-3.5">
                                        <div class="font-bold text-gray-900 dark:text-white">
                                            {{ $cotizacion->cliente->nombre ?? '—' }}
                                        </div>
                                        <div class="text-gray-500 dark:text-gray-400 text-[11px] truncate max-w-[260px]">
                                            {{ $cotizacion->asunto ?: '—' }}
                                        </div>
                                        @if($cotizacion->ordenCompraCliente)
                                            <div class="text-[10px] text-cyan-600 dark:text-cyan-400 font-mono mt-0.5">
                                                OC: {{ $cotizacion->ordenCompraCliente->numero_oc }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Fecha --}}
                                    <td class="px-3 py-3.5 text-center text-gray-600 dark:text-gray-400 font-mono">
                                        {{ $cotizacion->fecha?->format('d/m/Y') }}
                                    </td>

                                    {{-- Estado --}}
                                    <td class="px-3 py-3.5 text-center">
                                        @php
                                            $colores = [
                                                'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                'aprobada'  => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                                'rechazada' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                                'facturada' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
                                            ];
                                        @endphp
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $colores[$cotizacion->estado] ?? '' }}">
                                            ● {{ ucfirst($cotizacion->estado) }}
                                        </span>
                                    </td>

                                    {{-- Total a Facturar --}}
                                    <td class="px-4 py-3.5 text-right font-mono font-bold text-gray-900 dark:text-white text-sm">
                                        S/ {{ number_format($cotizacion->resumen->total_facturar ?? 0, 2) }}
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="px-4 py-3.5 text-right">
                                        <div class="flex justify-end gap-2">
                                            <x-action-button icon="view" color="cyan" label="Ver detalle de cotización" :href="route('cotizaciones.show', $cotizacion)" />
                                            <x-action-button icon="pdf" color="slate" label="Imprimir / PDF" :href="route('cotizaciones.pdf', $cotizacion)" target="_blank" />
                                            @if($cotizacion->estado === 'pendiente')
                                                <x-action-button icon="edit" color="amber" label="Editar cotización" :href="route('cotizaciones.edit', $cotizacion)" />
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <div class="text-3xl mb-2">📋</div>
                                        <p class="font-semibold">No se encontraron cotizaciones</p>
                                        <p class="text-xs mt-1">Haz clic en "+ Nueva Cotización" para emitir la primera propuesta comercial.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($cotizaciones->hasPages())
                    <div class="px-4 py-3.5 border-t border-gray-200 dark:border-gray-700/60">
                        {{ $cotizaciones->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
