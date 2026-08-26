<x-app-layout>
    <div class="p-4 sm:p-6 max-w-4xl mx-auto space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('ordenes-compra-cliente.index') }}" class="hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">O/C de Clientes</a>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Nueva O/C</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800/60">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </span>
                    Registrar Orden de Compra de Cliente
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ingreso de orden formal enviada por un cliente (para cotizar o facturar).</p>
            </div>
            <a href="{{ route('ordenes-compra-cliente.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold transition-colors">
                ← Volver
            </a>
        </div>

        @if($errors->any())
            <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-800 dark:text-rose-300 text-xs space-y-1">
                <p class="font-bold">Por favor corrija los siguientes errores:</p>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('ordenes-compra-cliente.store') }}" method="POST" enctype="multipart/form-data"
              class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1">
                        Cliente Emisor <span class="text-rose-500">*</span>
                    </label>
                    <select name="cliente_id" required
                            class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                        <option value="">Seleccione el cliente...</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->razon_social ?: $cliente->nombre }} — RUC: {{ $cliente->ruc ?: '—' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1">
                        Número / Código de O/C del Cliente <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="numero_oc" value="{{ old('numero_oc') }}" required placeholder="Ej: OC-2026-0098"
                           class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1">
                        Fecha de Recepción <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="fecha_recepcion" value="{{ old('fecha_recepcion', date('Y-m-d')) }}" required
                           class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1">
                        Archivo Adjunto de la O/C (PDF o Imagen)
                    </label>
                    <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 file:mr-4 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-cyan-50 file:text-cyan-700 dark:file:bg-cyan-950 dark:file:text-cyan-300 hover:file:bg-cyan-100 transition-all">
                    <p class="text-[11px] text-gray-400 mt-1">Formatos soportados: PDF, JPG, PNG. Tamaño máximo 10MB.</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1">
                        Observaciones / Requerimientos Especiales
                    </label>
                    <textarea name="observaciones" rows="3" placeholder="Detalles de entrega, forma de pago acordada, etc..."
                              class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all resize-none">{{ old('observaciones') }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                <a href="{{ route('ordenes-compra-cliente.index') }}"
                   class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold shadow-md shadow-cyan-600/20 transition-all">
                    Guardar O/C de Cliente
                </button>
            </div>
        </form>

    </div>
</x-app-layout>