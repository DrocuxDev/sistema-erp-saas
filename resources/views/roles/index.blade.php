<x-app-layout>
    <div class="p-4 sm:p-6 space-y-5">
        
        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('users.index') }}" class="hover:text-cyan-600 dark:hover:text-cyan-400">Usuarios</a>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Control de Acceso</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Gestión de Roles y Permisos
                </h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('roles.create') }}" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nuevo Rol</span>
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
            <div class="p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-800 dark:text-rose-300 text-xs sm:text-sm flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Tabla Estructurada de Roles (Reemplaza la cuadrícula repetitiva de 3 tarjetas de plantilla) -->
        <div class="bg-white dark:bg-card-bg rounded-2xl border border-gray-200/90 dark:border-gray-700/70 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/80 dark:bg-darker-bg/70 border-b border-gray-200 dark:border-gray-700/60 text-xs font-semibold text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-5 py-3.5">Rol y Descripción</th>
                            <th class="px-5 py-3.5">Módulos Concedidos</th>
                            <th class="px-5 py-3.5 text-center">Usuarios</th>
                            <th class="px-5 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($roles as $rol)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4 align-top max-w-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-gray-900 dark:text-white">
                                            {{ $rol->nombre }}
                                        </span>
                                        @if($rol->es_sistema)
                                            <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800/60 text-amber-700 dark:text-amber-300 text-[10px] font-semibold rounded">
                                                Sistema
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                        {{ $rol->descripcion ?: 'Sin descripción detallada registrada.' }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 align-top">
                                    <div class="text-[11px] font-medium text-gray-400 mb-1.5">
                                        {{ $rol->modulos->count() }} módulos con acceso:
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 max-w-xl">
                                        @forelse($rol->modulos as $mod)
                                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-darker-bg border border-slate-200/80 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 rounded text-[11px] font-mono">
                                                {{ $mod->nombre }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-gray-400 italic">Sin módulos asignados</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-4 align-top text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-mono">
                                        {{ $rol->users_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 align-top text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('roles.edit', $rol) }}" class="btn-outline !min-h-[32px] !py-1 text-xs">
                                            Configurar
                                        </a>
                                        @if($rol->es_sistema || $rol->slug === 'superadmin')
                                            <span class="p-1.5 text-gray-300 dark:text-gray-600 cursor-not-allowed" title="Rol protegido del sistema">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            </span>
                                        @elseif($rol->users_count > 0)
                                            <span class="p-1.5 text-gray-300 dark:text-gray-600 cursor-not-allowed" title="No se puede eliminar: tiene usuarios asignados.">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </span>
                                        @else
                                            <form action="{{ route('roles.destroy', $rol) }}" method="POST" onsubmit="confirmFormSubmit(event, '¿Eliminar este rol?', 'Se eliminará de forma permanente.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 transition-colors" title="Eliminar Rol">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
