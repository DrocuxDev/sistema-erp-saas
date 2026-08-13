<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ $proveedor->nombre }}
            </h2>
            <a href="{{ route('proveedores.index') }}" class="text-purple-primary hover:text-purple-light hover:underline font-semibold">
                ← Volver a lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-card-bg overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-gray-700/50">
                <div class="px-6 py-8">
                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Nombre Comercial</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $proveedor->nombre }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">RUC</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $proveedor->ruc }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Razón Social</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $proveedor->razon_social }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Estado</p>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold
                                {{ $proveedor->estado === 'activo' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' }}">
                                {{ ucfirst($proveedor->estado) }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-8">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Dirección</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $proveedor->direccion }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Teléfono</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $proveedor->telefono ?? 'No registrado' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Email</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $proveedor->email ?? 'No registrado' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Contacto</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $proveedor->contacto ?? 'No registrado' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Condición de Pago</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $proveedor->condicion_pago }}</p>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-6">
                        <a href="{{ route('proveedores.edit', $proveedor) }}" class="flex-1 rounded-lg bg-purple-primary px-4 py-3 text-center text-base font-semibold text-white hover:bg-purple-light transition-all">
                            Editar
                        </a>
                        <a href="{{ route('proveedores.index') }}" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-3 text-center text-base font-semibold text-gray-900 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all">
                            Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
