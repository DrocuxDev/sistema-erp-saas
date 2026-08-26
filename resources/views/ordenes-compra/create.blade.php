<x-app-layout>
    <div class="p-4 sm:p-6 max-w-6xl mx-auto space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('ordenes-compra.index') }}" class="hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">Órdenes de Compra</a>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Nueva O/C</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800/60">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </span>
                    Emitir Orden de Compra a Proveedor
                </h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="font-mono text-xs px-3 py-1.5 rounded-xl bg-cyan-50 dark:bg-cyan-950/80 text-cyan-800 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800/60 font-bold">
                    Próximo Código: {{ $proximoCodigo }}
                </span>
                <a href="{{ route('ordenes-compra.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold transition-colors">
                    ← Volver
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-800 dark:text-rose-300 text-xs space-y-1">
                <p class="font-bold">Por favor verifique los siguientes errores:</p>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('ordenes-compra.store') }}" id="form-orden-compra" class="space-y-6">
            @csrf

            <!-- Cabecera de la Orden -->
            <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider border-b border-gray-100 dark:border-gray-700/60 pb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                    Información del Proveedor y Condiciones de Compra
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1">
                            Proveedor Seleccionado <span class="text-rose-500">*</span>
                        </label>
                        <select name="proveedor_id" required
                                class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                            <option value="">Seleccione el proveedor...</option>
                            @foreach($proveedores as $p)
                                <option value="{{ $p->id }}" {{ old('proveedor_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->razon_social ?: $p->nombre }} — RUC: {{ $p->ruc }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1">
                            Fecha de Emisión <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required
                               class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1">
                            Forma de Pago
                        </label>
                        <input type="text" name="forma_pago" value="{{ old('forma_pago', '30 días crédito / Factura') }}"
                               class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1">
                            Plazo de Entrega
                        </label>
                        <input type="text" name="plazo_entrega" value="{{ old('plazo_entrega', '3 a 5 días hábiles') }}"
                               class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1">
                            Lugar de Entrega
                        </label>
                        <input type="text" name="lugar_entrega" value="{{ old('lugar_entrega', 'Almacén Central JHARDSYSTEX') }}"
                               class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1">
                            Observaciones para el Proveedor
                        </label>
                        <input type="text" name="observaciones" value="{{ old('observaciones') }}" placeholder="Ej: Entregar con certificado de garantía y guía de remisión"
                               class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                    </div>
                </div>
            </div>

            <!-- Detalle de Productos Dinámicos -->
            <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Ítems / Productos Solicitados
                    </h3>
                    <button type="button" id="btn-add-line"
                            class="px-3 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        + Agregar Producto
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left" id="table-lines">
                        <thead class="bg-slate-900 dark:bg-slate-950 text-white uppercase text-[10px] tracking-wider font-semibold">
                            <tr>
                                <th class="px-3 py-3 w-[50%]">Producto</th>
                                <th class="px-3 py-3 w-[15%] text-center">Cantidad</th>
                                <th class="px-3 py-3 w-[18%] text-right">P. Compra Unit (S/)</th>
                                <th class="px-3 py-3 w-[12%] text-right">Subtotal</th>
                                <th class="px-3 py-3 w-[5%] text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="lines-container" class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            <!-- Inserción dinámica de líneas JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Totales -->
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60 flex justify-end">
                    <div class="w-80 space-y-2 font-mono text-xs text-right">
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span class="font-sans">Subtotal (Neto):</span>
                            <span id="txt-subtotal" class="font-bold text-sm">S/ 0.00</span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span class="font-sans">I.G.V. (18%):</span>
                            <span id="txt-igv" class="font-bold text-sm">S/ 0.00</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-2 flex justify-between items-center text-sm">
                            <span class="font-sans font-bold text-cyan-600 dark:text-cyan-400">Total O/C:</span>
                            <span id="txt-total" class="font-bold text-cyan-600 dark:text-cyan-400 text-xl">S/ 0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('ordenes-compra.index') }}" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-7 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold shadow-md shadow-cyan-600/20 transition-all">
                    Guardar y Generar Orden de Compra
                </button>
            </div>
        </form>

    </div>

    @push('scripts')
    <script>
        const productosCatalogo = @json($productos);
        let lineCount = 0;

        function addLine(productoId = '', cantidad = 1, precio = '') {
            const container = document.getElementById('lines-container');
            const rowId = 'line-' + lineCount;

            let optionsHtml = '<option value="">Seleccione un producto...</option>';
            productosCatalogo.forEach(p => {
                const selected = p.id == productoId ? 'selected' : '';
                const defaultPrice = p.precio_base_compra || 0;
                optionsHtml += `<option value="${p.id}" data-precio="${defaultPrice}" ${selected}>${p.nombre} (SKU: ${p.sku || '—'})</option>`;
            });

            const tr = document.createElement('tr');
            tr.id = rowId;
            tr.className = 'hover:bg-gray-50/70 dark:hover:bg-slate-800/40 transition-colors';
            tr.innerHTML = `
                <td class="px-3 py-2.5">
                    <select name="lineas[${lineCount}][producto_id]" required onchange="onProductChange(this, ${lineCount})"
                            class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        ${optionsHtml}
                    </select>
                </td>
                <td class="px-3 py-2.5 text-center">
                    <input type="number" step="0.01" min="0.01" name="lineas[${lineCount}][cantidad]" value="${cantidad}" required oninput="calculateTotals()"
                           class="w-24 text-center font-mono text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-2 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </td>
                <td class="px-3 py-2.5 text-right">
                    <input type="number" step="0.01" min="0" name="lineas[${lineCount}][precio_compra]" id="price-${lineCount}" value="${precio}" required oninput="calculateTotals()"
                           class="w-28 text-right font-mono text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-2 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </td>
                <td class="px-3 py-2.5 text-right font-mono font-bold text-gray-900 dark:text-white" id="subtotal-${lineCount}">
                    S/ 0.00
                </td>
                <td class="px-3 py-2.5 text-center">
                    <button type="button" onclick="removeLine('${rowId}')" class="p-1.5 text-rose-500 hover:text-rose-700 transition-colors" title="Eliminar fila">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>
            `;

            container.appendChild(tr);
            lineCount++;
            calculateTotals();
        }

        function onProductChange(select, index) {
            const selectedOpt = select.options[select.selectedIndex];
            const price = selectedOpt.getAttribute('data-precio') || 0;
            const priceInput = document.getElementById(`price-${index}`);
            if (priceInput && (!priceInput.value || priceInput.value == 0)) {
                priceInput.value = parseFloat(price).toFixed(2);
            }
            calculateTotals();
        }

        function removeLine(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.remove();
                calculateTotals();
            }
        }

        function calculateTotals() {
            let subtotal = 0;
            const rows = document.getElementById('lines-container').children;

            for (let i = 0; i < rows.length; i++) {
                const tr = rows[i];
                const cantInput = tr.querySelector('input[name*="[cantidad]"]');
                const priceInput = tr.querySelector('input[name*="[precio_compra]"]');
                const subtotalTd = tr.querySelector('td[id^="subtotal-"]');

                if (cantInput && priceInput && subtotalTd) {
                    const cant = parseFloat(cantInput.value) || 0;
                    const price = parseFloat(priceInput.value) || 0;
                    const lineSub = cant * price;
                    subtotalTd.textContent = 'S/ ' + lineSub.toFixed(2);
                    subtotal += lineSub;
                }
            }

            const igv = subtotal * 0.18;
            const total = subtotal + igv;

            document.getElementById('txt-subtotal').textContent = 'S/ ' + subtotal.toFixed(2);
            document.getElementById('txt-igv').textContent = 'S/ ' + igv.toFixed(2);
            document.getElementById('txt-total').textContent = 'S/ ' + total.toFixed(2);
        }

        document.getElementById('btn-add-line').addEventListener('click', () => addLine());

        // Fila inicial al cargar
        document.addEventListener('DOMContentLoaded', () => {
            addLine();
        });
    </script>
    @endpush
</x-app-layout>
