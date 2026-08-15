<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('ordenes-compra-cliente.show', $orden) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
                ← Volver
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Editar OC: {{ $orden->numero_oc }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Modificar datos o anular la orden</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('ordenes-compra-cliente.update', $orden) }}" method="POST" enctype="multipart/form-data"
                  class="bg-white dark:bg-card-bg rounded-xl shadow p-6 space-y-5">
                @csrf
                @method('PUT')

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
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ old('cliente_id', $orden->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }} — {{ $cliente->ruc }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Número de OC *</label>
                    <input type="text" name="numero_oc" value="{{ old('numero_oc', $orden->numero_oc) }}" required
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Fecha de Recepción *</label>
                    <input type="date" name="fecha_recepcion" value="{{ old('fecha_recepcion', $orden->fecha_recepcion?->format('Y-m-d')) }}" required
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Estado *</label>
                    <select name="estado" required
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                        <option value="pendiente" {{ old('estado', $orden->estado) === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="procesada" {{ old('estado', $orden->estado) === 'procesada' ? 'selected' : '' }}>Procesada</option>
                        <option value="anulada"   {{ old('estado', $orden->estado) === 'anulada'   ? 'selected' : '' }}>Anulada</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Archivo (PDF o imagen)</label>
                    @if($orden->archivo)
                        <div class="mb-2 flex items-center gap-3">
                            <a href="{{ Storage::url($orden->archivo) }}" target="_blank"
                               class="text-sm text-purple-primary hover:underline">Ver archivo actual</a>
                            <span class="text-xs text-gray-400">— Subir uno nuevo lo reemplazará</span>
                        </div>
                    @endif
                    <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Máximo 10MB. Formatos: PDF, JPG, PNG.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Observaciones</label>
                    <textarea name="observaciones" rows="3"
                              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-purple-primary focus:border-transparent resize-none">{{ old('observaciones', $orden->observaciones) }}</textarea>
                </div>

                <div class="flex justify-between items-center pt-2">
                    <div class="flex gap-3">
                        <a href="{{ route('ordenes-compra-cliente.show', $orden) }}"
                           class="px-4 py-2 bg-gray-200 dark:bg-darker-bg text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-300 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="px-6 py-2 bg-purple-primary text-white rounded-lg text-sm hover:bg-purple-light transition-colors font-semibold">
                            Guardar cambios
                        </button>
                    </div>
                </div>
            </form>

            <form action="{{ route('ordenes-compra-cliente.destroy', $orden) }}" method="POST" class="mt-4"
                  onsubmit="return confirm('¿Eliminar esta orden? Esta acción no se puede deshacer.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg text-sm hover:bg-red-200 transition-colors">
                    Eliminar
                </button>
            </form>
        </div>
    </div>
</x-app-layout>