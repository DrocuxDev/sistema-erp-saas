<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            Editar Proveedor: {{ $proveedor->nombre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="page-container">
            <div class="bg-white dark:bg-card-bg overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-gray-700/50">
                <div class="px-6 py-6">
                    <form action="{{ route('proveedores.update', $proveedor) }}" method="POST" enctype="multipart/form-data" class="space-y-6" data-dirty-check>
                        @csrf
                        @method('PATCH')

                        <div class="flex items-start gap-6">
                            <x-ui-image-input name="imagen" :preview="$proveedor->imagen ? Storage::url($proveedor->imagen) : null" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nombre" class="form-label">
                                    Nombre Comercial *
                                </label>
                                <input
                                    id="nombre"
                                    type="text"
                                    name="nombre"
                                    value="{{ old('nombre', $proveedor->nombre) }}"
                                    required
                                    autofocus
                                    class="form-input"
                                />
                                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                            </div>

                            <div>
                                <label for="ruc" class="form-label">
                                    RUC *
                                </label>
                                <input
                                    id="ruc"
                                    type="text"
                                    name="ruc"
                                    value="{{ old('ruc', $proveedor->ruc) }}"
                                    required
                                    class="form-input"
                                />
                                <x-input-error :messages="$errors->get('ruc')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <label for="razon_social" class="form-label">
                                Razón Social *
                            </label>
                            <input
                                id="razon_social"
                                type="text"
                                name="razon_social"
                                value="{{ old('razon_social', $proveedor->razon_social) }}"
                                required
                                class="form-input"
                            />
                            <x-input-error :messages="$errors->get('razon_social')" class="mt-2" />
                        </div>

                        <div>
                            <label for="direccion" class="form-label">
                                Dirección *
                            </label>
                            <input
                                id="direccion"
                                type="text"
                                name="direccion"
                                value="{{ old('direccion', $proveedor->direccion) }}"
                                required
                                class="form-input"
                            />
                            <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="telefono" class="form-label">
                                    Teléfono
                                </label>
                                <input
                                    id="telefono"
                                    type="text"
                                    name="telefono"
                                    value="{{ old('telefono', $proveedor->telefono) }}"
                                    class="form-input"
                                />
                                <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                            </div>

                            <div>
                                <label for="email" class="form-label">
                                    Correo electrónico
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email', $proveedor->email) }}"
                                    class="form-input"
                                />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <label for="contacto" class="form-label">
                                Contacto
                            </label>
                            <input
                                id="contacto"
                                type="text"
                                name="contacto"
                                value="{{ old('contacto', $proveedor->contacto) }}"
                                class="form-input"
                            />
                            <x-input-error :messages="$errors->get('contacto')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="condicion_pago" class="form-label">
                                    Condición de Pago *
                                </label>
                                <input
                                    id="condicion_pago"
                                    type="text"
                                    name="condicion_pago"
                                    value="{{ old('condicion_pago', $proveedor->condicion_pago) }}"
                                    required
                                    class="form-input"
                                />
                                <x-input-error :messages="$errors->get('condicion_pago')" class="mt-2" />
                            </div>

                            <div>
                                <label for="estado" class="form-label">
                                    Estado *
                                </label>
                                <select
                                    id="estado"
                                    name="estado"
                                    required
                                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all"
                                >
                                    <option value="activo" {{ old('estado', $proveedor->estado) === 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactivo" {{ old('estado', $proveedor->estado) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                <x-input-error :messages="$errors->get('estado')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex gap-4 pt-6">
                            <button
                                type="submit"
                                class="flex-1 rounded-lg bg-cyan-600 dark:bg-cyan-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700 dark:hover:bg-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 transition-all shadow-2xs"
                            >
                                Guardar Cambios
                            </button>
                            <a href="{{ route('proveedores.index') }}" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-2xs">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
