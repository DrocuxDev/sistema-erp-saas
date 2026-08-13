<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
            {{ __('Configuración del Sistema') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-card-bg overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700/50">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('configuracion.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="nombre" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                Nombre de Configuración *
                            </label>
                            <input
                                id="nombre"
                                type="text"
                                name="nombre"
                                value="{{ old('nombre', $configuracion->nombre) }}"
                                required
                                class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                            />
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="pct_igv" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                    IGV (%) *
                                </label>
                                <input
                                    id="pct_igv"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    name="pct_igv"
                                    value="{{ old('pct_igv', $configuracion->pct_igv) }}"
                                    required
                                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                                />
                                <x-input-error :messages="$errors->get('pct_igv')" class="mt-2" />
                            </div>

                            <div>
                                <label for="pct_retencion" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                    Retención (%) *
                                </label>
                                <input
                                    id="pct_retencion"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    name="pct_retencion"
                                    value="{{ old('pct_retencion', $configuracion->pct_retencion) }}"
                                    required
                                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                                />
                                <x-input-error :messages="$errors->get('pct_retencion')" class="mt-2" />
                            </div>

                            <div>
                                <label for="pct_comision_venta" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                    Comisión Venta (%) *
                                </label>
                                <input
                                    id="pct_comision_venta"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    name="pct_comision_venta"
                                    value="{{ old('pct_comision_venta', $configuracion->pct_comision_venta) }}"
                                    required
                                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                                />
                                <x-input-error :messages="$errors->get('pct_comision_venta')" class="mt-2" />
                            </div>

                            <div>
                                <label for="pct_comision_ganancia" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                    Comisión Ganancia (%) *
                                </label>
                                <input
                                    id="pct_comision_ganancia"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    name="pct_comision_ganancia"
                                    value="{{ old('pct_comision_ganancia', $configuracion->pct_comision_ganancia) }}"
                                    required
                                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                                />
                                <x-input-error :messages="$errors->get('pct_comision_ganancia')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex gap-4 pt-6">
                            <button
                                type="submit"
                                class="flex-1 rounded-lg bg-purple-primary px-4 py-3 text-base font-semibold text-white hover:bg-purple-light focus:outline-none focus:ring-2 focus:ring-purple-primary focus:ring-offset-2 transition-all"
                            >
                                Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
