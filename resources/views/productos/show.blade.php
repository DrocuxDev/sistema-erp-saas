<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $producto->nombre }}</h2>
            <a href="{{ route('productos.index') }}" class="text-purple-primary hover:text-purple-light hover:underline font-semibold">
                ← Volver a lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-card-bg overflow-hidden shadow-md rounded-lg border border-gray-200 dark:border-gray-700/50">
                <!-- Header Info -->
                <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-8 py-8 text-white">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
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
                                    Stock Bajo
                                </p>
                            @else
                                <p class="mt-1 px-3 py-1 bg-green-500 text-white rounded-full text-xs font-bold w-fit">
                                    En Stock
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                    <div class="px-8 flex space-x-6 whitespace-nowrap">
                        <button class="tab-btn py-4 px-2 border-b-2 border-cyan-500 text-cyan-600 dark:text-cyan-400 font-bold text-xs uppercase tracking-wider" onclick="showTab('detalles', this)">
                            DETALLES
                        </button>
                        <button class="tab-btn py-4 px-2 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-semibold text-xs uppercase tracking-wider" onclick="showTab('inventario', this)">
                            INVENTARIO
                        </button>
                        <button class="tab-btn py-4 px-2 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-semibold text-xs uppercase tracking-wider" onclick="showTab('opciones', this)">
                            OPCIONES
                        </button>
                        <button class="tab-btn py-4 px-2 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-semibold text-xs uppercase tracking-wider" onclick="showTab('proveedores', this)">
                            PROVEEDORES ({{ $producto->productoProveedores->count() }})
                        </button>
                        <button class="tab-btn py-4 px-2 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-semibold text-xs uppercase tracking-wider" onclick="showTab('presentaciones', this)">
                            PRESENTACIONES ({{ $producto->presentacionesCompra->count() }})
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="px-8 py-8">
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
                        </div>
                    </div>

                    <!-- Inventario Tab -->
                    <div id="inventario" class="tab-content hidden">
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="bg-blue-50 dark:bg-darker-bg rounded-lg p-6 border-l-4 border-blue-500">
                                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Stock Actual</p>
                                    <p class="text-4xl font-bold text-gray-900 dark:text-white mt-2">{{ $producto->stock_actual }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $producto->unidad_medida }}</p>
                                </div>
                                <div class="bg-orange-50 dark:bg-darker-bg rounded-lg p-6 border-l-4 border-orange-500">
                                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Stock Mínimo</p>
                                    <p class="text-4xl font-bold text-gray-900 dark:text-white mt-2">{{ $producto->stock_minimo }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $producto->unidad_medida }}</p>
                                </div>
                            </div>
                            @if ($producto->stock_actual <= $producto->stock_minimo)
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                    <p class="text-sm font-semibold text-red-800 dark:text-red-400">⚠️ Advertencia: Stock bajo</p>
                                    <p class="text-sm text-red-700 dark:text-red-300 mt-1">El stock actual está por debajo del nivel mínimo permitido.</p>
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

                    <!-- Proveedores Asociados Tab -->
                    <div id="proveedores" class="tab-content hidden space-y-6">
                        <form action="{{ route('productos.proveedores.add', $producto) }}" method="POST" class="bg-gray-50 dark:bg-darker-bg p-4 rounded-lg flex flex-col md:flex-row gap-4 items-end">
                            @csrf
                            <div class="flex-1">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Proveedor</label>
                                <select name="proveedor_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-darker-bg text-gray-900 dark:text-white focus:ring-purple-primary focus:border-purple-primary">
                                    <option value="">Seleccionar proveedor...</option>
                                    @foreach ($proveedoresDisponibles as $prov)
                                        <option value="{{ $prov->id }}">{{ $prov->nombre }} ({{ $prov->ruc }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full md:w-48">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Precio Compra</label>
                                <input type="number" step="0.01" min="0" name="precio_compra" required placeholder="0.00" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-darker-bg text-gray-900 dark:text-white focus:ring-purple-primary focus:border-purple-primary">
                            </div>
                            <button type="submit" class="px-4 py-2 bg-purple-primary text-white text-sm font-semibold rounded-md hover:bg-purple-light transition-colors">
                                + Asociar
                            </button>
                        </form>

                        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700/50 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-darker-bg text-xs text-gray-700 dark:text-gray-300 uppercase">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Proveedor</th>
                                        <th class="px-4 py-3 text-left">RUC</th>
                                        <th class="px-4 py-3 text-right">Precio Compra</th>
                                        <th class="px-4 py-3 text-center">Última Act.</th>
                                        <th class="px-4 py-3 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-900 dark:text-gray-100">
                                    @forelse ($producto->productoProveedores as $pp)
                                        <tr>
                                            <td class="px-4 py-3 font-medium">{{ $pp->proveedor->nombre ?? '-' }}</td>
                                            <td class="px-4 py-3">{{ $pp->proveedor->ruc ?? '-' }}</td>
                                            <td class="px-4 py-3 text-right font-bold">S/ {{ number_format($pp->precio_compra, 2) }}</td>
                                            <td class="px-4 py-3 text-center text-xs text-gray-500 dark:text-gray-400">{{ $pp->fecha_actualizacion ? \Carbon\Carbon::parse($pp->fecha_actualizacion)->format('d/m/Y') : '-' }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <form action="{{ route('productos.proveedores.remove', [$producto, $pp->proveedor_id]) }}" method="POST" onsubmit="return confirm('¿Desasociar proveedor?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-xs font-semibold">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400 text-sm">No hay proveedores asociados a este producto.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Presentaciones de Compra Tab -->
                    <div id="presentaciones" class="tab-content hidden space-y-6">
                        <form action="{{ route('productos.presentaciones.add', $producto) }}" method="POST" class="bg-gray-50 dark:bg-darker-bg p-4 rounded-lg flex flex-col md:flex-row gap-4 items-end">
                            @csrf
                            <div class="flex-1">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Unidad Presentación</label>
                                <input type="text" name="unidad_medida" placeholder="Ej: Caja x 12, Saco 50kg" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-darker-bg text-gray-900 dark:text-white focus:ring-purple-primary focus:border-purple-primary">
                            </div>
                            <div class="w-full md:w-48">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Equivalencia (en {{ $producto->unidad_medida }})</label>
                                <input type="number" step="0.0001" min="0.0001" name="equivalencia" required placeholder="12" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-darker-bg text-gray-900 dark:text-white focus:ring-purple-primary focus:border-purple-primary">
                            </div>
                            <div class="flex items-center gap-2 pb-2">
                                <input type="checkbox" id="preferida" name="preferida" value="1" class="rounded border-gray-300 dark:border-gray-600 text-purple-primary focus:ring-purple-primary">
                                <label for="preferida" class="text-xs text-gray-700 dark:text-gray-300">Preferida</label>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-purple-primary text-white text-sm font-semibold rounded-md hover:bg-purple-light transition-colors">
                                + Agregar
                            </button>
                        </form>

                        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700/50 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-darker-bg text-xs text-gray-700 dark:text-gray-300 uppercase">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Unidad Presentación</th>
                                        <th class="px-4 py-3 text-right">Equivalencia</th>
                                        <th class="px-4 py-3 text-center">Preferida</th>
                                        <th class="px-4 py-3 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-900 dark:text-gray-100">
                                    @forelse ($producto->presentacionesCompra as $pres)
                                        <tr>
                                            <td class="px-4 py-3 font-medium">{{ $pres->unidad_medida }}</td>
                                            <td class="px-4 py-3 text-right font-bold">{{ number_format($pres->equivalencia, 4) }} {{ $producto->unidad_medida }}</td>
                                            <td class="px-4 py-3 text-center">
                                                @if($pres->preferida)
                                                    <span class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 rounded-full text-xs font-semibold">Sí</span>
                                                @else
                                                    <span class="text-gray-400 text-xs">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <form action="{{ route('productos.presentaciones.remove', [$producto, $pres->id]) }}" method="POST" onsubmit="return confirm('¿Eliminar presentación?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-xs font-semibold">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400 text-sm">No hay presentaciones de compra registradas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="px-8 py-6 bg-gray-50 dark:bg-darker-bg border-t border-gray-200 dark:border-gray-700 flex gap-4">
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
