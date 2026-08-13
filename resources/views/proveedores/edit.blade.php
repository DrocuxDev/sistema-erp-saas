<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            Editar Proveedor: {{ $proveedor->nombre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-card-bg overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-gray-700/50">
                <div class="px-6 py-8">
                    <form action="{{ route('proveedores.update', $proveedor) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nombre" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                    Nombre Comercial *
                                </label>
                                <input
                                    id="nombre"
                                    type="text"
                                    name="nombre"
                                    value="{{ old('nombre', $proveedor->nombre) }}"
                                    required
                                    autofocus
                                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                                />
                                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                            </div>

                            <div>
                                <label for="ruc" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                    RUC *
                                </label>
                                <input
                                    id="ruc"
                                    type="text"
                                    name="ruc"
                                    value="{{ old('ruc', $proveedor->ruc) }}"
                                    required
                                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                                />
                                <x-input-error :messages="$errors->get('ruc')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <label for="razon_social" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                Razón Social *
                            </label>
                            <input
                                id="razon_social"
                                type="text"
                                name="razon_social"
                                value="{{ old('razon_social', $proveedor->razon_social) }}"
                                required
                                class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                            />
                            <x-input-error :messages="$errors->get('razon_social')" class="mt-2" />
                        </div>

                        <div>
                            <label for="direccion" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                Dirección *
                            </label>
                            <input
                                id="direccion"
                                type="text"
                                name="direccion"
                                value="{{ old('direccion', $proveedor->direccion) }}"
                                required
                                class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                            />
                            <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="telefono" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                    Teléfono
                                </label>
                                <input
                                    id="telefono"
                                    type="text"
                                    name="telefono"
                                    value="{{ old('telefono', $proveedor->telefono) }}"
                                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                                />
                                <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                    Email
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email', $proveedor->email) }}"
                                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                                />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <label for="contacto" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                Contacto
                            </label>
                            <input
                                id="contacto"
                                type="text"
                                name="contacto"
                                value="{{ old('contacto', $proveedor->contacto) }}"
                                class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                            />
                            <x-input-error :messages="$errors->get('contacto')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="condicion_pago" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                    Condición de Pago *
                                </label>
                                <input
                                    id="condicion_pago"
                                    type="text"
                                    name="condicion_pago"
                                    value="{{ old('condicion_pago', $proveedor->condicion_pago) }}"
                                    required
                                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
                                />
                                <x-input-error :messages="$errors->get('condicion_pago')" class="mt-2" />
                            </div>

                            <div>
                                <label for="estado" class="block text-sm font-semibold text-gray-900 dark:text-gray-200 mb-2">
                                    Estado *
                                </label>
                                <select
                                    id="estado"
                                    name="estado"
                                    required
                                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-4 py-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20 transition-all"
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
                                class="flex-1 rounded-lg bg-purple-primary px-4 py-3 text-base font-semibold text-white hover:bg-purple-light focus:outline-none focus:ring-2 focus:ring-purple-primary focus:ring-offset-2 transition-all"
                            >
                                Guardar Cambios
                            </button>
                            <a href="{{ route('proveedores.index') }}" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-3 text-center text-base font-semibold text-gray-900 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
