<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Cotizaciones</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Gestión de cotizaciones comerciales</p>
            </div>
            <a href="{{ route('cotizaciones.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-purple-primary text-white rounded-lg hover:bg-purple-light transition-colors font-medium text-sm">
                + Nueva Cotización
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form method="GET" class="bg-white dark:bg-card-bg rounded-xl shadow p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Estado</label>
                    <select name="estado" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white text-sm px-3 py-2">
                        <option value="">Todos</option>
                        @foreach(['pendiente', 'aprobada', 'rechazada', 'facturada'] as $estado)
                            <option value="{{ $estado }}" {{ request('estado') === $estado ? 'selected' : '' }}>
                                {{ ucfirst($estado) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Cliente</label>
                    <select name="cliente_id" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white text-sm px-3 py-2">
                        <option value="">Todos</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-purple-primary text-white rounded-lg text-sm hover:bg-purple-light transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('cotizaciones.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-darker-bg text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-300 transition-colors">
                    Limpiar
                </a>
            </form>

            <div class="bg-white dark:bg-card-bg rounded-xl shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-darker-bg border-b border-gray-200 dark:border-gray-600/30">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">#</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Cliente</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Fecha</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Estado</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-400">Subtotal</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-400">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600/30">
                            @forelse($cotizaciones as $cotizacion)
                                <tr class="hover:bg-gray-50 dark:hover:bg-darker-bg transition-colors">
                                    <td class="px-4 py-3 font-mono font-semibold text-gray-900 dark:text-white">#{{ $cotizacion->id }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $cotizacion->cliente->nombre ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $cotizacion->fecha?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $colores = [
                                                'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                'aprobada'  => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                                'rechazada' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                                'facturada' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $colores[$cotizacion->estado] ?? '' }}">
                                            {{ ucfirst($cotizacion->estado) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">
                                        S/ {{ number_format($cotizacion->resumen->subtotal ?? 0, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('cotizaciones.show', $cotizacion) }}"
                                                class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg text-xs hover:bg-blue-200 transition-colors">
                                                Ver
                                            </a>
                                            @if($cotizacion->estado === 'pendiente')
                                                <a href="{{ route('cotizaciones.edit', $cotizacion) }}"
                                                    class="px-3 py-1 bg-gray-100 dark:bg-darker-bg text-gray-700 dark:text-gray-300 rounded-lg text-xs hover:bg-gray-200 transition-colors">
                                                    Editar
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">
                                        No hay cotizaciones registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($cotizaciones->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-600/30">
                        {{ $cotizaciones->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
