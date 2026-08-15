<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('ventas.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
                ← Volver
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Venta #{{ $venta->id }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $venta->cliente->nombre ?? '—' }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-card-bg rounded-xl shadow p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Cliente</p>
                        <p class="font-bold text-gray-900 dark:text-white">{{ $venta->cliente->nombre ?? '—' }}</p>
                        <p class="text-sm text-gray-500">RUC: {{ $venta->cliente->ruc ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Fecha</p>
                        <p class="text-gray-900 dark:text-white">{{ $venta->fecha?->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Registrado por</p>
                        <p class="text-gray-900 dark:text-white">{{ $venta->usuario->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Total</p>
                        <p class="text-xl font-bold text-purple-primary">S/ {{ number_format($venta->total, 2) }}</p>
                    </div>
                    @if($venta->cotizacion)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Cotización origen</p>
                        <a href="{{ route('cotizaciones.show', $venta->cotizacion) }}" class="text-purple-primary hover:underline font-medium">
                            Cotización #{{ $venta->cotizacion->id }}
                        </a>
                    </div>
                    @endif
                    @if($venta->ordenCompraCliente)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">OC Cliente</p>
                        <a href="{{ route('ordenes-compra-cliente.show', $venta->ordenCompraCliente) }}" class="text-purple-primary hover:underline font-medium">
                            {{ $venta->ordenCompraCliente->numero_oc }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-card-bg rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600/30">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Detalle de productos</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-darker-bg">
                            <tr>
                                <th class="px-4 py-3 text-left">Producto</th>
                                <th class="px-4 py-3 text-right">Cant.</th>
                                <th class="px-4 py-3 text-right">P. Venta</th>
                                <th class="px-4 py-3 text-right">P. Compra</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                @if(auth()->user()->rol === 'administrador')
                                <th class="px-4 py-3 text-right">Ganancia</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600/30">
                            @foreach($detalle as $linea)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $linea->producto_nombre }}</p>
                                        <p class="text-xs text-gray-500">{{ $linea->unidad_medida }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono">{{ number_format($linea->cantidad, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono">S/ {{ number_format($linea->precio_venta_unit, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono">S/ {{ number_format($linea->precio_compra_unit, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-semibold">S/ {{ number_format($linea->cantidad * $linea->precio_venta_unit, 2) }}</td>
                                    @if(auth()->user()->rol === 'administrador')
                                    <td class="px-4 py-3 text-right font-mono {{ $linea->ganancia_linea >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        S/ {{ number_format($linea->ganancia_linea, 2) }}
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-600/30 flex justify-end">
                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                        <p class="text-2xl font-bold text-purple-primary">S/ {{ number_format($venta->total, 2) }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>