<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('ordenes-compra-cliente.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
                ← Volver
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Nueva Orden de Compra</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Registrar orden recibida de cliente</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('ordenes-compra-cliente.store') }}" method="POST" enctype="multipart/form-data"
                  class="bg-white dark:bg-card-bg rounded-xl shadow p-6 space-y-5">
                @csrf

                @if($errors->any())
                    <div class="p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-xl text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Cliente *</label>
                    <select name="cliente_id" required
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                        <option value="">Seleccionar cliente...</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }} — {{ $cliente->ruc }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Número de OC *</label>
                    <input type="text" name="numero_oc" value="{{ old('numero_oc') }}" required
                           placeholder="Ej: OC-2026-001"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Fecha de Recepción *</label>
                    <input type="date" name="fecha_recepcion" value="{{ old('fecha_recepcion', date('Y-m-d')) }}" required
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Archivo (PDF o imagen)</label>
                    <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Máximo 10MB. Formatos: PDF, JPG, PNG.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Observaciones</label>
                    <textarea name="observaciones" rows="3" placeholder="Notas adicionales..."
                              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-purple-primary focus:border-transparent resize-none">{{ old('observaciones') }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('ordenes-compra-cliente.index') }}"
                       class="px-4 py-2 bg-gray-200 dark:bg-darker-bg text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-300 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-purple-primary text-white rounded-lg text-sm hover:bg-purple-light transition-colors font-semibold">
                        Guardar Orden
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>