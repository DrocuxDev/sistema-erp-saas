<x-app-layout>
    <div class="p-4 sm:p-6 space-y-5">

        <!-- Encabezado de Página -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-2 text-xl font-bold text-gray-800 dark:text-white">
                <span class="text-gray-900 dark:text-white">Usuarios</span>
                <span class="text-gray-400 font-normal">/ Listado</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('roles.index') }}" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg transition-colors font-medium text-xs sm:text-sm flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Gestión de Roles
                </a>
                <a href="{{ route('users.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors font-medium text-xs sm:text-sm flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    + Agregar
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 rounded-xl text-red-800 dark:text-red-300 text-xs sm:text-sm flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Barra de Filtros Homologada a la Referencia -->
        <form method="GET" action="{{ route('users.index') }}" class="bg-white dark:bg-card-bg rounded-xl shadow-xs border border-gray-200 dark:border-gray-700/60 p-4">
            <div class="flex flex-wrap items-center gap-4 text-xs sm:text-sm">
                
                {{-- Filtro Usuario --}}
                <div class="flex items-center gap-2">
                    <label class="font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">Usuario</label>
                    <input type="text" name="usuario" value="{{ request('usuario') }}" 
                           placeholder="" 
                           class="w-40 sm:w-48 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-darker-bg text-gray-900 dark:text-white text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none">
                </div>

                {{-- Filtro Rol --}}
                <div class="flex items-center gap-2">
                    <label class="font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">Rol</label>
                    <select name="rol_id" class="w-40 sm:w-48 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-darker-bg text-gray-900 dark:text-white text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        <option value="TODOS">TODOS</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ request('rol_id') == $r->id ? 'selected' : '' }}>
                                {{ strtoupper($r->nombre) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro Punto de Emisión --}}
                <div class="flex items-center gap-2">
                    <label class="font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">Punto.Emisión</label>
                    <select name="punto_emision" class="w-40 sm:w-48 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-darker-bg text-gray-900 dark:text-white text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        <option value="">TODOS</option>
                        @foreach($puntosEmision as $pe)
                            <option value="{{ $pe }}" {{ request('punto_emision') == $pe ? 'selected' : '' }}>
                                {{ strtoupper($pe) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Checkbox Solo Activos --}}
                <div class="flex items-center gap-2">
                    <label class="inline-flex items-center gap-1.5 cursor-pointer select-none font-semibold text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="solo_activos" value="1" {{ request()->has('solo_activos') || !request()->has('usuario') ? 'checked' : '' }} 
                               class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                        <span>Solo Activos</span>
                    </label>
                </div>

                {{-- Botón Buscar --}}
                <div class="ml-auto flex items-center gap-2">
                    <button type="submit" class="px-4 py-1.5 bg-sky-100 hover:bg-sky-200 text-sky-800 dark:bg-sky-950 dark:text-sky-300 border border-sky-300 dark:border-sky-800 rounded font-semibold text-xs transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Buscar
                    </button>
                    @if(request()->anyFilled(['usuario', 'rol_id', 'punto_emision']))
                        <a href="{{ route('users.index') }}" class="px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400">Limpiar</a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Contador de Registros -->
        <div class="flex justify-end text-xs text-gray-500 font-mono">
            {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} / {{ $users->total() }}
        </div>

        <!-- Tabla Estilizada Según la Referencia Corporativa -->
        <div class="bg-white dark:bg-card-bg rounded-xl shadow-xs border border-gray-200 dark:border-gray-700/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                    <thead class="bg-gray-100 dark:bg-darker-bg text-gray-600 dark:text-gray-300 font-medium">
                        <tr>
                            <th class="px-4 py-2.5 text-left">Usuario</th>
                            <th class="px-4 py-2.5 text-left">Rol</th>
                            <th class="px-4 py-2.5 text-left">Empleado</th>
                            <th class="px-4 py-2.5 text-left">Correo electrónico</th>
                            <th class="px-4 py-2.5 text-left">Punto de emisión</th>
                            <th class="px-4 py-2.5 text-center">Activo</th>
                            <th class="px-4 py-2.5 text-center w-24">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($users as $user)
                            @php
                                $esSuper = $user->isSuperAdmin();
                                $bgFila = $esSuper 
                                    ? 'bg-amber-50/80 dark:bg-amber-950/20 hover:bg-amber-100/60 font-medium' 
                                    : 'hover:bg-gray-50 dark:hover:bg-darker-bg/50';
                            @endphp
                            <tr class="{{ $bgFila }} transition-colors">
                                
                                {{-- Columna Usuario --}}
                                <td class="px-4 py-3 whitespace-nowrap text-gray-800 dark:text-gray-200">
                                    <div class="flex items-center gap-1.5">
                                        @if($esSuper)
                                            <span title="SuperAdmin Base - Inmutable" class="text-amber-500">👑</span>
                                        @endif
                                        <span class="font-mono font-semibold">{{ $user->username ?: explode('@', $user->email)[0] }}</span>
                                    </div>
                                </td>

                                {{-- Columna Rol --}}
                                <td class="px-4 py-3 whitespace-nowrap uppercase font-bold {{ $esSuper ? 'text-amber-700 dark:text-amber-400' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $user->rolPersonalizado?->nombre ?: ($esSuper ? 'SUPERADMIN' : strtoupper($user->rol)) }}
                                </td>

                                {{-- Columna Empleado (Nombre) --}}
                                <td class="px-4 py-3 whitespace-nowrap uppercase text-gray-900 dark:text-white font-semibold">
                                    {{ $user->name }}
                                </td>

                                {{-- Columna Email --}}
                                <td class="px-4 py-3 whitespace-nowrap uppercase text-gray-600 dark:text-gray-400 font-mono">
                                    {{ $user->email }}
                                </td>

                                {{-- Columna Punto de Emisión --}}
                                <td class="px-4 py-3 whitespace-nowrap uppercase text-gray-700 dark:text-gray-300">
                                    {{ $user->punto_emision ?: 'OFICINA' }}
                                </td>

                                {{-- Columna Activo --}}
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    @if($esSuper)
                                        <span class="inline-flex text-emerald-600 dark:text-emerald-400" title="SuperAdmin Activo Permanente">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                        </span>
                                    @else
                                        <form action="{{ route('users.toggle-activo', $user) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="focus:outline-none transition-transform active:scale-95" title="Click para alternar estado">
                                                @if($user->activo)
                                                    <span class="text-emerald-600 dark:text-emerald-400 hover:opacity-80">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                                    </span>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500 hover:text-gray-600">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </span>
                                                @endif
                                            </button>
                                        </form>
                                    @endif
                                </td>

                                {{-- Columna Acciones --}}
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- Botón Editar --}}
                                        @if(!$esSuper || auth()->user()->isSuperAdmin())
                                            <x-action-button icon="edit" color="amber" label="Editar usuario y permisos" :href="route('users.edit', $user)" />
                                        @endif

                                        {{-- Botón Eliminar (Bloqueado para SuperAdmin) --}}
                                        @if(!$esSuper && $user->id !== auth()->id() && auth()->user()->puedeGestionarA($user))
                                            <x-action-button icon="delete" color="rose" label="Eliminar usuario" onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')" />
                                            <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @elseif($esSuper)
                                            <span class="text-gray-300 dark:text-gray-600 p-1 cursor-not-allowed" title="SuperAdmin Base protegido contra eliminación">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-xs text-gray-500 dark:text-gray-400">
                                    No se encontraron usuarios que coincidan con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($users->hasPages())
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <script>
        async function confirmDelete(userId, userName) {
            await submitAfterConfirm('delete-form-' + userId, '¿Eliminar este usuario?', `Se eliminará permanentemente a "${userName}".`);
        }
    </script>
</x-app-layout>
