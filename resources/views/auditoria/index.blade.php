<x-app-layout>
    <div class="p-4 sm:p-6 space-y-6">
        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <span>Administración</span>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Auditoría</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                    Auditoría & Trazabilidad de Operaciones
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Bitácora inmutable de eventos, creaciones, modificaciones y eliminaciones en el ERP.</p>
            </div>
        </div>

        <!-- Filtros -->
        <form method="GET" action="{{ route('auditoria.index') }}" class="bg-white dark:bg-card-bg rounded-xl shadow-sm border border-gray-200 dark:border-gray-700/50 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Tabla</label>
                    <input type="text" name="tabla" value="{{ request('tabla') }}" placeholder="Nombre de tabla..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Acción</label>
                    <select name="accion" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                        <option value="">Todas</option>
                        <option value="creacion" {{ request('accion') === 'creacion' ? 'selected' : '' }}>Creación</option>
                        <option value="edicion" {{ request('accion') === 'edicion' ? 'selected' : '' }}>Edición</option>
                        <option value="anulacion" {{ request('accion') === 'anulacion' ? 'selected' : '' }}>Anulación</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Usuario</label>
                    <input type="text" name="usuario" value="{{ request('usuario') }}" placeholder="Nombre de usuario..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Fecha</label>
                    <input type="date" name="fecha" value="{{ request('fecha') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 bg-white dark:bg-darker-bg text-gray-900 dark:text-gray-100">
                </div>
                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                    <button type="submit" class="flex-1 px-4 py-2 bg-cyan-600 dark:bg-cyan-500 text-white rounded-lg hover:bg-cyan-700 dark:hover:bg-cyan-600 transition-colors text-sm font-semibold flex items-center justify-center gap-1 shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Filtrar
                    </button>
                    <a href="{{ route('auditoria.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-semibold">
                        Limpiar
                    </a>
                </div>
            </div>
        </form>

        <!-- Contador -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-sm text-gray-600 dark:text-gray-400">
            <div>
                Mostrando {{ $registros->firstItem() ?? 0 }} - {{ $registros->lastItem() ?? 0 }} de {{ $registros->total() }} registros
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-white dark:bg-card-bg rounded-xl shadow-sm border border-gray-200 dark:border-gray-700/50 overflow-hidden">
            @if ($registros->isEmpty())
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No hay registros de auditoría que coincidan con la búsqueda.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50/80 dark:bg-darker-bg border-b border-gray-200 dark:border-gray-700/60">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Fecha / Hora</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Tabla / Registro</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Acción</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Usuario</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Descripción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-card-bg divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($registros as $registro)
                                <tr class="hover:bg-gray-50 dark:hover:bg-darker-bg transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">#{{ $registro->id }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <div>{{ $registro->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $registro->created_at->format('H:i:s') }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $registro->tabla }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">ID: #{{ $registro->registro_id }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        @if($registro->accion === 'creacion')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                                Creación
                                            </span>
                                        @elseif($registro->accion === 'edicion')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400">
                                                Edición
                                            </span>
                                        @elseif($registro->accion === 'anulacion')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">
                                                Anulación
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-400">{{ $registro->accion }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        @if($registro->usuario)
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 bg-slate-700 dark:bg-slate-600 rounded-full flex items-center justify-center text-white font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($registro->usuario->name, 0, 2)) }}
                                                </div>
                                                <div class="truncate max-w-xs">
                                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $registro->usuario->name }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $registro->usuario->email }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400 text-xs">Sistema</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $registro->detalle ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($registros->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $registros->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
