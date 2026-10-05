<nav class="bg-white dark:bg-darker-bg border-b border-gray-200 dark:border-gray-700/50 transition-colors duration-200 sticky top-0 z-30">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center gap-3 sm:gap-6">
            
            {{-- Left Side: controles del menú y buscador rápido de módulos --}}
            <div class="flex items-center gap-3 flex-1 max-w-xl">
                {{-- Móvil y tablet: abre el panel superpuesto. --}}
                <button type="button" @click="sidebarOpen = !sidebarOpen"
                        :aria-expanded="sidebarOpen.toString()" aria-controls="main-sidebar"
                        aria-label="Alternar menú lateral" title="Alternar menú lateral"
                        class="flex lg:hidden p-2 -ml-2 text-gray-500 dark:text-gray-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-gray-100 dark:hover:bg-gray-800/80 rounded-xl transition-all shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Escritorio: permite recuperar el panel después de colapsarlo. --}}
                <button type="button" x-show="sidebarCollapsed" x-cloak @click="sidebarCollapsed = false"
                        aria-label="Mostrar menú lateral" title="Mostrar menú lateral" aria-controls="main-sidebar"
                        class="hidden lg:inline-flex p-2 -ml-2 text-gray-500 dark:text-gray-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-gray-100 dark:hover:bg-gray-800/80 rounded-xl transition-all shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                {{-- BUSCADOR RÁPIDO DE MÓDULOS (CON ÍCONOS VECTORIALES SVG) --}}
                @php
                    $modulosDisponibles = [];
                    $user = auth()->user();

                    if ($user->tieneModulo('dashboard')) {
                        $modulosDisponibles[] = [
                            'id' => 'dashboard',
                            'nombre' => 'Panel comercial',
                            'categoria' => 'Principal', 
                            'ruta' => route('dashboard'), 
                            'svg' => '<svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>', 
                            'desc' => 'Métricas, KPIs y gráficos'
                        ];
                    }
                    if ($user->tieneModulo('cotizaciones')) {
                        $modulosDisponibles[] = [
                            'id' => 'cotizaciones',
                            'nombre' => 'Cotizaciones (C001)', 
                            'categoria' => 'Comercial', 
                            'ruta' => route('cotizaciones.index'), 
                            'svg' => '<svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', 
                            'desc' => 'Gestión y emisión de cotizaciones'
                        ];
                    }
                    if ($user->tieneModulo('ventas')) {
                        $modulosDisponibles[] = [
                            'id' => 'ventas',
                            'nombre' => 'Ventas (V001)', 
                            'categoria' => 'Comercial', 
                            'ruta' => route('ventas.index'), 
                            'svg' => '<svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>', 
                            'desc' => 'Facturación y notas de venta'
                        ];
                    }
                    if ($user->tieneModulo('ordenes_compra_cliente')) {
                        $modulosDisponibles[] = [
                            'id' => 'ordenes_compra_cliente',
                            'nombre' => 'O/C de Clientes (OCC01)', 
                            'categoria' => 'Comercial', 
                            'ruta' => route('ordenes-compra-cliente.index'), 
                            'svg' => '<svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', 
                            'desc' => 'Recepción de órdenes de clientes'
                        ];
                    }
                    if ($user->tieneModulo('compras')) {
                        $modulosDisponibles[] = [
                            'id' => 'compras',
                            'nombre' => 'O/C a Proveedor (OC01)', 
                            'categoria' => 'Abastecimiento', 
                            'ruta' => route('ordenes-compra.index'), 
                            'svg' => '<svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>',
                            'desc' => 'Órdenes de compra a proveedores'
                        ];
                    }
                    if ($user->tieneModulo('productos')) {
                        $modulosDisponibles[] = [
                            'id' => 'productos',
                            'nombre' => 'Catálogo de Productos', 
                            'categoria' => 'Maestros', 
                            'ruta' => route('productos.index'), 
                            'svg' => '<svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>', 
                            'desc' => 'Gestión de productos y precios'
                        ];
                    }
                    if ($user->tieneModulo('inventario')) {
                        $modulosDisponibles[] = [
                            'id' => 'inventario',
                            'nombre' => 'Inventario / Kardex', 
                            'categoria' => 'Maestros', 
                            'ruta' => route('inventario.kardex'), 
                            'svg' => '<svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>', 
                            'desc' => 'Movimientos y stock valorizado'
                        ];
                    }
                    if ($user->tieneModulo('clientes')) {
                        $modulosDisponibles[] = [
                            'id' => 'clientes',
                            'nombre' => 'Clientes', 
                            'categoria' => 'Maestros', 
                            'ruta' => route('clientes.index'), 
                            'svg' => '<svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>', 
                            'desc' => 'Directorio y contactos comerciales'
                        ];
                    }
                    if ($user->tieneModulo('proveedores')) {
                        $modulosDisponibles[] = [
                            'id' => 'proveedores',
                            'nombre' => 'Proveedores', 
                            'categoria' => 'Maestros', 
                            'ruta' => route('proveedores.index'), 
                            'svg' => '<svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>', 
                            'desc' => 'Proveedores y condiciones de compra'
                        ];
                    }
                    if ($user->tieneModulo('configuracion')) {
                        $modulosDisponibles[] = [
                            'id' => 'series',
                            'nombre' => 'Series y Correlativos', 
                            'categoria' => 'Administración', 
                            'ruta' => route('series-documentos.index'), 
                            'svg' => '<svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>', 
                            'desc' => 'Numeración de documentos'
                        ];
                        $modulosDisponibles[] = [
                            'id' => 'configuracion',
                            'nombre' => 'Configuración General', 
                            'categoria' => 'Administración', 
                            'ruta' => route('configuracion.edit'), 
                            'svg' => '<svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', 
                            'desc' => 'RUC, empresa, cuentas e IGV'
                        ];
                    }
                    if ($user->tieneModulo('usuarios')) {
                        $modulosDisponibles[] = [
                            'id' => 'usuarios',
                            'nombre' => 'Usuarios y Roles', 
                            'categoria' => 'Administración', 
                            'ruta' => route('users.index'), 
                            'svg' => '<svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                            'desc' => 'Permisos y control de acceso'
                        ];
                    }
                    if ($user->tieneModulo('auditoria')) {
                        $modulosDisponibles[] = [
                            'id' => 'auditoria',
                            'nombre' => 'Auditoría', 
                            'categoria' => 'Administración', 
                            'ruta' => route('auditoria.index'), 
                            'svg' => '<svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>', 
                            'desc' => 'Registro de cambios del sistema'
                        ];
                    }
                    $modulosDisponibles[] = [
                        'id' => 'perfil',
                        'nombre' => 'Mi Perfil', 
                        'categoria' => 'Cuenta', 
                        'ruta' => route('profile.show'),
                        'svg' => '<svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>', 
                        'desc' => 'Datos personales y contraseña'
                    ];
                @endphp

                <div x-data="{
                        open: false,
                        query: '',
                        selectedIndex: 0,
                        modules: {{ Js::from($modulosDisponibles) }},
                        get filtered() {
                            if (!this.query) return this.modules;
                            const q = this.query.toLowerCase().trim();
                            return this.modules.filter(m => 
                                m.nombre.toLowerCase().includes(q) || 
                                m.categoria.toLowerCase().includes(q) ||
                                (m.desc && m.desc.toLowerCase().includes(q))
                            );
                        },
                        navigate() {
                            const list = this.filtered;
                            if (list.length > 0 && list[this.selectedIndex]) {
                                window.location.href = list[this.selectedIndex].ruta;
                            }
                        }
                    }" 
                    @keydown.window.ctrl.k.prevent="$refs.searchInput.focus(); open = true"
                    @keydown.window.meta.k.prevent="$refs.searchInput.focus(); open = true"
                    @click.away="open = false" 
                    class="relative flex-1">
                    
                    {{-- Input buscador con diseño ejecutivo --}}
                    <div class="relative flex items-center">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400 dark:text-gray-500" aria-hidden="true">
                            <svg class="w-4 h-4 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input x-ref="searchInput"
                               type="text" 
                               x-model="query"
                               @focus="open = true"
                               @input="open = true; selectedIndex = 0"
                               @keydown.arrow-down.prevent="selectedIndex = (selectedIndex + 1) % filtered.length"
                               @keydown.arrow-up.prevent="selectedIndex = (selectedIndex - 1 + filtered.length) % filtered.length"
                               @keydown.enter.prevent="navigate()"
                               @keydown.escape="open = false; $refs.searchInput.blur()"
                                placeholder="Buscar o elegir módulo..."
                                class="w-full min-h-[40px] pl-10 pr-14 py-2 bg-gray-100 dark:bg-card-bg hover:bg-gray-100/90 dark:hover:bg-darker-bg text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700/60 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all placeholder-gray-400 dark:placeholder-gray-500 shadow-2xs">
                        
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                            <kbd class="hidden sm:inline-block px-1.5 py-0.5 text-[10px] font-mono font-semibold text-gray-400 dark:text-gray-500 bg-white dark:bg-darker-bg border border-gray-200 dark:border-gray-700/80 rounded-md">
                                Ctrl K
                            </kbd>
                        </div>
                    </div>

                    {{-- Lista desplegable de módulos con SVG --}}
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute left-0 mt-2 w-full sm:w-96 max-h-96 bg-white dark:bg-card-bg rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-2xl overflow-y-auto custom-scrollbar z-50 p-2"
                         style="display: none;">
                        
                        <div class="px-3 py-1.5 text-[10px] font-medium text-gray-400 dark:text-gray-500 flex justify-between items-center border-b border-gray-100 dark:border-gray-700/50 pb-2 mb-1">
                            <span>Módulos del Sistema</span>
                            <span x-text="filtered.length + ' disponibles'"></span>
                        </div>

                        <template x-for="(mod, index) in filtered" :key="mod.ruta">
                            <a :href="mod.ruta" 
                               @mouseenter="selectedIndex = index"
                               :class="selectedIndex === index ? 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-900 dark:text-cyan-200 border-cyan-200 dark:border-cyan-800/80' : 'text-gray-700 dark:text-gray-300 border-transparent hover:bg-gray-50 dark:hover:bg-darker-bg'"
                               class="flex items-center gap-3 px-3 py-2 rounded-xl border transition-all text-xs">
                                
                                {{-- Contenedor con ícono SVG --}}
                                <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-darker-bg border border-gray-200 dark:border-gray-700/60 flex items-center justify-center shrink-0"
                                     x-html="mod.svg">
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-gray-900 dark:text-white" x-text="mod.nombre"></span>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-semibold" x-text="mod.categoria"></span>
                                    </div>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate" x-text="mod.desc"></p>
                                </div>
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </template>

                        <div x-show="filtered.length === 0" class="py-8 text-center text-xs text-gray-400">
                            No se encontró ningún módulo con "<span x-text="query"></span>"
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Side: Theme Toggle & User Profile --}}
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                {{-- Theme Toggle (SVG sol/luna con transición, 40px) --}}
                <button @click="darkMode = !darkMode"
                        :aria-pressed="darkMode.toString()"
                        aria-label="Cambiar entre modo claro y oscuro"
                        title="Cambiar a modo claro/oscuro"
                        class="relative h-10 w-10 shrink-0 inline-flex items-center justify-center bg-gray-100 dark:bg-card-bg text-gray-600 dark:text-cyan-400 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700/80 transition-colors border border-gray-200/80 dark:border-gray-700/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500">
                    <svg x-show="!darkMode"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -rotate-90 scale-75"
                         x-transition:enter-end="opacity-100 rotate-0 scale-100"
                         class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                    </svg>
                    <svg x-show="darkMode" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 rotate-90 scale-75"
                         x-transition:enter-end="opacity-100 rotate-0 scale-100"
                         class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                {{-- Profile Dropdown --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2.5 min-h-[40px] px-3 bg-gray-100 dark:bg-card-bg text-gray-900 dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700/80 transition-colors border border-gray-200/80 dark:border-gray-700/60">
                            <div class="text-left text-xs hidden sm:block">
                                <div class="font-bold leading-tight">{{ Auth::user()->name }}</div>
                                <div class="text-[10px] text-cyan-600 dark:text-cyan-400 font-semibold leading-tight capitalize">{{ Auth::user()->rol }}</div>
                            </div>
                            <svg class="w-3.5 h-3.5 text-gray-400 ml-1 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.show')">
                            {{ __('Mi Perfil') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();"
                                    class="text-rose-600 dark:text-rose-400">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
