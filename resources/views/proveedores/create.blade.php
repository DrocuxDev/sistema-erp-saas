<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Encabezado con navegación -->
            <div class="mb-6">
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <a href="{{ route('proveedores.index') }}" class="flex items-center hover:text-purple-primary transition-colors">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Proveedores
                    </a>
                    <span class="mx-2">/</span>
                    <span class="text-gray-900 dark:text-white font-medium">Nuevo</span>
                </div>
            </div>

            <form action="{{ route('proveedores.store') }}" method="POST">
                @csrf
                
                <div class="bg-white dark:bg-card-bg shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700/50">
                    <!-- Área superior: Nombre -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-start justify-between gap-6">
                            <div class="flex-1">
                                <input
                                    type="text"
                                    name="nombre"
                                    value="{{ old('nombre') }}"
                                    placeholder="Nombre del proveedor"
                                    required
                                    autofocus
                                    class="block w-full text-lg font-medium border-0 border-b-2 border-gray-300 dark:border-gray-600 bg-transparent px-0 py-2 text-gray-900 dark:text-white focus:ring-0 focus:border-purple-primary placeholder-gray-400 dark:placeholder-gray-500"
                                />
                                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                            </div>
                            
                            <!-- Icono del proveedor -->
                            <div class="flex-shrink-0">
                                <div class="w-32 h-32 bg-gray-100 dark:bg-darker-bg rounded-lg flex items-center justify-center relative group">
                                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pestañas -->
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex -mb-px">
                            <button type="button" class="tab-button active px-6 py-3 text-sm font-medium border-b-2 border-purple-primary text-purple-primary dark:text-purple-primary" data-tab="general">
                                GENERAL
                            </button>
                            <button type="button" class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600" data-tab="contacto">
                                CONTACTO
                            </button>
                        </nav>
                    </div>

                    <!-- Contenido de pestañas -->
                    <div class="p-6">
                        <!-- Tab: General -->
                        <div id="tab-general" class="tab-content">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <label for="ruc" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        RUC
                                    </label>
                                    <input
                                        id="ruc"
                                        type="text"
                                        name="ruc"
                                        value="{{ old('ruc') }}"
                                        placeholder="12345678901"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white shadow-sm focus:border-purple-primary focus:ring-purple-primary sm:text-sm"
                                    />
                                    <x-input-error :messages="$errors->get('ruc')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="estado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Estado
                                    </label>
                                    <select
                                        id="estado"
                                        name="estado"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white shadow-sm focus:border-purple-primary focus:ring-purple-primary sm:text-sm"
                                    >
                                        <option value="activo" {{ old('estado', 'activo') == 'activo' ? 'selected' : '' }}>Activo</option>
                                        <option value="inactivo" {{ old('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('estado')" class="mt-1" />
                                </div>

                                <div class="md:col-span-2">
                                    <label for="razon_social" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Razón Social
                                    </label>
                                    <input
                                        id="razon_social"
                                        type="text"
                                        name="razon_social"
                                        value="{{ old('razon_social') }}"
                                        placeholder="Razón social del proveedor"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white shadow-sm focus:border-purple-primary focus:ring-purple-primary sm:text-sm"
                                    />
                                    <x-input-error :messages="$errors->get('razon_social')" class="mt-1" />
                                </div>

                                <div class="md:col-span-2">
                                    <label for="direccion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Dirección
                                    </label>
                                    <textarea
                                        id="direccion"
                                        name="direccion"
                                        rows="2"
                                        placeholder="Dirección completa del proveedor"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white shadow-sm focus:border-purple-primary focus:ring-purple-primary sm:text-sm"
                                    >{{ old('direccion') }}</textarea>
                                    <x-input-error :messages="$errors->get('direccion')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="condicion_pago" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Condición de pago
                                    </label>
                                    <select
                                        id="condicion_pago"
                                        name="condicion_pago"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white shadow-sm focus:border-purple-primary focus:ring-purple-primary sm:text-sm"
                                    >
                                        <option value="Al contado" {{ old('condicion_pago', 'Al contado') == 'Al contado' ? 'selected' : '' }}>Al contado</option>
                                        <option value="7 días" {{ old('condicion_pago') == '7 días' ? 'selected' : '' }}>7 días</option>
                                        <option value="15 días" {{ old('condicion_pago') == '15 días' ? 'selected' : '' }}>15 días</option>
                                        <option value="30 días" {{ old('condicion_pago') == '30 días' ? 'selected' : '' }}>30 días</option>
                                        <option value="45 días" {{ old('condicion_pago') == '45 días' ? 'selected' : '' }}>45 días</option>
                                        <option value="60 días" {{ old('condicion_pago') == '60 días' ? 'selected' : '' }}>60 días</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('condicion_pago')" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Contacto -->
                        <div id="tab-contacto" class="tab-content hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <label for="contacto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Persona de contacto
                                    </label>
                                    <input
                                        id="contacto"
                                        type="text"
                                        name="contacto"
                                        value="{{ old('contacto') }}"
                                        placeholder="Nombre de la persona de contacto"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white shadow-sm focus:border-purple-primary focus:ring-purple-primary sm:text-sm"
                                    />
                                    <x-input-error :messages="$errors->get('contacto')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Teléfono
                                    </label>
                                    <input
                                        id="telefono"
                                        type="text"
                                        name="telefono"
                                        value="{{ old('telefono') }}"
                                        placeholder="999 999 999"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white shadow-sm focus:border-purple-primary focus:ring-purple-primary sm:text-sm"
                                    />
                                    <x-input-error :messages="$errors->get('telefono')" class="mt-1" />
                                </div>

                                <div class="md:col-span-2">
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Email
                                    </label>
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        placeholder="correo@ejemplo.com"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white shadow-sm focus:border-purple-primary focus:ring-purple-primary sm:text-sm"
                                    />
                                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
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
                            href="{{ route('proveedores.index') }}"
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
