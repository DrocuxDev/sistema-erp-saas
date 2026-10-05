<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="page-title">
                Nuevo Ajuste Manual de Inventario
            </h2>
            <a href="{{ route('inventario.kardex') }}" class="text-cyan-600 dark:text-cyan-400 hover:underline font-semibold">
                Volver al Kardex
            </a>
        </div>
    </x-slot>

    <div class="page-container py-6 sm:py-8">
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('inventario.ajuste.store') }}" method="POST">
                        @csrf

                        <x-ui-card>
                        <div class="p-6 space-y-6">
                        <div>
                            <label for="producto_id" class="ui-label">
                                Producto *
                            </label>
                            <select
                                id="producto_id"
                                name="producto_id"
                                required
                                class="ui-input"
                            >
                                <option value="">Seleccionar producto...</option>
                                @foreach ($productos as $prod)
                                    <option value="{{ $prod->id }}" {{ old('producto_id') == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->nombre }} (SKU: {{ $prod->sku }}) — Existencias actuales: {{ number_format($prod->stock_actual, 2) }} {{ $prod->unidad_medida }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('producto_id')" class="mt-2" />
                        </div>

                        <div class="form-grid-2">
                            <div>
                            <label for="tipo" class="ui-label">
                                Tipo de Operación *
                            </label>
                            <select
                                id="tipo"
                                name="tipo"
                                required
                                class="ui-input"
                            >
                                <option value="entrada" {{ old('tipo') === 'entrada' ? 'selected' : '' }}>+ Entrada (Incrementa stock)</option>
                                <option value="salida" {{ old('tipo') === 'salida' ? 'selected' : '' }}>- Salida (Reduce stock)</option>
                                <option value="ajuste" {{ old('tipo', 'ajuste') === 'ajuste' ? 'selected' : '' }}>⚙️ Ajuste de Inventario</option>
                            </select>
                            <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                            </div>

                            <div>
                            <label for="cantidad" class="ui-label">
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
                                class="ui-input"
                            />
                            <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3 pt-2 border-t border-gray-200 dark:border-white/10">
                            <button
                                type="submit"
                                class="btn-primary flex-1"
                            >
                                Registrar Ajuste
                            </button>
                            <a href="{{ route('inventario.kardex') }}" class="btn-outline flex-1">
                                Cancelar
                            </a>
                        </div>
                        </div>
                        </x-ui-card>
                    </form>
    </div>
</x-app-layout>
