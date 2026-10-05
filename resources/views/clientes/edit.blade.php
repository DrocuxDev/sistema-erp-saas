<x-app-layout>
    <div class="py-6">
        <div class="page-container">
            <!-- Encabezado con navegación -->
            <div class="mb-6">
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <a href="{{ route('clientes.index') }}" class="flex items-center hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Clientes
                    </a>
                    <span class="mx-2">/</span>
                    <span class="text-gray-900 dark:text-white font-medium">Editar</span>
                </div>
            </div>

            <form action="{{ route('clientes.update', $cliente) }}" method="POST" enctype="multipart/form-data" data-dirty-check>
                @csrf
                @method('PATCH')
                
                <div class="bg-white dark:bg-card-bg shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700/50">
                    <!-- Área superior: Nombre -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-start justify-between gap-6">
                            <div class="flex-1">
                                <input
                                    type="text"
                                    name="nombre"
                                    value="{{ old('nombre', $cliente->nombre) }}"
                                    placeholder="Nombre del cliente"
                                    required
                                    autofocus
                                    class="block w-full text-lg font-medium border-0 border-b-2 border-gray-300 dark:border-gray-600 bg-transparent px-0 py-2 text-gray-900 dark:text-white focus:ring-0 focus:border-cyan-500 placeholder-gray-400 dark:placeholder-gray-500"
                                />
                                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                            </div>
                            
                            <x-ui-image-input name="imagen" :preview="$cliente->imagen ? Storage::url($cliente->imagen) : null" />
                        </div>
                    </div>

                    <!-- Pestañas -->
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex -mb-px">
                            <button type="button" class="tab-button active px-6 py-3 text-sm font-medium border-b-2 border-cyan-600 dark:border-cyan-400 text-cyan-600 dark:text-cyan-400" data-tab="general">
                                General
                            </button>
                            <button type="button" class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600" data-tab="contacto">
                                Contacto y Ubicación
                            </button>
                        </nav>
                    </div>

                    <!-- Contenido de pestañas -->
                    <div class="p-6">
                        <!-- Tab: General -->
                        <div id="tab-general" class="tab-content">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <label for="ruc" class="form-label">
                                        RUC
                                    </label>
                                    <input
                                        id="ruc"
                                        type="text"
                                        name="ruc"
                                        value="{{ old('ruc', $cliente->ruc) }}"
                                        placeholder="12345678901"
                                        class="form-input"
                                    />
                                    <x-input-error :messages="$errors->get('ruc')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="estado" class="form-label">
                                        Estado
                                    </label>
                                    <select
                                        id="estado"
                                        name="estado"
                                        class="form-input"
                                    >
                                        <option value="activo" {{ old('estado', $cliente->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                                        <option value="inactivo" {{ old('estado', $cliente->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('estado')" class="mt-1" />
                                </div>

                                <div class="md:col-span-2">
                                    <label for="razon_social" class="form-label">
                                        Razón Social
                                    </label>
                                    <input
                                        id="razon_social"
                                        type="text"
                                        name="razon_social"
                                        value="{{ old('razon_social', $cliente->razon_social) }}"
                                        placeholder="Razón social del cliente"
                                        class="form-input"
                                    />
                                    <x-input-error :messages="$errors->get('razon_social')" class="mt-1" />
                                </div>

                                <div class="md:col-span-2">
                                    <label for="direccion" class="form-label">
                                        Dirección
                                    </label>
                                    <textarea
                                        id="direccion"
                                        name="direccion"
                                        rows="2"
                                        placeholder="Dirección completa del cliente"
                                        class="form-input"
                                    >{{ old('direccion', $cliente->direccion) }}</textarea>
                                    <x-input-error :messages="$errors->get('direccion')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="condicion_pago" class="form-label">
                                        Condición de pago
                                    </label>
                                    <select
                                        id="condicion_pago"
                                        name="condicion_pago"
                                        class="form-input"
                                    >
                                        <option value="Al contado" {{ old('condicion_pago', $cliente->condicion_pago) == 'Al contado' ? 'selected' : '' }}>Al contado</option>
                                        <option value="7 días" {{ old('condicion_pago', $cliente->condicion_pago) == '7 días' ? 'selected' : '' }}>7 días</option>
                                        <option value="15 días" {{ old('condicion_pago', $cliente->condicion_pago) == '15 días' ? 'selected' : '' }}>15 días</option>
                                        <option value="30 días" {{ old('condicion_pago', $cliente->condicion_pago) == '30 días' ? 'selected' : '' }}>30 días</option>
                                        <option value="45 días" {{ old('condicion_pago', $cliente->condicion_pago) == '45 días' ? 'selected' : '' }}>45 días</option>
                                        <option value="60 días" {{ old('condicion_pago', $cliente->condicion_pago) == '60 días' ? 'selected' : '' }}>60 días</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('condicion_pago')" class="mt-1" />
                                </div>
                                <div>
                                    <label for="precio_incluye_igv" class="form-label">Precios del cliente</label>
                                    <select id="precio_incluye_igv" name="precio_incluye_igv" class="form-input">
                                        <option value="0" {{ old('precio_incluye_igv', $cliente->precio_incluye_igv) == 0 ? 'selected' : '' }}>Sin IGV incluido</option>
                                        <option value="1" {{ old('precio_incluye_igv', $cliente->precio_incluye_igv) == 1 ? 'selected' : '' }}>Con IGV incluido</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="pct_igv" class="form-label">IGV del cliente (%)</label>
                                    <input id="pct_igv" type="number" name="pct_igv" min="0" max="100" step="0.01"
                                           value="{{ old('pct_igv', $cliente->pct_igv) }}" class="form-input" required>
                                    <x-input-error :messages="$errors->get('pct_igv')" class="mt-1" />
                                </div>
                                <div>
                                    <label for="pct_retencion" class="form-label">Retención del cliente (%)</label>
                                    <input id="pct_retencion" type="number" name="pct_retencion" min="0" max="100" step="0.01"
                                           value="{{ old('pct_retencion', $cliente->pct_retencion) }}" class="form-input" required>
                                    <x-input-error :messages="$errors->get('pct_retencion')" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Contacto -->
                        <div id="tab-contacto" class="tab-content hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <label for="contacto" class="form-label">
                                        Persona de contacto
                                    </label>
                                    <input
                                        id="contacto"
                                        type="text"
                                        name="contacto"
                                        value="{{ old('contacto', $cliente->contacto) }}"
                                        placeholder="Nombre de la persona de contacto"
                                        class="form-input"
                                    />
                                    <x-input-error :messages="$errors->get('contacto')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="telefono" class="form-label">
                                        Teléfono
                                    </label>
                                    <input
                                        id="telefono"
                                        type="text"
                                        name="telefono"
                                        value="{{ old('telefono', $cliente->telefono) }}"
                                        placeholder="999 999 999"
                                        class="form-input"
                                    />
                                    <x-input-error :messages="$errors->get('telefono')" class="mt-1" />
                                </div>

                                <div class="md:col-span-2">
                                    <label for="email" class="form-label">
                                        Correo electrónico
                                    </label>
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value="{{ old('email', $cliente->email) }}"
                                        placeholder="correo@ejemplo.com"
                                        class="form-input"
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
                            class="btn-primary"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Guardar
                        </button>
                        <a
                            href="{{ route('clientes.index') }}"
                            class="btn-secondary"
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
                    btn.classList.remove('active', 'border-cyan-600', 'dark:border-cyan-400', 'text-cyan-600', 'dark:text-cyan-400');
                    btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
                });
                this.classList.add('active', 'border-cyan-600', 'dark:border-cyan-400', 'text-cyan-600', 'dark:text-cyan-400');
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
