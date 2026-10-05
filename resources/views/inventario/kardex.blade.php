<x-app-layout>
    <div class="p-6">
        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <span>Maestros</span>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Inventario & Kardex</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                    Kardex y control de inventario
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Trazabilidad física de entradas por compras, salidas por ventas y ajustes manuales.</p>
            </div>
            <div class="flex items-center gap-3">
                @if(auth()->user()->puedeAccion('ajustar_inventario'))
                <a href="{{ route('inventario.ajuste.create') }}" class="w-full sm:w-auto px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 font-semibold text-xs sm:text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo Ajuste Manual
                </a>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filtros -->
        <form method="GET" action="{{ route('inventario.kardex') }}" class="bg-white dark:bg-card-bg rounded-xl shadow-2xs border border-gray-200/90 dark:border-gray-700/70 p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Producto</label>
                    <select name="producto_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                        <option value="">Todos los productos</option>
                        @foreach ($productos as $prod)
                            <option value="{{ $prod->id }}" {{ request('producto_id') == $prod->id ? 'selected' : '' }}>
                                {{ $prod->nombre }} ({{ $prod->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Tipo de Movimiento</label>
                    <select name="tipo" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                        <option value="">Todos</option>
                        <option value="entrada" {{ request('tipo') === 'entrada' ? 'selected' : '' }}>Entrada</option>
                        <option value="salida" {{ request('tipo') === 'salida' ? 'selected' : '' }}>Salida</option>
                        <option value="ajuste" {{ request('tipo') === 'ajuste' ? 'selected' : '' }}>Ajuste</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Fecha Fin</label>
                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100 font-mono">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary !min-h-[38px] !py-1.5 flex-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Filtrar</span>
                    </button>
                    <a href="{{ route('inventario.kardex') }}" class="btn-secondary !min-h-[38px] !py-1.5">
                        Limpiar
                    </a>
                </div>
            </div>
        </form>

        <!-- Paginación superior -->
        <div class="flex items-center justify-between mb-3 text-sm text-gray-600 dark:text-gray-400">
            <div>
                Mostrando <span class="font-mono font-semibold text-gray-900 dark:text-gray-100">{{ $movimientos->firstItem() ?? 0 }}</span> - <span class="font-mono font-semibold text-gray-900 dark:text-gray-100">{{ $movimientos->lastItem() ?? 0 }}</span> de <span class="font-mono font-semibold text-gray-900 dark:text-gray-100">{{ $movimientos->total() }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span>Pág.</span>
                <input type="number" value="{{ $movimientos->currentPage() }}" min="1" max="{{ $movimientos->lastPage() }}" class="w-16 px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-center bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100 font-mono">
                <span class="font-mono">de {{ $movimientos->lastPage() }}</span>
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-2xs border border-gray-200/90 dark:border-gray-700/70 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50/80 dark:bg-darker-bg/70 border-b border-gray-200 dark:border-gray-700/60 text-xs font-semibold text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3.5 text-left">ID</th>
                            <th class="px-4 py-3.5 text-left">Fecha</th>
                            <th class="px-4 py-3.5 text-left">Producto</th>
                            <th class="px-4 py-3.5 text-center">Tipo</th>
                            <th class="px-4 py-3.5 text-right">Cantidad</th>
                            <th class="px-4 py-3.5 text-left">Referencia</th>
                            <th class="px-4 py-3.5 text-left">Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-card-bg divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($movimientos as $mov)
                            <tr class="hover:bg-gray-50 dark:hover:bg-darker-bg transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">#{{ $mov->id }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $mov->producto->nombre ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $mov->producto->sku ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($mov->tipo === 'entrada')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                            + Entrada
                                        </span>
                                    @elseif($mov->tipo === 'salida')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">
                                            - Salida
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400">
                                            ⚙️ Ajuste
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format($mov->cantidad, 2) }} {{ $mov->producto->unidad_medida ?? '' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 capitalize">
                                    {{ $mov->referencia_tipo }} {{ $mov->referencia_id ? '#'.$mov->referencia_id : '' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $mov->usuario->name ?? 'Sistema' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No hay movimientos de inventario registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($movimientos->hasPages())
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ $movimientos->firstItem() ?? 0 }} - {{ $movimientos->lastItem() ?? 0 }} / {{ $movimientos->total() }}</div>
                <div>{{ $movimientos->links() }}</div>
            </div>
        @endif
    </div>
</x-app-layout>
