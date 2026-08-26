<x-app-layout>
    <div class="p-4 sm:p-6 space-y-6">
        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                    <span>Maestros</span>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Productos</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800/60">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    Catálogo de Productos y Precios
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Control de artículos, precios base de compra/venta y alertas de stock mínimo.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('productos.create') }}" class="w-full sm:w-auto px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 font-semibold text-xs sm:text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo Producto
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-400 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filtros -->
        <form method="GET" action="{{ route('productos.index') }}" class="bg-white dark:bg-card-bg rounded-xl shadow-sm border border-gray-200 dark:border-gray-700/50 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                    <select name="tipo" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-primary bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                        <option value="">Todos</option>
                        <option value="vendible" {{ request('tipo') === 'vendible' ? 'selected' : '' }}>Vendibles</option>
                        <option value="comprable" {{ request('tipo') === 'comprable' ? 'selected' : '' }}>Comprables</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Estado Stock</label>
                    <select name="estado" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-primary bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                        <option value="">Todos</option>
                        <option value="disponible" {{ request('estado') === 'disponible' ? 'selected' : '' }}>Stock Normal</option>
                        <option value="bajo" {{ request('estado') === 'bajo' ? 'selected' : '' }}>⚠️ Stock Bajo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Categoría</label>
                    <input type="text" name="categoria" value="{{ request('categoria') }}" placeholder="Buscar categoría..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-primary bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">SKU / Nombre</label>
                    <input type="text" name="marca" value="{{ request('marca') }}" placeholder="SKU..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-primary bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                </div>
                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                    <button type="submit" class="flex-1 px-4 py-2 bg-purple-primary text-white rounded-lg hover:bg-purple-light transition-colors text-sm font-semibold flex items-center justify-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Buscar
                    </button>
                    <a href="{{ route('productos.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-semibold">
                        Limpiar
                    </a>
                </div>
            </div>
        </form>

        <!-- Contador -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-sm text-gray-600 dark:text-gray-400">
            <div>
                Mostrando {{ $productos->firstItem() ?? 0 }} - {{ $productos->lastItem() ?? 0 }} de {{ $productos->total() }} productos
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-white dark:bg-card-bg rounded-xl shadow-sm border border-gray-200 dark:border-gray-700/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-darker-bg">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">SKU</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Categoría</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">U.Medida</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Operaciones</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Stock</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-card-bg divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($productos as $producto)
                            <tr class="hover:bg-gray-50 dark:hover:bg-darker-bg transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('productos.show', $producto) }}" class="text-sm font-semibold text-purple-primary hover:text-purple-light hover:underline font-mono">
                                        {{ $producto->sku }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 max-w-xs">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $producto->nombre }}</div>
                                    @if($producto->descripcion)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $producto->descripcion }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $producto->categoria ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 font-semibold">{{ $producto->unidad_medida }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($producto->puede_venderse)
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">Venta</span>
                                        @endif
                                        @if($producto->puede_comprarse)
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400">Compra</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="text-sm font-bold {{ $producto->stock_actual <= $producto->stock_minimo ? 'text-pink-accent' : 'text-gray-900 dark:text-gray-100' }}">
                                        {{ number_format($producto->stock_actual, 2) }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">mín: {{ number_format($producto->stock_minimo, 2) }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="{{ route('productos.show', $producto) }}" class="text-blue-accent hover:text-blue-400 p-1" title="Ver Ficha">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('productos.edit', $producto) }}" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 p-1" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <button type="button" onclick="confirmDelete({{ $producto->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 p-1" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                        <form id="delete-form-{{ $producto->id }}" action="{{ route('productos.destroy', $producto) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No se encontraron productos registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($productos->hasPages())
            <div class="mt-4">
                {{ $productos->links() }}
            </div>
        @endif
    </div>

    <script>
        function confirmDelete(productId) {
            if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
                document.getElementById('delete-form-' + productId).submit();
            }
        }
    </script>
</x-app-layout>
