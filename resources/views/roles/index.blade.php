<x-app-layout>
    <div class="p-4 sm:p-6 space-y-5">
        
        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-2 text-xl font-bold text-gray-800 dark:text-white">
                <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">Usuarios</a>
                <span class="text-gray-400 font-normal">/</span>
                <span class="text-gray-900 dark:text-white">Gestión de Roles</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('roles.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors font-medium text-xs sm:text-sm flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nuevo Rol
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 rounded-xl text-red-800 dark:text-red-300 text-xs sm:text-sm flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Listado de Roles -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($roles as $rol)
                <div class="bg-white dark:bg-card-bg rounded-xl shadow-xs border border-gray-200 dark:border-gray-700/60 p-5 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                {{ $rol->nombre }}
                            </h3>
                            @if($rol->es_sistema)
                                <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-[10px] font-bold rounded-full">
                                    SISTEMA
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $rol->descripcion ?: 'Sin descripción detallada.' }}</p>

                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                            <div class="text-[11px] font-bold uppercase text-gray-400 mb-2">
                                Módulos Concedidos ({{ $rol->modulos->count() }}):
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($rol->modulos as $mod)
                                    <span class="px-2 py-0.5 bg-slate-100 dark:bg-darker-bg text-slate-700 dark:text-slate-300 rounded text-[10px] font-medium">
                                        {{ $mod->nombre }}
                                    </span>
                                @empty
                                    <span class="text-[11px] text-gray-400 italic">Sin módulos asignados</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                        <span class="text-gray-500 font-medium">
                            <strong>{{ $rol->users_count }}</strong> usuarios asociados
                        </span>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('roles.edit', $rol) }}" class="px-2.5 py-1 bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 rounded hover:bg-sky-100 font-semibold transition-colors">
                                Configurar Módulos
                            </a>
                            @if($rol->es_sistema || $rol->slug === 'superadmin')
                                <span class="p-1 text-gray-300 dark:text-gray-600 cursor-not-allowed" title="Rol protegido del sistema (No se puede eliminar)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                            @elseif($rol->users_count > 0)
                                <span class="p-1 text-gray-300 dark:text-gray-600 cursor-not-allowed" title="No se puede eliminar: tiene {{ $rol->users_count }} usuario(s) asignado(s). Primero reasigne o elimine los usuarios asociados.">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </span>
                            @else
                                <form action="{{ route('roles.destroy', $rol) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar el rol \'{{ $rol->nombre }}\'?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 transition-colors" title="Eliminar Rol">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
