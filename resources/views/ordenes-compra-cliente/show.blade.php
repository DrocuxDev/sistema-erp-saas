<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('ordenes-compra-cliente.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
                ← Volver
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">OC: {{ $orden->numero_oc }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Detalle de la orden de compra del cliente</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Detalle --}}
            <div class="bg-white dark:bg-card-bg rounded-xl shadow p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Cliente</p>
                        <p class="text-base font-bold text-gray-900 dark:text-white">{{ $orden->cliente->nombre ?? '—' }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">RUC: {{ $orden->cliente->ruc ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Número de OC</p>
                        <p class="text-base font-bold font-mono text-gray-900 dark:text-white">{{ $orden->numero_oc }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Fecha de Recepción</p>
                        <p class="text-base text-gray-900 dark:text-white">{{ $orden->fecha_recepcion?->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Estado</p>
                        @php
                            $colores = [
                                'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                'procesada' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                'anulada'   => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                            ];
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $colores[$orden->estado] ?? '' }}">
                            {{ ucfirst($orden->estado) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Registrado por</p>
                        <p class="text-base text-gray-900 dark:text-white">{{ $orden->usuario->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Fecha de Registro</p>
                        <p class="text-base text-gray-900 dark:text-white">{{ $orden->created_at?->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($orden->observaciones)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Observaciones</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $orden->observaciones }}</p>
                    </div>
                    @endif
                </div>

                {{-- Archivo --}}
                @if($orden->archivo)
                <div class="mt-5 pt-5 border-t border-gray-200 dark:border-gray-600/30">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Archivo adjunto</p>
                    <a href="{{ Storage::url($orden->archivo) }}" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg text-sm hover:bg-blue-200 transition-colors font-medium">
                        📄 Descargar / Ver archivo
                    </a>
                </div>
                @endif
            </div>

            {{-- Documentos generados --}}
            @if($orden->cotizacionGenerada || $orden->ventaGenerada)
            <div class="bg-white dark:bg-card-bg rounded-xl shadow p-6">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Documentos generados</h3>
                <div class="flex flex-wrap gap-3">
                    @if($orden->cotizacionGenerada)
                        <a href="{{ route('cotizaciones.show', $orden->cotizacionGenerada) }}"
                           class="px-4 py-2 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-lg text-sm font-medium hover:bg-purple-200 transition-colors">
                            Ver Cotización #{{ $orden->cotizacionGenerada->id }}
                        </a>
                    @endif
                    @if($orden->ventaGenerada)
                        <a href="{{ route('ventas.show', $orden->ventaGenerada) }}"
                           class="px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg text-sm font-medium hover:bg-green-200 transition-colors">
                            Ver Venta #{{ $orden->ventaGenerada->id }}
                        </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Acciones --}}
            <div class="flex flex-wrap gap-3">
                @if($orden->estado === 'pendiente')
                    <a href="{{ route('cotizaciones.create', ['orden_compra_cliente_id' => $orden->id]) }}"
                       class="px-5 py-2 bg-purple-primary text-white rounded-lg text-sm font-semibold hover:bg-purple-light transition-colors">
                        📋 Generar Cotización
                    </a>
                    <a href="{{ route('ventas.create', ['orden_compra_cliente_id' => $orden->id]) }}"
                       class="px-5 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors">
                        🧾 Generar Venta
                    </a>
                @endif
                <a href="{{ route('ordenes-compra-cliente.edit', $orden) }}"
                   class="px-5 py-2 bg-gray-200 dark:bg-darker-bg text-gray-700 dark:text-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors">
                    Editar
                </a>
            </div>

        </div>
    </div>
</x-app-layout>