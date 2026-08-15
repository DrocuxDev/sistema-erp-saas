@php
    $productosJson = $productos->map(function ($producto) {
        return [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'unidad_medida' => $producto->unidad_medida,
            'stock_actual' => (float) $producto->stock_actual,
            'proveedores' => $producto->productoProveedores->map(function ($pp) {
                return [
                    'id' => $pp->proveedor_id,
                    'nombre' => $pp->proveedor->nombre,
                    'precio_compra' => (float) $pp->precio_compra,
                ];
            })->values(),
        ];
    })->values();

    $lineasIniciales = old('lineas');

    if (! $lineasIniciales && $lineasPrecargadas->isNotEmpty()) {
        $lineasIniciales = $lineasPrecargadas->map(function ($detalle) {
            return [
                'producto_id' => $detalle->producto_id,
                'cantidad' => $detalle->cantidad,
                'precio_venta_unit' => $detalle->precio_venta_unit,
                'precio_compra_unit' => $detalle->precio_compra_unit,
            ];
        })->values()->all();
    }

    if (! $lineasIniciales) {
        $lineasIniciales = [['producto_id' => '', 'cantidad' => 1, 'precio_venta_unit' => '', 'precio_compra_unit' => '']];
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('ventas.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
                ← Volver
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Nueva Venta</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    @if($cotizacion)
                        Generada desde Cotización #{{ $cotizacion->id }} — {{ $cotizacion->cliente->nombre }}
                    @elseif($ordenCompra)
                        Desde OC {{ $ordenCompra->numero_oc }} — {{ $ordenCompra->cliente->nombre }}
                    @else
                        Registrar venta directa
                    @endif
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('ventas.store') }}" method="POST" id="form-venta"
                  class="bg-white dark:bg-card-bg rounded-xl shadow p-6 space-y-6">
                @csrf

                @if($cotizacion)
                    <input type="hidden" name="cotizacion_id" value="{{ $cotizacion->id }}">
                @endif
                @if($ordenCompra)
                    <input type="hidden" name="orden_compra_cliente_id" value="{{ $ordenCompra->id }}">
                @endif

                @if($errors->any())
                    <div class="p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-xl text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Cliente *</label>
                        <select name="cliente_id" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm"
                                {{ $cotizacion || $ordenCompra ? 'disabled' : '' }}>
                            <option value="">Seleccionar...</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ (string) $clienteIdPreseleccionado === (string) $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @if($cotizacion || $ordenCompra)
                            <input type="hidden" name="cliente_id" value="{{ $clienteIdPreseleccionado }}">
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Fecha *</label>
                        <input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Líneas de venta</h3>
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
                                    <th class="px-2 py-2 text-left">Stock</th>
                                    <th class="px-2 py-2 text-left">Cant.</th>
                                    <th class="px-2 py-2 text-left">P. Venta</th>
                                    <th class="px-2 py-2 text-left">P. Compra</th>
                                    <th class="px-2 py-2 text-right">Total</th>
                                    <th class="px-2 py-2"></th>
                                </tr>
                            </thead>
                            <tbody id="lineas-body"></tbody>
                        </table>
                    </div>

                    <div class="flex justify-end mt-4">
                        <div class="text-right">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total de la venta</p>
                            <p class="text-2xl font-bold text-purple-primary" id="total-venta">S/ 0.00</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('ventas.index') }}"
                       class="px-4 py-2 bg-gray-200 dark:bg-darker-bg text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-300 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-purple-primary text-white rounded-lg text-sm hover:bg-purple-light transition-colors font-semibold">
                        Registrar Venta
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

        function recalcularTotales() {
            let total = 0;
            document.querySelectorAll('#lineas-body tr').forEach(tr => {
                const cantidad = parseFloat(tr.querySelector('.linea-cantidad')?.value) || 0;
                const precioVenta = parseFloat(tr.querySelector('.linea-precio-venta')?.value) || 0;
                const subtotal = cantidad * precioVenta;
                tr.querySelector('.linea-total').textContent = 'S/ ' + subtotal.toFixed(2);
                total += subtotal;
            });
            document.getElementById('total-venta').textContent = 'S/ ' + total.toFixed(2);
        }

        function crearFilaLinea(data = {}) {
            const idx = lineaIndex++;
            const tr = document.createElement('tr');
            tr.className = 'border-t border-gray-200 dark:border-gray-600/30';

            const productoOptions = productos.map(p =>
                `<option value="${p.id}" data-stock="${p.stock_actual}" ${String(data.producto_id) === String(p.id) ? 'selected' : ''}>${p.nombre}</option>`
            ).join('');

            tr.innerHTML = `
                <td class="px-2 py-2">
                    <select name="lineas[${idx}][producto_id]" required class="linea-producto w-full min-w-[180px] rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1 text-sm">
                        <option value="">Producto...</option>
                        ${productoOptions}
                    </select>
                </td>
                <td class="px-2 py-2 linea-stock text-xs text-gray-500">—</td>
                <td class="px-2 py-2">
                    <input type="number" name="lineas[${idx}][cantidad]" value="${data.cantidad ?? 1}" min="0.01" step="0.01" required
                           class="linea-cantidad w-20 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1 text-sm">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="lineas[${idx}][precio_venta_unit]" value="${data.precio_venta_unit ?? ''}" min="0" step="0.01" required
                           class="linea-precio-venta w-24 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1 text-sm">
                </td>
                <td class="px-2 py-2">
                    <input type="number" name="lineas[${idx}][precio_compra_unit]" value="${data.precio_compra_unit ?? ''}" min="0" step="0.01" required
                           class="linea-precio-compra w-24 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1 text-sm">
                </td>
                <td class="px-2 py-2 text-right linea-total font-mono font-semibold">S/ 0.00</td>
                <td class="px-2 py-2">
                    <button type="button" class="btn-quitar-linea text-red-600 hover:text-red-800 text-xs font-semibold">Quitar</button>
                </td>
            `;

            document.getElementById('lineas-body').appendChild(tr);

            const selectProducto = tr.querySelector('.linea-producto');
            const stockCell = tr.querySelector('.linea-stock');
            const inputCantidad = tr.querySelector('.linea-cantidad');
            const inputPrecioVenta = tr.querySelector('.linea-precio-venta');
            const inputPrecioCompra = tr.querySelector('.linea-precio-compra');

            function actualizarProducto() {
                const producto = obtenerProducto(selectProducto.value);
                if (producto) {
                    stockCell.textContent = producto.stock_actual + ' ' + (producto.unidad_medida || '');
                    if (producto.proveedores.length > 0 && !inputPrecioCompra.value) {
                        inputPrecioCompra.value = producto.proveedores[0].precio_compra;
                    }
                } else {
                    stockCell.textContent = '—';
                }
                recalcularTotales();
            }

            selectProducto.addEventListener('change', actualizarProducto);
            inputCantidad.addEventListener('input', recalcularTotales);
            inputPrecioVenta.addEventListener('input', recalcularTotales);

            tr.querySelector('.btn-quitar-linea').addEventListener('click', () => {
                if (document.querySelectorAll('#lineas-body tr').length > 1) {
                    tr.remove();
                    recalcularTotales();
                }
            });

            if (data.producto_id) actualizarProducto();
        }

        document.getElementById('btn-agregar-linea').addEventListener('click', () => crearFilaLinea());
        lineasIniciales.forEach(linea => crearFilaLinea(linea));
        recalcularTotales();
    </script>
    @endpush
</x-app-layout>