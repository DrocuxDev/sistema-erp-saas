<x-app-layout>
    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Configurar Rol: <span class="text-indigo-600 uppercase">{{ $rol->nombre }}</span>
                </h1>
                <p class="text-xs text-gray-500">Ajuste los módulos asignados por defecto para los usuarios con este rol.</p>
            </div>
            <a href="{{ route('roles.index') }}" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-semibold hover:bg-gray-200">
                ← Volver a Roles
            </a>
        </div>

        <form action="{{ route('roles.update', $rol) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-card-bg rounded-xl shadow-xs border border-gray-200 dark:border-gray-700/60 p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nombre del Rol *</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $rol->nombre) }}" required 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-darker-bg text-gray-900 dark:text-white uppercase focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Descripción / Función</label>
                        <input type="text" name="descripcion" value="{{ old('descripcion', $rol->descripcion) }}" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-darker-bg text-gray-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-1" />
                    </div>
                </div>
            </div>

            <!-- Asignación de Módulos -->
            <div class="bg-white dark:bg-card-bg rounded-xl shadow-xs border border-gray-200 dark:border-gray-700/60 p-6 space-y-4">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider border-b border-gray-100 dark:border-gray-700/60 pb-2">
                    Módulos Habilitados para este Rol
                </h2>

                <div class="space-y-4">
                    @foreach($modulos as $categoria => $mods)
                        <div>
                            <span class="text-[11px] font-bold uppercase text-indigo-600 dark:text-indigo-400 tracking-wider">{{ $categoria }}</span>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 mt-2">
                                @foreach($mods as $m)
                                    @php
                                        $marcado = in_array($m->id, old('modulos', $rolModulos));
                                    @endphp
                                    <label class="inline-flex items-center gap-2 p-2.5 rounded-lg bg-gray-50 dark:bg-darker-bg border border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-800 dark:text-gray-200 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                                        <input type="checkbox" name="modulos[]" value="{{ $m->id }}" {{ $marcado ? 'checked' : '' }}
                                               class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                        <span>{{ $m->nombre }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-xs sm:text-sm shadow-sm transition-colors">
                    Actualizar Rol y Módulos
                </button>
                <a href="{{ route('roles.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-lg font-semibold text-xs sm:text-sm transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
