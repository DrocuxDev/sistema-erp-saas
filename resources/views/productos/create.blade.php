<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Encabezado con navegación -->
            <div class="mb-6">
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <a href="{{ route('productos.index') }}" class="flex items-center hover:text-purple-primary transition-colors">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Productos
                    </a>
                    <span class="mx-2">/</span>
                    <span class="text-gray-900 dark:text-white font-medium">Nuevo</span>
                </div>
            </div>

            <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="bg-white dark:bg-card-bg shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700/50">
                    <!-- Área superior: Nombre y checkboxes -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-start justify-between gap-6">
                            <div class="flex-1">
                                <input
                                    type="text"
                                    name="nombre"
                                    value="{{ old('nombre') }}"
                                    placeholder="Nombre del producto"
                                    required
                                    autofocus
                                    class="block w-full text-lg font-medium border-0 border-b-2 border-gray-300 dark:border-gray-600 bg-transparent px-0 py-2 text-gray-900 dark:text-white focus:ring-0 focus:border-purple-primary placeholder-gray-400 dark:placeholder-gray-500"
                                />
                                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                            </div>
                            
                            <!-- Imagen del producto -->
                            <div class="flex-shrink-0">
                                <div class="w-32 h-32 bg-gray-100 dark:bg-darker-bg rounded-lg flex items-center justify-center relative group">
                                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Checkboxes superiores -->
                        <div class="flex gap-6 mt-4">
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="puede_venderse"
                                    value="1"
                                    {{ old('puede_venderse', true) ? 'checked' : '' }}
                                    class="rounded border-gray-300 dark:border-gray-600 text-purple-primary focus:ring-purple-primary"
                                />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Puede venderse</span>
                            </label>
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="puede_comprarse"
                                    value="1"
                                    {{ old('puede_comprarse', true) ? 'checked' : '' }}
                                    class="rounded border-gray-300 dark:border-gray-600 text-purple-primary focus:ring-purple-primary"
                                />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Puede comprarse</span>
                            </label>
                        </div>
                    </div>

                    <!-- Pestañas -->
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex -mb-px">
                            <button type="button" class="tab-button active px-6 py-3 text-sm font-medium border-b-2 border-purple-primary text-purple-primary dark:text-purple-primary" data-tab="general">
                                GENERAL
                            </button>
                            <button type="button" class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600" data-tab="inventario">
                                INVENTARIO
                            </button>
                        </nav>
                    </div>

                    <!-- Contenido de pestañas -->
                    <div class="p-6">
                        <!-- Tab: General -->
                        <div id="tab-general" class="tab-content">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        SKU / Código
                                    </label>
                                    <input
                                        id="sku"
                                        type="text"
                                        name="sku"
                                        value="{{ old('sku') }}"
                                        placeholder="Ej: PROD-001"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white shadow-sm focus:border-purple-primary focus:ring-purple-primary sm:text-sm"
                                    />
                                    <x-input-error :messages="$errors->get('sku')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="categoria" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Categoría
                                    </label>
                                    <input
                                        id="categoria"
                                        type="text"
                                        name="categoria"
                                        value="{{ old('categoria') }}"
                                        placeholder="Ej: Materiales, Servicios"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white shadow-sm focus:border-purple-primary focus:ring-purple-primary sm:text-sm"
                                    />
                                    <x-input-error :messages="$errors->get('categoria')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="unidad_medida" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Unidad de medida
                                    </label>
                                    <select
                                        id="unidad_medida"
                                        name="unidad_medida"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white shadow-sm focus:border-purple-primary focus:ring-purple-primary sm:text-sm"
                                    >
                                        <option value="UND" {{ old('unidad_medida', 'UND') == 'UND' ? 'selected' : '' }}>UND - Unidad</option>
                                        <option value="KG" {{ old('unidad_medida') == 'KG' ? 'selected' : '' }}>KG - Kilogramo</option>
                                        <option value="LT" {{ old('unidad_medida') == 'LT' ? 'selected' : '' }}>LT - Litro</option>
                                        <option value="MT" {{ old('unidad_medida') == 'MT' ? 'selected' : '' }}>MT - Metro</option>
                                        <option value="CJ" {{ old('unidad_medida') == 'CJ' ? 'selected' : '' }}>CJ - Caja</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('unidad_medida')" class="mt-1" />
                                </div>

                                <div class="md:col-span-2">
                                    <label for="descripcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Descripción
                                    </label>
                                    <textarea
                                        id="descripcion"
                                        name="descripcion"
                                        rows="3"
                                        placeholder="Descripción detallada del producto..."
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white shadow-sm focus:border-purple-primary focus:ring-purple-primary sm:text-sm"
                                    >{{ old('descripcion') }}</textarea>
                                    <x-input-error :messages="$errors->get('descripcion')" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Inventario -->
                        <div id="tab-inventario" class="tab-content hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <label for="stock_actual" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Stock actual
                                    </label>
                                    <input
                                        id="stock_actual"
                                        type="number"
                                        name="stock_actual"
                                        value="{{ old('stock_actual', 0) }}"
                                        min="0"
                                        step="0.01"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white shadow-sm focus:border-purple-primary focus:ring-purple-primary sm:text-sm"
                                    />
                                    <x-input-error :messages="$errors->get('stock_actual')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="stock_minimo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Stock mínimo
                                    </label>
                                    <input
                                        id="stock_minimo"
                                        type="number"
                                        name="stock_minimo"
                                        value="{{ old('stock_minimo', 0) }}"
                                        min="0"
                                        step="0.01"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white shadow-sm focus:border-purple-primary focus:ring-purple-primary sm:text-sm"
                                    />
                                    <x-input-error :messages="$errors->get('stock_minimo')" class="mt-1" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-darker-bg border-t border-gray-200 dark:border-gray-700 flex justify-start gap-3">
                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 bg-purple-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-light focus:outline-none focus:ring-2 focus:ring-purple-primary focus:ring-offset-2 transition-all"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Guardar
                        </button>
                        <a
                            href="{{ route('productos.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all"
                        >
                            Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Manejo de pestañas
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.dataset.tab;
                
                // Actualizar botones
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('active', 'border-purple-primary', 'text-purple-primary');
                    btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
                });
                this.classList.add('active', 'border-purple-primary', 'text-purple-primary');
                this.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
                
                // Mostrar contenido
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                document.getElementById('tab-' + tabName).classList.remove('hidden');
            });
        });
    </script>
    @endpush
</x-app-layout>
