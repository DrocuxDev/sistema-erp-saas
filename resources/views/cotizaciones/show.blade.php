<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('cotizaciones.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
                    ← Volver
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Cotización #{{ $cotizacion->id }}</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $cotizacion->cliente->nombre ?? '—' }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="document.getElementById('modal-pdf').classList.remove('hidden')"
                    class="px-4 py-2 bg-gray-200 dark:bg-darker-bg text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-300 transition-colors">
                    PDF / Imprimir
                </button>
                @if($cotizacion->estado === 'pendiente')
                    <a href="{{ route('cotizaciones.edit', $cotizacion) }}"
                       class="px-4 py-2 bg-gray-200 dark:bg-darker-bg text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors">
                        Editar
                    </a>
                    <form action="{{ route('cotizaciones.aprobar', $cotizacion) }}" method="POST"
                          onsubmit="return confirm('¿Aprobar esta cotización?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors">
                            Aprobar
                        </button>
                    </form>
                @endif
                @if($cotizacion->estado === 'aprobada')
                    @if(\Illuminate\Support\Facades\Route::has('ventas.create'))
                        <a href="{{ route('ventas.create', ['cotizacion_id' => $cotizacion->id]) }}"
                            class="px-4 py-2 bg-purple-primary text-white rounded-lg text-sm font-semibold hover:bg-purple-light transition-colors">
                                Generar Venta
                        </a>
                    @else
                        <span class="px-4 py-2 bg-gray-200 dark:bg-darker-bg text-gray-500 rounded-lg text-sm font-semibold cursor-not-allowed" title="Módulo de Ventas aún no disponible">
                            Generar Venta (próximamente)
                        </span>
                    @endif
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-card-bg rounded-xl shadow p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Cliente</p>
                        <p class="font-bold text-gray-900 dark:text-white">{{ $cotizacion->cliente->nombre ?? '—' }}</p>
                        <p class="text-sm text-gray-500">RUC: {{ $cotizacion->cliente->ruc ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Fecha</p>
                        <p class="text-gray-900 dark:text-white">{{ $cotizacion->fecha?->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Estado</p>
                        @php
                            $colores = [
                                'pendiente' => 'bg-yellow-100 text-yellow-800',
                                'aprobada'  => 'bg-blue-100 text-blue-800',
                                'rechazada' => 'bg-red-100 text-red-800',
                                'facturada' => 'bg-green-100 text-green-800',
                            ];
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $colores[$cotizacion->estado] ?? '' }}">
                            {{ ucfirst($cotizacion->estado) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Registrado por</p>
                        <p class="text-gray-900 dark:text-white">{{ $cotizacion->usuario->name ?? '—' }}</p>
                    </div>
                    @if($cotizacion->ordenCompraCliente)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">OC Cliente</p>
                        <a href="{{ route('ordenes-compra-cliente.show', $cotizacion->ordenCompraCliente) }}"
                           class="text-purple-primary hover:underline font-medium">
                            {{ $cotizacion->ordenCompraCliente->numero_oc }}
                        </a>
                    </div>
                    @endif
                    @if($cotizacion->observaciones)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Observaciones</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $cotizacion->observaciones }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-card-bg rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600/30">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Detalle de líneas</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-darker-bg">
                            <tr>
                                <th class="px-4 py-3 text-left">Producto</th>
                                <th class="px-4 py-3 text-left">Proveedor</th>
                                <th class="px-4 py-3 text-right">Cant.</th>
                                <th class="px-4 py-3 text-right">P. Venta</th>
                                <th class="px-4 py-3 text-right">P. Compra</th>
                                <th class="px-4 py-3 text-right">IGV Compra</th>
                                <th class="px-4 py-3 text-right">Total Venta</th>
                                <th class="px-4 py-3 text-right">Ganancia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600/30">
                            @foreach($cotizacion->detalles as $detalle)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $detalle->producto->nombre ?? $detalle->descripcion }}</p>
                                        <p class="text-xs text-gray-500">{{ $detalle->unidad_medida }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $detalle->proveedor->nombre ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-mono">{{ number_format($detalle->cantidad, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono">S/ {{ number_format($detalle->precio_venta_unit, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono">S/ {{ number_format($detalle->precio_compra_unit, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono">S/ {{ number_format($detalle->igv_compra, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-semibold">S/ {{ number_format($detalle->precio_venta_total, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono {{ $detalle->ganancia_linea >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        S/ {{ number_format($detalle->ganancia_linea, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($cotizacion->resumen)
            <div class="bg-white dark:bg-card-bg rounded-xl shadow p-6">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Resumen financiero</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="space-y-2">
                        <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span class="font-mono font-semibold">S/ {{ number_format($cotizacion->resumen->subtotal, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">IGV</span><span class="font-mono">S/ {{ number_format($cotizacion->resumen->igv, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Retención</span><span class="font-mono">S/ {{ number_format($cotizacion->resumen->retencion, 2) }}</span></div>
                        <div class="flex justify-between border-t pt-2"><span class="font-semibold">Total a facturar</span><span class="font-mono font-bold text-purple-primary">S/ {{ number_format($cotizacion->resumen->total_facturar, 2) }}</span></div>
                    </div>

                    @if(auth()->user()->rol === 'administrador')
                    <div class="space-y-2">
                        <div class="flex justify-between"><span class="text-gray-600">Compra activa</span><span class="font-mono">S/ {{ number_format($cotizacion->resumen->compra_activa, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Compra fija</span><span class="font-mono">S/ {{ number_format($cotizacion->resumen->compra_fija, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Ganancia</span><span class="font-mono text-green-600">S/ {{ number_format($cotizacion->resumen->ganancia, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Comisión venta</span><span class="font-mono">S/ {{ number_format($cotizacion->resumen->comision_venta, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Comisión ganancia</span><span class="font-mono">S/ {{ number_format($cotizacion->resumen->comision_ganancia, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Comisión total</span><span class="font-mono">S/ {{ number_format($cotizacion->resumen->comision_total, 2) }}</span></div>
                        <div class="flex justify-between border-t pt-2"><span class="font-semibold">Rentabilidad</span><span class="font-mono font-bold {{ $cotizacion->resumen->rentabilidad >= 0 ? 'text-green-600' : 'text-red-600' }}">S/ {{ number_format($cotizacion->resumen->rentabilidad, 2) }}</span></div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
{{-- Modal Vista Previa PDF  --}}
    <div id="modal-pdf" class="hidden fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/70">
        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-2xl ring-1 ring-black/10 w-full max-w-6xl h-[80vh] flex flex-col overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-600/30 bg-gray-50 dark:bg-darker-bg">
                <h3 class="font-bold text-gray-900 dark:text-white">Vista previa — Cotización #{{ $cotizacion->id }}</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('cotizaciones.pdf.descargar', $cotizacion) }}"
                        class="px-4 py-1.5 bg-purple-primary text-white rounded-lg text-sm font-semibold hover:bg-purple-light transition-colors">
                        Descargar PDF
                    </a>
                    <button type="button" onclick="document.getElementById('modal-pdf').classList.add('hidden')"
                            class="px-3 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-300 transition-colors">
                        ✕ Cerrar
                    </button>
                </div>
            </div>
            <div class="flex-1 bg-gray-200 dark:bg-gray-900 p-4 overflow-hidden">
                <iframe src="{{ route('cotizaciones.pdf', $cotizacion) }}"
                        class="w-full h-full bg-white rounded-lg shadow-inner"
                        style="border: none; overflow: hidden;"
                        scrolling="auto"></iframe>
            </div>
        </div>
    </div>
</x-app-layout>
