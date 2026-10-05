<x-app-layout>
    <div class="p-4 sm:p-6 space-y-6">
        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <span>Maestros</span>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Proveedores</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                    Catálogo de Proveedores & Distribuidores
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Gestión de empresas mayoristas, distribuidores autorizados y canales de suministro.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('proveedores.create') }}" class="w-full sm:w-auto px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 font-semibold text-xs sm:text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo Proveedor
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-400 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filtros -->
        <form method="GET" action="{{ route('proveedores.index') }}" class="bg-white dark:bg-card-bg rounded-xl shadow-sm border border-gray-200 dark:border-gray-700/50 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nombre/RUC</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                    <select name="estado" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                        <option value="">Todos</option>
                        <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Condición Pago</label>
                    <input type="text" name="condicion_pago" value="{{ request('condicion_pago') }}" placeholder="Ej: Contado..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Contacto</label>
                    <input type="text" name="contacto" value="{{ request('contacto') }}" placeholder="Nombre contacto..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                    <input type="text" name="telefono" value="{{ request('telefono') }}" placeholder="Teléfono..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                </div>
                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                    <button type="submit" class="flex-1 px-4 py-2 bg-cyan-600 dark:bg-cyan-500 text-white rounded-lg hover:bg-cyan-700 dark:hover:bg-cyan-600 transition-colors text-sm font-semibold flex items-center justify-center gap-1 shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Buscar
                    </button>
                    <a href="{{ route('proveedores.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-semibold">
                        Limpiar
                    </a>
                </div>
            </div>
        </form>

        <!-- Contador y paginación rápida -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-sm text-gray-600 dark:text-gray-400">
            <div>
                Mostrando {{ $proveedores->firstItem() ?? 0 }} - {{ $proveedores->lastItem() ?? 0 }} de {{ $proveedores->total() }} registros
            </div>
        </div>

        <!-- Tabla escritorio / Cards móvil -->
        <div class="bg-white dark:bg-card-bg rounded-xl shadow-sm border border-gray-200 dark:border-gray-700/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50/80 dark:bg-darker-bg border-b border-gray-200 dark:border-gray-700/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Proveedor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">RUC</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Contacto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Teléfono / correo electrónico</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Condición</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Estado</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-card-bg divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($proveedores as $proveedor)
                            <tr class="hover:bg-gray-50 dark:hover:bg-darker-bg transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('proveedores.show', $proveedor) }}" class="text-sm font-semibold text-cyan-600 dark:text-cyan-400 hover:text-cyan-500 hover:underline">
                                        #{{ $proveedor->id }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $proveedor->nombre }}</div>
                                    @if($proveedor->razon_social && $proveedor->razon_social !== $proveedor->nombre)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $proveedor->razon_social }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $proveedor->ruc }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $proveedor->contacto ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    <div>{{ $proveedor->telefono ?? '-' }}</div>
                                    @if($proveedor->email)
                                        <a href="mailto:{{ $proveedor->email }}" class="text-xs text-cyan-600 dark:text-cyan-400 hover:underline block truncate max-w-xs">{{ $proveedor->email }}</a>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $proveedor->condicion_pago }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    @if($proveedor->estado === 'activo')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-400">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <x-action-button icon="view" color="cyan" label="Ver ficha de proveedor" :href="route('proveedores.show', $proveedor)" />
                                        <x-action-button icon="edit" color="amber" label="Editar proveedor" :href="route('proveedores.edit', $proveedor)" />
                                        <x-action-button icon="delete" color="rose" label="Eliminar proveedor" onclick="confirmDelete({{ $proveedor->id }})" />
                                        <form id="delete-form-{{ $proveedor->id }}" action="{{ route('proveedores.destroy', $proveedor) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No se encontraron proveedores registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($proveedores->hasPages())
            <div class="mt-4">
                {{ $proveedores->links() }}
            </div>
        @endif
    </div>

    <script>
        async function confirmDelete(id) {
            await submitAfterConfirm('delete-form-' + id, '¿Eliminar este proveedor?', 'Esta acción no se puede deshacer.');
        }
    </script>
</x-app-layout>
