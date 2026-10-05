<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $producto->nombre }}</h2>
            <a href="{{ route('productos.index') }}" class="btn-secondary text-xs">
                Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="page-container space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-card-bg overflow-hidden shadow-md rounded-lg border border-gray-200 dark:border-gray-700/50">
                <!-- Header Info -->
                <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-6 text-white">
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                        @if($producto->imagen)
                            <img src="{{ Storage::url($producto->imagen) }}" alt="Foto de {{ $producto->nombre }}"
                                 class="w-32 h-32 rounded-xl object-cover border border-white/20 shrink-0" />
                        @else
                            <div class="w-32 h-32 rounded-xl border border-dashed border-white/20 bg-white/5 flex items-center justify-center shrink-0" title="Sin foto">
                                <svg class="w-12 h-12 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 flex-1 w-full">
                        <div>
                            <p class="text-sm font-semibold text-white/80">SKU</p>
                            <p class="text-2xl font-bold mt-1">{{ $producto->sku }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white/80">Categoría</p>
                            <p class="text-lg font-semibold mt-1">{{ $producto->categoria ?? 'Sin categoría' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white/80">Unidad de Medida</p>
                            <p class="text-lg font-semibold mt-1">{{ $producto->unidad_medida }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white/80">Estado</p>
                            @if ($producto->stock_actual <= $producto->stock_minimo)
                                <p class="mt-1 px-3 py-1 bg-red-500 text-white rounded-full text-xs font-bold w-fit">
                                    Existencias bajas
                                </p>
                            @else
                                <p class="mt-1 px-3 py-1 bg-green-500 text-white rounded-full text-xs font-bold w-fit">
                                    Disponible
                                </p>
                            @endif
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                    <div class="px-6 flex space-x-6 whitespace-nowrap">
                        <button class="tab-btn py-4 px-2 border-b-2 border-cyan-500 text-cyan-600 dark:text-cyan-400 font-semibold text-xs" onclick="showTab('detalles', this)">
                            DETALLES
                        </button>
                        <button class="tab-btn py-4 px-2 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-medium text-xs" onclick="showTab('inventario', this)">
                            INVENTARIO
                        </button>
                        <button class="tab-btn py-4 px-2 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-medium text-xs" onclick="showTab('opciones', this)">
                            OPCIONES
                        </button>
                        <button class="tab-btn py-4 px-2 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-medium text-xs" onclick="showTab('proveedores', this)">
                            PROVEEDORES ({{ $producto->productoProveedores->count() }})
                        </button>
                        <button class="tab-btn py-4 px-2 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-medium text-xs" onclick="showTab('presentaciones', this)">
                            PRESENTACIONES ({{ $producto->presentacionesCompra->count() }})
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="px-6 py-6">
                    <!-- Detalles Tab -->
                    <div id="detalles" class="tab-content">
                        <div class="space-y-6">
                            <div>
                                <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Nombre del Producto</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $producto->nombre }}</p>
                            </div>
                            @if ($producto->descripcion)
                                <div>
                                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Descripción</p>
                                    <p class="text-base text-gray-700 dark:text-gray-300">{{ $producto->descripcion }}</p>
                                </div>
                            @endif
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-2">
                                @php
                                    $atributos = [
                                        'Tipo de producto' => $producto->tipo_producto ? ucfirst($producto->tipo_producto) : null,
                                        'Tipo de existencia' => $producto->tipo_existencia ? ucfirst(str_replace('_', ' ', $producto->tipo_existencia)) : null,
                                        'Precio compra ref.' => $producto->precio_compra !== null ? 'S/ '.number_format($producto->precio_compra, 2) : null,
                                        'Precio venta ref.' => $producto->precio_venta !== null ? 'S/ '.number_format($producto->precio_venta, 2) : null,
                                        'Marca' => $producto->marca,
                                        'Modelo' => $producto->modelo,
                                        'Existencias máximas' => $producto->stock_maximo,
                                        'Peso bruto (kg)' => $producto->peso_bruto,
                                        'Seguimiento' => $producto->seguimiento ? ucfirst($producto->seguimiento) : null,
                                        'Condición' => $producto->condicion ? ucfirst($producto->condicion) : null,
                                        'Envase' => $producto->envase ? ucfirst($producto->envase) : null,
                                        'Área producción' => $producto->tipo_area_produccion ? ucfirst($producto->tipo_area_produccion) : null,
                                        'Se fabrica' => $producto->se_fabrica ? 'Sí' : null,
                                    ];
                                @endphp
                                @foreach($atributos as $etiqueta => $valor)
                                    @if(!is_null($valor) && $valor !== '')
                                        <div>
                                            <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">{{ $etiqueta }}</p>
                                            <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $valor }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Inventario Tab -->
                    <div id="inventario" class="tab-content hidden">
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-blue-50 dark:bg-darker-bg rounded-lg p-6 border-l-4 border-blue-500">
                                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Existencias actuales</p>
                                    <p class="text-4xl font-bold text-gray-900 dark:text-white mt-2">{{ $producto->stock_actual }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $producto->unidad_medida }}</p>
                                </div>
                                <div class="bg-orange-50 dark:bg-darker-bg rounded-lg p-6 border-l-4 border-orange-500">
                                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Existencias mínimas</p>
                                    <p class="text-4xl font-bold text-gray-900 dark:text-white mt-2">{{ $producto->stock_minimo }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $producto->unidad_medida }}</p>
                                </div>
                            </div>
                            @if ($producto->stock_actual <= $producto->stock_minimo)
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                    <p class="text-sm font-semibold text-red-800 dark:text-red-400">⚠️ Advertencia: existencias bajas</p>
                                    <p class="text-sm text-red-700 dark:text-red-300 mt-1">Las existencias actuales están por debajo del nivel mínimo permitido.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Opciones de Negocio Tab -->
                    <div id="opciones" class="tab-content hidden">
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-gray-50 dark:bg-darker-bg rounded-lg p-6 border-l-4 {{ $producto->puede_venderse ? 'border-green-500' : 'border-gray-300 dark:border-gray-600' }}">
                                    <div class="flex items-center">
                                        @if ($producto->puede_venderse)
                                            <div class="text-green-500 text-2xl mr-3">✓</div>
                                        @else
                                            <div class="text-gray-400 text-2xl mr-3">✗</div>
                                        @endif
                                        <div>
                                            <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Puede venderse</p>
                                            <p class="text-sm {{ $producto->puede_venderse ? 'text-green-700 dark:text-green-400' : 'text-gray-700 dark:text-gray-300' }} font-medium">
                                                {{ $producto->puede_venderse ? 'Habilitado' : 'Deshabilitado' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 dark:bg-darker-bg rounded-lg p-6 border-l-4 {{ $producto->puede_comprarse ? 'border-green-500' : 'border-gray-300 dark:border-gray-600' }}">
                                    <div class="flex items-center">
                                        @if ($producto->puede_comprarse)
                                            <div class="text-green-500 text-2xl mr-3">✓</div>
                                        @else
                                            <div class="text-gray-400 text-2xl mr-3">✗</div>
                                        @endif
                                        <div>
                                            <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Puede comprarse</p>
                                            <p class="text-sm {{ $producto->puede_comprarse ? 'text-green-700 dark:text-green-400' : 'text-gray-700 dark:text-gray-300' }} font-medium">
                                                {{ $producto->puede_comprarse ? 'Habilitado' : 'Deshabilitado' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Proveedores Asociados Tab (solo lectura: la gestión vive en el edit) -->
                    <div id="proveedores" class="tab-content hidden space-y-6">
                        <x-proveedores-table :producto="$producto" :readonly="true" />
                    </div>

                    <!-- Presentaciones de Compra Tab (solo lectura: la gestión vive en el edit) -->
                    <div id="presentaciones" class="tab-content hidden space-y-6">
                        <x-presentaciones-table :producto="$producto" tipo="compra" :readonly="true" />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="px-6 py-6 bg-gray-50 dark:bg-darker-bg border-t border-gray-200 dark:border-gray-700 flex gap-4">
                    <a href="{{ route('productos.edit', $producto) }}" class="flex-1 rounded-lg bg-cyan-600 px-6 py-3 text-center text-base font-semibold text-white hover:bg-cyan-500 transition-all shadow-sm">
                        Editar Producto
                    </a>
                    <a href="{{ route('productos.index') }}" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-6 py-3 text-center text-base font-semibold text-gray-900 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition-all">
                        Volver a Lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName, element) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            document.getElementById(tabName).classList.remove('hidden');
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-cyan-500', 'text-cyan-600', 'dark:text-cyan-400', 'font-bold');
                btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'font-semibold');
            });
            element.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'font-semibold');
            element.classList.add('border-cyan-500', 'text-cyan-600', 'dark:text-cyan-400', 'font-bold');
        }
    </script>
</x-app-layout>
