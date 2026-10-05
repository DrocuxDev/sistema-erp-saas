<x-app-layout>
    <div class="page-container py-6 space-y-6">
        
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    Crear Nuevo Rol de Usuario
                </h1>
                <p class="text-xs text-gray-500">Defina el nombre del rol y qué módulos tendrán permitidos por defecto los usuarios con este rol.</p>
            </div>
            <a href="{{ route('roles.index') }}" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-semibold hover:bg-gray-200">
                Volver a Roles
            </a>
        </div>

        <form action="{{ route('roles.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-white dark:bg-card-bg rounded-xl shadow-xs border border-gray-200 dark:border-gray-700/60 p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="form-label">Nombre del Rol *</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" required 
                               placeholder="Ej. FACTURACION / CAJA"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-darker-bg text-gray-900 dark:text-white uppercase focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
                    </div>

                    <div>
                        <label class="form-label">Descripción / Función</label>
                        <input type="text" name="descripcion" value="{{ old('descripcion') }}" 
                               placeholder="Ej. Responsable de emisión de comprobantes"
                               class="form-input">
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-1" />
                    </div>
                </div>
            </div>

            <!-- Asignación de Módulos -->
            <div class="bg-white dark:bg-card-bg rounded-xl shadow-xs border border-gray-200 dark:border-gray-700/60 p-6 space-y-4">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700/60 pb-2">
                    Módulos Habilitados para este Rol
                </h2>

                <div class="space-y-4">
                    @foreach($modulos as $categoria => $mods)
                        <div>
                            <span class="text-xs font-semibold text-cyan-600 dark:text-cyan-400">{{ $categoria }}</span>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 mt-2">
                                @foreach($mods as $m)
                                    <label class="inline-flex items-center gap-2 p-2.5 rounded-lg bg-gray-50 dark:bg-darker-bg border border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-800 dark:text-gray-200 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                                        <input type="checkbox" name="modulos[]" value="{{ $m->id }}" 
                                               class="w-4 h-4 text-cyan-600 rounded border-gray-300 focus:ring-cyan-500">
                                        <span>{{ $m->nombre }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg font-bold text-xs sm:text-sm shadow-sm transition-colors">
                    Crear y Guardar Rol
                </button>
                <a href="{{ route('roles.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-lg font-semibold text-xs sm:text-sm transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
