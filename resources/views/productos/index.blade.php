<x-app-layout>
    <div class="p-4 sm:p-6 space-y-6">
        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <span>Maestros</span>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Productos</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
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
                    <select name="tipo" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                        <option value="">Todos</option>
                        <option value="vendible" {{ request('tipo') === 'vendible' ? 'selected' : '' }}>Vendibles</option>
                        <option value="comprable" {{ request('tipo') === 'comprable' ? 'selected' : '' }}>Comprables</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Estado de existencias</label>
                    <select name="estado" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                        <option value="">Todos</option>
                        <option value="disponible" {{ request('estado') === 'disponible' ? 'selected' : '' }}>Existencias normales</option>
                        <option value="bajo" {{ request('estado') === 'bajo' ? 'selected' : '' }}>⚠️ Existencias bajas</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Categoría</label>
                    <input type="text" name="categoria" value="{{ request('categoria') }}" placeholder="Buscar categoría..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">SKU / Nombre</label>
                    <input type="text" name="marca" value="{{ request('marca') }}" placeholder="SKU..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                </div>
                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                    <button type="submit" class="flex-1 px-4 py-2 bg-cyan-600 dark:bg-cyan-500 text-white rounded-lg hover:bg-cyan-700 dark:hover:bg-cyan-600 transition-colors text-sm font-semibold flex items-center justify-center gap-1 shadow-2xs">
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
                    <thead class="bg-gray-50/80 dark:bg-darker-bg border-b border-gray-200 dark:border-gray-700/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">SKU</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Producto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Categoría</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">U.Medida</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Operaciones</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Existencias</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-card-bg divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($productos as $producto)
                            <tr class="hover:bg-gray-50 dark:hover:bg-darker-bg transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('productos.show', $producto) }}" class="text-sm font-semibold text-cyan-600 dark:text-cyan-400 hover:text-cyan-500 hover:underline font-mono">
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
                                    <div class="text-sm font-bold font-mono {{ $producto->stock_actual <= $producto->stock_minimo ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-gray-100' }}">
                                        {{ number_format($producto->stock_actual, 2) }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">mín: {{ number_format($producto->stock_minimo, 2) }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <x-action-button icon="view" color="cyan" label="Ver ficha de producto" :href="route('productos.show', $producto)" />
                                        <x-action-button icon="edit" color="amber" label="Editar producto" :href="route('productos.edit', $producto)" />
                                        <x-action-button icon="delete" color="rose" label="Eliminar producto" onclick="confirmDelete({{ $producto->id }})" />
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
        async function confirmDelete(productId) {
            await submitAfterConfirm('delete-form-' + productId, '¿Eliminar este producto?', 'Esta acción no se puede deshacer.');
        }
    </script>
</x-app-layout>
