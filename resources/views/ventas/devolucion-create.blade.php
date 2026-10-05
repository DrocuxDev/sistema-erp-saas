<x-app-layout>
    <div class="page-container py-4 sm:py-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('ventas.index') }}" class="hover:text-cyan-600 dark:hover:text-cyan-400">Ventas</a>
                    <span>/</span>
                    <a href="{{ route('ventas.show', $venta) }}" class="hover:text-cyan-600 dark:hover:text-cyan-400">{{ $venta->codigo_formateado }}</a>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Devolución</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Registrar devolución</h1>
                <p class="text-xs text-gray-500 mt-1">{{ $venta->cliente->nombre }} · Venta del {{ $venta->fecha->format('d/m/Y') }}</p>
            </div>
            <a href="{{ route('ventas.show', $venta) }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm transition-all">Volver</a>
        </div>

        @if($errors->any())
            <div class="p-4 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-2xl text-xs">
                <ul class="list-disc pl-4 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('ventas.devoluciones.store', $venta) }}" class="space-y-6">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', $idempotencyKey) }}">

            <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 overflow-hidden">
                <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-5 text-white">
                    <h2 class="text-sm font-bold">Productos de {{ $venta->codigo_formateado }}</h2>
                    <p class="text-xs text-slate-300 mt-1">Ingrese la cantidad física recibida. Deje cero en las líneas no devueltas.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-900 dark:bg-slate-950 text-white font-semibold">
                            <tr>
                                <th class="px-4 py-3">Producto / SKU</th>
                                <th class="px-4 py-3 text-right">Vendido</th>
                                <th class="px-4 py-3 text-right">Pendiente</th>
                                <th class="px-4 py-3 text-right">A devolver</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @foreach($venta->detalles as $indice => $linea)
                                <tr class="hover:bg-gray-50 dark:hover:bg-darker-bg/50">
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-gray-900 dark:text-white">{{ $linea->producto->nombre }}</p>
                                        <p class="text-[11px] font-mono text-gray-400">{{ $linea->producto->sku ?? '—' }}</p>
                                        <input type="hidden" name="lineas[{{ $indice }}][venta_detalle_id]" value="{{ $linea->id }}">
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono">{{ number_format($linea->cantidad, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-cyan-700 dark:text-cyan-300">{{ number_format($saldos[$linea->id] / 100, 2) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <input type="number" name="lineas[{{ $indice }}][cantidad]" value="{{ old("lineas.$indice.cantidad", '0') }}" min="0" max="{{ number_format($saldos[$linea->id] / 100, 2, '.', '') }}" step="0.01" @disabled($saldos[$linea->id] === 0)
                                            class="w-28 rounded-xl border-gray-300 dark:border-gray-700 dark:bg-darker-bg dark:text-white text-xs text-right font-mono">
                                        @if($saldos[$linea->id] === 0)<input type="hidden" name="lineas[{{ $indice }}][cantidad]" value="0">@endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="fecha" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Fecha de devolución</label>
                        <input id="fecha" type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required
                            class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-darker-bg dark:text-white text-xs">
                    </div>
                    <div>
                        <label for="motivo" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Motivo</label>
                        <textarea id="motivo" name="motivo" rows="3" minlength="5" maxlength="1000" required
                            class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-darker-bg dark:text-white text-xs">{{ old('motivo') }}</textarea>
                    </div>
                </div>
                <p class="text-xs text-amber-700 dark:text-amber-300">Esta operación ingresa los productos al inventario y registra el importe pendiente de compensar. No anula el comprobante ni genera un reembolso automáticamente.</p>
                <div class="flex justify-end gap-2">
                    <a href="{{ route('ventas.show', $venta) }}" class="px-3.5 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold">Cancelar</a>
                    <button type="submit" class="px-3.5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold shadow-sm">Registrar devolución</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
