<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Nuevo Ajuste Manual de Inventario
            </h2>
            <a href="{{ route('inventario.kardex') }}" class="text-purple-primary hover:text-purple-light hover:underline font-semibold">
                ← Volver al Kardex
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-card-bg overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-gray-700/50">
                <div class="px-6 py-8">
                    <form action="{{ route('inventario.ajuste.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <label for="producto_id" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                Producto *
                            </label>
                            <select
                                id="producto_id"
                                name="producto_id"
                                required
                                class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                            >
                                <option value="">Seleccionar producto...</option>
                                @foreach ($productos as $prod)
                                    <option value="{{ $prod->id }}" {{ old('producto_id') == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->nombre }} (SKU: {{ $prod->sku }}) — Stock actual: {{ number_format($prod->stock_actual, 2) }} {{ $prod->unidad_medida }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('producto_id')" class="mt-2" />
                        </div>

                        <div>
                            <label for="tipo" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                Tipo de Operación *
                            </label>
                            <select
                                id="tipo"
                                name="tipo"
                                required
                                class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                            >
                                <option value="entrada" {{ old('tipo') === 'entrada' ? 'selected' : '' }}>+ Entrada (Incrementa stock)</option>
                                <option value="salida" {{ old('tipo') === 'salida' ? 'selected' : '' }}>- Salida (Reduce stock)</option>
                                <option value="ajuste" {{ old('tipo', 'ajuste') === 'ajuste' ? 'selected' : '' }}>⚙️ Ajuste de Inventario</option>
                            </select>
                            <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                        </div>

                        <div>
                            <label for="cantidad" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                Cantidad *
                            </label>
                            <input
                                id="cantidad"
                                type="number"
                                step="0.01"
                                min="0.01"
                                name="cantidad"
                                value="{{ old('cantidad') }}"
                                required
                                placeholder="0.00"
                                class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                            />
                            <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
                        </div>

                        <div class="flex gap-4 pt-6">
                            <button
                                type="submit"
                                class="flex-1 rounded-lg bg-purple-primary px-4 py-3 text-base font-semibold text-white hover:bg-purple-light focus:outline-none focus:ring-2 focus:ring-purple-primary focus:ring-offset-2 transition-all"
                            >
                                Registrar Ajuste
                            </button>
                            <a href="{{ route('inventario.kardex') }}" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-3 text-center text-base font-semibold text-gray-900 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
