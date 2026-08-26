<x-app-layout>
    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Registrar Nuevo Usuario
                </h1>
                <p class="text-xs text-gray-500">Cree credenciales de acceso, asigne roles y configure permisos de módulos.</p>
            </div>
            <a href="{{ route('users.index') }}" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-semibold hover:bg-gray-200">
                ← Volver al Listado
            </a>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- 1. Datos Personales y Credenciales -->
            <div class="bg-white dark:bg-card-bg rounded-xl shadow-xs border border-gray-200 dark:border-gray-700/60 p-6 space-y-4">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider border-b border-gray-100 dark:border-gray-700/60 pb-2">
                    1. Información del Usuario & Credenciales
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nombre Completo (Empleado) *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required 
                               placeholder="Ej. Juan Pérez Ramos"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-darker-bg text-gray-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Correo Electrónico (Login) *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required 
                               placeholder="usuario@empresa.com"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-darker-bg text-gray-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Alias / Nombre de Usuario</label>
                        <input type="text" name="username" value="{{ old('username') }}" 
                               placeholder="Ej. JPEREZ@EMPRESA"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-darker-bg text-gray-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        <x-input-error :messages="$errors->get('username')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Punto de Emisión *</label>
                        <input type="text" name="punto_emision" value="{{ old('punto_emision', 'OFICINA') }}" required 
                               placeholder="Ej. OFICINA, CAJA CHICA, TIENDA 1"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-darker-bg text-gray-900 dark:text-white uppercase focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        <x-input-error :messages="$errors->get('punto_emision')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Contraseña *</label>
                        <input type="password" name="password" required 
                               placeholder="Mínimo 8 caracteres"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-darker-bg text-gray-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Confirmar Contraseña *</label>
                        <input type="password" name="password_confirmation" required 
                               placeholder="Repetir contraseña"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-darker-bg text-gray-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="pt-2 flex items-center gap-6">
                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="activo" value="1" checked class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500">
                        <span>Usuario Activo (Permitir inicio de sesión)</span>
                    </label>

                    @if(auth()->user()->isSuperAdmin())
                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-3 py-1.5 rounded-lg border border-amber-200 dark:border-amber-800">
                            <input type="checkbox" name="is_superadmin" value="1" class="w-4 h-4 text-amber-600 rounded border-amber-300 focus:ring-amber-500">
                            <span>👑 Conceder rango SuperAdmin (Acceso total e inmutable)</span>
                        </label>
                    @endif
                </div>
            </div>

            <!-- 2. Asignación de Rol Principal -->
            <div class="bg-white dark:bg-card-bg rounded-xl shadow-xs border border-gray-200 dark:border-gray-700/60 p-6 space-y-4">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider border-b border-gray-100 dark:border-gray-700/60 pb-2">
                    2. Asignación de Rol
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($roles as $r)
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-500 cursor-pointer transition-all bg-gray-50/50 dark:bg-darker-bg/50">
                            <input type="radio" name="rol_id" value="{{ $r->id }}" {{ old('rol_id') == $r->id || $loop->first ? 'checked' : '' }}
                                   class="mt-0.5 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div>
                                <div class="text-xs font-bold text-gray-900 dark:text-white uppercase">{{ $r->nombre }}</div>
                                <div class="text-[11px] text-gray-500 leading-tight mt-0.5">{{ $r->descripcion ?: 'Acceso estándar' }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- 3. Asignación Granular de Módulos (Opcional) -->
            <div class="bg-white dark:bg-card-bg rounded-xl shadow-xs border border-gray-200 dark:border-gray-700/60 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-2">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                            3. Permisos Personalizados por Módulo (Excepciones)
                        </h2>
                        <p class="text-[11px] text-gray-500">Marque o desmarque módulos para añadir o restringir accesos directos independientemente de su rol.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach($modulos as $categoria => $mods)
                        <div>
                            <span class="text-[11px] font-bold uppercase text-blue-600 dark:text-blue-400 tracking-wider">{{ $categoria }}</span>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 mt-2">
                                @foreach($mods as $m)
                                    <label class="inline-flex items-center gap-2 p-2 rounded bg-gray-50 dark:bg-darker-bg border border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-800 dark:text-gray-200 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                                        <input type="checkbox" name="modulos[]" value="{{ $m->id }}" 
                                               class="w-3.5 h-3.5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                        <span>{{ $m->nombre }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs sm:text-sm shadow-sm transition-colors">
                    Guardar y Registrar Usuario
                </button>
                <a href="{{ route('users.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-lg font-semibold text-xs sm:text-sm transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
