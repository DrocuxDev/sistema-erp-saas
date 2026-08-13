<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Resumen del sistema ERP</p>
            </div>
            <div class="text-left sm:text-right">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ now()->format('d \\d\\e M \\d\\e Y') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ now()->format('H:i:s') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Tarjetas de Métricas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <!-- Total Usuarios -->
                <div class="bg-gradient-to-br from-purple-primary to-purple-light rounded-xl sm:rounded-2xl shadow-xl p-5 sm:p-6 hover:shadow-2xl transition-shadow">
                    <div class="text-white">
                        <p class="text-sm font-medium opacity-90 mb-1">Total Usuarios</p>
                        <p class="text-base sm:text-lg font-semibold mb-2">Usuarios Activos</p>
                        <p class="text-3xl sm:text-4xl font-bold">{{ \App\Models\User::count() }}</p>
                    </div>
                </div>

                <!-- Total Proveedores -->
                <div class="bg-white dark:bg-card-bg rounded-xl sm:rounded-2xl shadow-xl p-5 sm:p-6 hover:shadow-2xl transition-all duration-200">
                    <div class="text-gray-800 dark:text-white">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Proveedores Activos</p>
                        <p class="text-base sm:text-lg font-semibold mb-2">Total Proveedores</p>
                        <p class="text-3xl sm:text-4xl font-bold">{{ \App\Models\Proveedor::where('estado', 'activo')->count() }}</p>
                    </div>
                </div>

                <!-- Total Clientes -->
                <div class="bg-gradient-to-br from-pink-accent to-pink-400 rounded-xl sm:rounded-2xl shadow-xl p-5 sm:p-6 hover:shadow-2xl transition-shadow sm:col-span-2 lg:col-span-1">
                    <div class="text-white">
                        <p class="text-sm font-medium opacity-90 mb-1">Clientes Activos</p>
                        <p class="text-base sm:text-lg font-semibold mb-2">Total Clientes</p>
                        <p class="text-3xl sm:text-4xl font-bold">{{ \App\Models\Cliente::where('estado', 'activo')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <!-- Productos con Stock Bajo -->
                <div class="bg-white dark:bg-card-bg rounded-xl sm:rounded-2xl shadow-xl overflow-hidden">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-600/30">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">⚠️ Productos con Stock Bajo</h3>
                    </div>
                    <div class="p-4 sm:p-6">
                        @php
                            $productosStockBajo = \App\Models\Producto::whereColumn('stock_actual', '<=', 'stock_minimo')->limit(5)->get();
                        @endphp
                        @forelse ($productosStockBajo as $producto)
                            <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-600/30 last:border-b-0">
                                <div class="min-w-0 flex-1 mr-3">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $producto->nombre }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $producto->sku }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-pink-accent">{{ $producto->stock_actual }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">mín: {{ $producto->stock_minimo }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">✓ Todos los productos tienen stock adecuado</p>
                        @endforelse
                        @if($productosStockBajo->count() > 0)
                            <div class="mt-4">
                                <a href="{{ route('productos.index') }}" class="block text-center px-4 py-2 bg-purple-primary text-white rounded-lg hover:bg-purple-light transition-colors font-medium text-sm">
                                    Ver todos los productos
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Métricas de Inventario -->
                <div class="bg-white dark:bg-card-bg rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white mb-4 sm:mb-6">📦 Inventario</h3>
                    <div class="space-y-3 sm:space-y-4">
                        <div class="flex items-center justify-between p-3 sm:p-4 bg-gray-50 dark:bg-darker-bg rounded-xl">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Productos</p>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ \App\Models\Producto::count() }}</p>
                            </div>
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-accent/20 rounded-lg flex items-center justify-center">
                                <span class="text-xl sm:text-2xl">📦</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 sm:p-4 bg-gray-50 dark:bg-darker-bg rounded-xl">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Productos Vendibles</p>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ \App\Models\Producto::where('puede_venderse', true)->count() }}</p>
                            </div>
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                                <span class="text-xl sm:text-2xl">✅</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-3 sm:p-4 bg-gray-50 dark:bg-darker-bg rounded-xl">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Productos Comprables</p>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ \App\Models\Producto::where('puede_comprarse', true)->count() }}</p>
                            </div>
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
                                <span class="text-xl sm:text-2xl">🛒</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acceso Rápido -->
            <div class="bg-white dark:bg-card-bg rounded-xl sm:rounded-2xl shadow-xl overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-600/30">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">🚀 Acceso Rápido</h3>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                        <a href="{{ route('productos.index') }}" class="group flex flex-col items-center gap-2 sm:gap-3 p-3 sm:p-4 bg-gray-50 dark:bg-darker-bg rounded-xl hover:bg-gray-100 dark:hover:bg-purple-primary/20 border border-gray-200 dark:border-gray-600/30 dark:hover:border-purple-primary transition-all duration-200">
                            <div class="w-10 h-10 sm:w-14 sm:h-14 bg-blue-accent/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span class="text-2xl sm:text-3xl">📦</span>
                            </div>
                            <div class="text-center">
                                <p class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white">Productos</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 hidden sm:block">Administrar</p>
                            </div>
                        </a>

                        <a href="{{ route('proveedores.index') }}" class="group flex flex-col items-center gap-2 sm:gap-3 p-3 sm:p-4 bg-gray-50 dark:bg-darker-bg rounded-xl hover:bg-gray-100 dark:hover:bg-purple-primary/20 border border-gray-200 dark:border-gray-600/30 dark:hover:border-purple-primary transition-all duration-200">
                            <div class="w-10 h-10 sm:w-14 sm:h-14 bg-orange-500/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span class="text-2xl sm:text-3xl">🏢</span>
                            </div>
                            <div class="text-center">
                                <p class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white">Proveedores</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 hidden sm:block">Administrar</p>
                            </div>
                        </a>

                        <a href="{{ route('clientes.index') }}" class="group flex flex-col items-center gap-2 sm:gap-3 p-3 sm:p-4 bg-gray-50 dark:bg-darker-bg rounded-xl hover:bg-gray-100 dark:hover:bg-purple-primary/20 border border-gray-200 dark:border-gray-600/30 dark:hover:border-purple-primary transition-all duration-200">
                            <div class="w-10 h-10 sm:w-14 sm:h-14 bg-green-500/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span class="text-2xl sm:text-3xl">🤝</span>
                            </div>
                            <div class="text-center">
                                <p class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white">Clientes</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 hidden sm:block">Administrar</p>
                            </div>
                        </a>

                        @if (auth()->user()->rol === 'administrador')
                            <a href="{{ route('users.index') }}" class="group flex flex-col items-center gap-2 sm:gap-3 p-3 sm:p-4 bg-gray-50 dark:bg-darker-bg rounded-xl hover:bg-gray-100 dark:hover:bg-purple-primary/20 border border-gray-200 dark:border-gray-600/30 dark:hover:border-purple-primary transition-all duration-200">
                                <div class="w-10 h-10 sm:w-14 sm:h-14 bg-purple-primary/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <span class="text-2xl sm:text-3xl">👥</span>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white">Usuarios</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 hidden sm:block">Administrar</p>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Información del Sistema -->
            @if (auth()->user()->rol === 'administrador')
            <div class="bg-white dark:bg-card-bg rounded-xl sm:rounded-2xl shadow-xl overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-600/30">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">ℹ️ Información del Sistema</h3>
                </div>
                <div class="p-4 sm:p-6 grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                    <div class="p-3 sm:p-4 bg-gray-50 dark:bg-darker-bg rounded-xl">
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold mb-1 sm:mb-2">Aplicación</p>
                        <p class="text-sm sm:text-lg font-bold text-gray-900 dark:text-white truncate">{{ config('app.name', 'ERP') }}</p>
                    </div>
                    <div class="p-3 sm:p-4 bg-gray-50 dark:bg-darker-bg rounded-xl">
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold mb-1 sm:mb-2">Entorno</p>
                        <p class="text-sm sm:text-lg font-bold text-gray-900 dark:text-white capitalize">{{ app()->environment() }}</p>
                    </div>
                    <div class="p-3 sm:p-4 bg-gray-50 dark:bg-darker-bg rounded-xl">
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold mb-1 sm:mb-2">Usuario Actual</p>
                        <p class="text-sm sm:text-lg font-bold text-gray-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                    </div>
                    <div class="p-3 sm:p-4 bg-gray-50 dark:bg-darker-bg rounded-xl">
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold mb-1 sm:mb-2">Rol</p>
                        <p class="text-sm sm:text-lg font-bold text-gray-900 dark:text-white capitalize">{{ auth()->user()->rol }}</p>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
