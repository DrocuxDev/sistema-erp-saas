@php
    $productosJson = $productos->map(function ($producto) {
        return [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'unidad_medida' => $producto->unidad_medida,
            'proveedores' => $producto->productoProveedores->map(function ($pp) {
                return [
                    'id' => $pp->proveedor_id,
                    'nombre' => $pp->proveedor->nombre,
                    'precio_compra' => (float) $pp->precio_compra,
                ];
            })->values(),
        ];
    })->values();

    $lineasIniciales = old('lineas', $cotizacion->detalles->map(function ($detalle) {
        return [
            'producto_id' => $detalle->producto_id,
            'proveedor_id' => $detalle->proveedor_id,
            'cantidad' => $detalle->cantidad,
            'precio_venta_unit' => $detalle->precio_venta_unit,
            'precio_compra_unit' => $detalle->precio_compra_unit,
            'igv_compra' => $detalle->igv_compra,
        ];
    })->all());
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('cotizaciones.show', $cotizacion) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
                ← Volver
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Cotización #{{ $cotizacion->id }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Solo editable en estado pendiente</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('cotizaciones.update', $cotizacion) }}" method="POST" id="form-cotizacion"
                  class="bg-white dark:bg-card-bg rounded-xl shadow p-6 space-y-6">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-xl text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Cliente *</label>
                        <select name="cliente_id" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm">
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ (string) old('cliente_id', $cotizacion->cliente_id) === (string) $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Fecha *</label>
                        <input type="date" name="fecha" value="{{ old('fecha', $cotizacion->fecha?->format('Y-m-d')) }}" required
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">No utilizable (S/)</label>
                        <input type="number" name="no_utilizable" value="{{ old('no_utilizable', $cotizacion->resumen->no_utilizable ?? 0) }}" min="0" step="0.01"
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Observaciones</label>
                    <textarea name="observaciones" rows="2"
                              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm resize-none">{{ old('observaciones', $cotizacion->observaciones) }}</textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Líneas de cotización</h3>
                        <button type="button" id="btn-agregar-linea"
                                class="px-3 py-1.5 bg-purple-primary text-white rounded-lg text-sm hover:bg-purple-light transition-colors">
                            + Agregar línea
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" id="tabla-lineas">
                            <thead class="bg-gray-50 dark:bg-darker-bg">
                                <tr>
                                    <th class="px-2 py-2 text-left">Producto</th>
                                    <th class="px-2 py-2 text-left">Proveedor</th>
                                    <th class="px-2 py-2 text-left">Cant.</th>
                                    <th class="px-2 py-2 text-left">P. Venta</th>
                                    <th class="px-2 py-2 text-left">P. Compra</th>
                                    <th class="px-2 py-2 text-left">IGV Compra</th>
                                    <th class="px-2 py-2"></th>
                                </tr>
                            </thead>
                            <tbody id="lineas-body"></tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('cotizaciones.show', $cotizacion) }}"
                       class="px-4 py-2 bg-gray-200 dark:bg-darker-bg text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-300 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-purple-primary text-white rounded-lg text-sm hover:bg-purple-light transition-colors font-semibold">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const productos = @json($productosJson);
        const lineasIniciales = @json($lineasIniciales);
        let lineaIndex = 0;

        function obtenerProducto(id) {
            return productos.find(p => String(p.id) === String(id));
        }

        function crearFilaLinea(data = {}) {
            const idx = lineaIndex++;
            const tr = document.createElement('tr');
            tr.className = 'border-t border-gray-200 dark:border-gray-600/30';

            const productoOptions = productos.map(p =>
                `<option value="${p.id}" ${String(data.producto_id) === String(p.id) ? 'selected' : ''}>${p.nombre}</option>`
            ).join('');

            tr.innerHTML = `
                <td class="px-2 py-2">
                    <select name="lineas[${idx}][producto_id]" required class="linea-producto w-full min-w-[160px] rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1 text-sm">
                        <option value="">Producto...</option>
                        ${productoOptions}
                    </select>
                </td>
                <td class="px-2 py-2">
                    <select name="lineas[${idx}][proveedor_id]" required class="linea-proveedor w-full min-w-[140px] rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1 text-sm">
                        <option value="">Proveedor...</option>
                    </select>
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="lineas[${idx}][cantidad]" value="${data.cantidad ?? 1}" min="0.01" step="0.01" required
                           class="w-20 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1 text-sm">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="lineas[${idx}][precio_venta_unit]" value="${data.precio_venta_unit ?? ''}" min="0" step="0.01" required
                           class="w-24 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1 text-sm">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="lineas[${idx}][precio_compra_unit]" value="${data.precio_compra_unit ?? ''}" min="0" step="0.01" required
                           class="linea-precio-compra w-24 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1 text-sm">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="lineas[${idx}][igv_compra]" value="${data.igv_compra ?? 0}" min="0" step="0.01"
                           class="w-20 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1 text-sm">
                </td>
                <td class="px-2 py-2">
                    <button type="button" class="btn-quitar-linea text-red-600 hover:text-red-800 text-xs font-semibold">Quitar</button>
                </td>
            `;

            document.getElementById('lineas-body').appendChild(tr);

            const selectProducto = tr.querySelector('.linea-producto');
            const selectProveedor = tr.querySelector('.linea-proveedor');
            const inputPrecioCompra = tr.querySelector('.linea-precio-compra');

            function actualizarProveedores(productoId, proveedorSeleccionado = null) {
                const producto = obtenerProducto(productoId);
                selectProveedor.innerHTML = '<option value="">Proveedor...</option>';
                if (!producto) return;

                producto.proveedores.forEach(prov => {
                    const selected = String(proveedorSeleccionado) === String(prov.id) ? 'selected' : '';
                    selectProveedor.innerHTML += `<option value="${prov.id}" ${selected}>${prov.nombre}</option>`;
                });
            }

            selectProducto.addEventListener('change', () => actualizarProveedores(selectProducto.value));
            selectProveedor.addEventListener('change', () => {
                const producto = obtenerProducto(selectProducto.value);
                const prov = producto?.proveedores.find(p => String(p.id) === String(selectProveedor.value));
                if (prov) inputPrecioCompra.value = prov.precio_compra;
            });

            tr.querySelector('.btn-quitar-linea').addEventListener('click', () => {
                if (document.querySelectorAll('#lineas-body tr').length > 1) tr.remove();
            });

            if (data.producto_id) actualizarProveedores(data.producto_id, data.proveedor_id);
        }

        document.getElementById('btn-agregar-linea').addEventListener('click', () => crearFilaLinea());
        lineasIniciales.forEach(linea => crearFilaLinea(linea));
    </script>
    @endpush
</x-app-layout>
