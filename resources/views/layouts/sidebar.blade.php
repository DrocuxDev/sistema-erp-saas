<aside 
    :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full': !sidebarOpen,
        'lg:w-64 lg:translate-x-0': !sidebarCollapsed,
        'lg:w-0 lg:-translate-x-full lg:border-r-0': sidebarCollapsed
    }" 
    class="fixed lg:sticky top-0 inset-y-0 left-0 z-40 h-screen bg-white dark:bg-sidebar-bg border-r border-gray-200 dark:border-gray-700/50 transition-all duration-300 ease-in-out flex flex-col shrink-0 overflow-hidden select-none"
>
    {{-- Logo Limpio (Solo Texto Jhardsystex ERP) & Botón Ocultar --}}
    <div class="p-4 sm:p-5 border-b border-gray-200 dark:border-gray-700/50 flex items-center justify-between shrink-0 h-16">
        <a href="{{ route('dashboard') }}" class="flex items-center min-w-0">
            <span class="font-extrabold text-gray-900 dark:text-white text-base sm:text-lg tracking-tight truncate">
                Jhardsystex ERP
            </span>
        </a>

        <div class="flex items-center gap-1">
            {{-- Botón Ocultar en Desktop --}}
            <button @click="sidebarCollapsed = true" 
                    title="Ocultar menú lateral"
                    class="hidden lg:flex p-1.5 text-gray-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-gray-100 dark:hover:bg-gray-800/80 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>

            {{-- Botón Cerrar en Móvil --}}
            <button @click="sidebarOpen = false" 
                    class="lg:hidden p-1.5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Navegación con Scrollbar Independiente --}}
    <nav class="p-3 sm:p-4 space-y-1 flex-1 overflow-y-auto custom-scrollbar">
        
        @if(auth()->user()->tieneModulo('dashboard'))
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Dashboard</span>
            </x-nav-link>
        @endif

        {{-- SECCIÓN MAESTROS --}}
        @if(auth()->user()->tieneModulo('proveedores') || auth()->user()->tieneModulo('clientes') || auth()->user()->tieneModulo('productos') || auth()->user()->tieneModulo('inventario'))
            <div class="pt-4">
                <p class="px-3 py-1.5 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Maestros & Catálogo</p>
                
                @if(auth()->user()->tieneModulo('proveedores'))
                    <x-nav-link :href="route('proveedores.index')" :active="request()->routeIs('proveedores.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        <span>Proveedores</span>
                    </x-nav-link>
                @endif

                @if(auth()->user()->tieneModulo('clientes'))
                    <x-nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Clientes</span>
                    </x-nav-link>
                @endif

                @if(auth()->user()->tieneModulo('productos'))
                    <x-nav-link :href="route('productos.index')" :active="request()->routeIs('productos.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span>Productos</span>
                    </x-nav-link>
                @endif

                @if(auth()->user()->tieneModulo('inventario'))
                    <x-nav-link :href="route('inventario.kardex')" :active="request()->routeIs('inventario.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                        <span>Inventario / Kardex</span>
                    </x-nav-link>
                @endif
            </div>
        @endif

        {{-- SECCIÓN VENTAS --}}
        @if(auth()->user()->tieneModulo('ordenes_compra_cliente') || auth()->user()->tieneModulo('cotizaciones') || auth()->user()->tieneModulo('ventas'))
            <div class="pt-4">
                <p class="px-3 py-1.5 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Flujo Comercial</p>

                @if(auth()->user()->tieneModulo('ordenes_compra_cliente'))
                    <x-nav-link :href="route('ordenes-compra-cliente.index')" :active="request()->routeIs('ordenes-compra-cliente.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>O/C de Clientes</span>
                    </x-nav-link>
                @endif

                @if(auth()->user()->tieneModulo('cotizaciones'))
                    <x-nav-link :href="route('cotizaciones.index')" :active="request()->routeIs('cotizaciones.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Cotizaciones (C001)</span>
                    </x-nav-link>
                @endif

                @if(auth()->user()->tieneModulo('ventas'))
                    <x-nav-link :href="route('ventas.index')" :active="request()->routeIs('ventas.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <span>Ventas (V001)</span>
                    </x-nav-link>
                @endif
            </div>
        @endif

        {{-- SECCIÓN COMPRAS --}}
        @if(auth()->user()->tieneModulo('compras'))
            <div class="pt-4">
                <p class="px-3 py-1.5 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Abastecimiento</p>

                <x-nav-link :href="route('ordenes-compra.index')" :active="request()->routeIs('ordenes-compra.*')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span>O/C a Proveedor (OC01)</span>
                </x-nav-link>
            </div>
        @endif

        {{-- SECCIÓN ADMINISTRACIÓN --}}
        @if(auth()->user()->tieneModulo('usuarios') || auth()->user()->tieneModulo('configuracion') || auth()->user()->tieneModulo('auditoria'))
            <div class="pt-4">
                <p class="px-3 py-1.5 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Administración</p>
                
                @if(auth()->user()->tieneModulo('usuarios'))
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*') || request()->routeIs('roles.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span>Usuarios & Roles</span>
                    </x-nav-link>
                @endif

                @if(auth()->user()->tieneModulo('configuracion'))
                    <x-nav-link :href="route('series-documentos.index')" :active="request()->routeIs('series-documentos.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                        </svg>
                        <span>Series & Correlativos</span>
                    </x-nav-link>

                    <x-nav-link :href="route('configuracion.edit')" :active="request()->routeIs('configuracion.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Configuración</span>
                    </x-nav-link>
                @endif

                @if(auth()->user()->tieneModulo('auditoria'))
                    <x-nav-link :href="route('auditoria.index')" :active="request()->routeIs('auditoria.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span>Auditoría</span>
                    </x-nav-link>
                @endif
            </div>
        @endif
    </nav>

    {{-- Cerrar Sesión --}}
    <div class="p-3 sm:p-4 border-t border-gray-200 dark:border-gray-700/50 shrink-0 bg-gray-50/50 dark:bg-darker-bg/30">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-gray-700 dark:text-gray-300 hover:text-rose-600 dark:hover:text-rose-400 bg-white dark:bg-gray-800/60 hover:bg-rose-50 dark:hover:bg-rose-950/30 border border-gray-200/80 dark:border-gray-700/60 rounded-xl transition-all font-semibold text-xs shadow-xs">
                <svg class="w-4 h-4 text-gray-400 group-hover:text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Cerrar Sesión</span>
            </button>
        </form>
        <p class="text-[9px] text-gray-400 dark:text-gray-500 text-center mt-2.5 font-mono">JHARDSYSTEX S.A.C. © {{ date('Y') }}</p>
    </div>
</aside>
