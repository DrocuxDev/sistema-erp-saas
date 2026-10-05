<x-app-layout>
    <div class="page-container py-4 sm:py-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <span>{{ $origen }}</span><span>/</span>
                    <a href="{{ $rutaVolver }}" class="hover:text-cyan-600 dark:hover:text-cyan-400">{{ $documento }}</a><span>/</span>
                    <span class="text-gray-900 dark:text-white">{{ $titulo }}</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $titulo }}</h1>
                <p class="text-xs text-gray-500 mt-1">{{ $tercero }} · {{ $documento }}</p>
            </div>
            <a href="{{ $rutaVolver }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold">Volver</a>
        </div>

        @if($errors->any())
            <div class="p-4 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-2xl text-xs">
                <ul class="list-disc pl-4 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ $rutaStore }}" method="POST" class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 overflow-hidden">
            @csrf
            <input type="hidden" name="tipo" value="{{ $tipo }}">
            <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', $idempotencyKey) }}">
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-5 text-white flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold">{{ $titulo }}</h2>
                    <p class="text-xs text-slate-300 mt-1">Fecha del documento: {{ $fechaOrigen->format('d/m/Y') }}</p>
                </div>
                <div class="text-right"><p class="text-[10px] text-slate-400">Máximo disponible</p><p class="text-lg font-bold font-mono text-emerald-400">S/ {{ number_format($limite / 100, 2) }}</p></div>
            </div>
            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="fecha" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Fecha</label>
                        <input id="fecha" name="fecha" type="date" value="{{ old('fecha', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-darker-bg dark:text-white text-xs">
                    </div>
                    <div>
                        <label for="monto" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Importe S/</label>
                        <input id="monto" name="monto" type="number" min="0.01" max="{{ number_format($limite / 100, 2, '.', '') }}" step="0.01" value="{{ old('monto', number_format($limite / 100, 2, '.', '')) }}" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-darker-bg dark:text-white text-xs font-mono">
                    </div>
                    <div>
                        <label for="medio" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Medio</label>
                        <select id="medio" name="medio" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-darker-bg dark:text-white text-xs">
                            @foreach(['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'deposito' => 'Depósito', 'tarjeta' => 'Tarjeta', 'otro' => 'Otro'] as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected(old('medio') === $valor)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="referencia" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Referencia / número de operación</label>
                        <input id="referencia" name="referencia" type="text" maxlength="100" value="{{ old('referencia') }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-darker-bg dark:text-white text-xs">
                        <p class="text-[11px] text-gray-500 mt-1">Obligatoria salvo para efectivo.</p>
                    </div>
                </div>
                <div>
                    <label for="observacion" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Observación</label>
                    <textarea id="observacion" name="observacion" rows="3" maxlength="1000" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-darker-bg dark:text-white text-xs">{{ old('observacion') }}</textarea>
                </div>
                <p class="text-xs text-gray-500">Este registro documenta una operación manual. Verifique el movimiento real en caja o banco antes de guardarlo.</p>
                <div class="flex justify-end gap-2">
                    <a href="{{ $rutaVolver }}" class="px-3.5 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold">Cancelar</a>
                    <button type="submit" class="px-3.5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold shadow-sm">{{ $titulo }}</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
