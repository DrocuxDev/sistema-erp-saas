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

    $clientesJson = $clientes->map(function ($c) {
        return [
            'id' => $c->id,
            'nombre' => $c->nombre,
            'razon_social' => $c->razon_social,
            'ruc' => $c->ruc,
            'contacto' => $c->contacto,
            'telefono' => $c->telefono,
            'direccion' => $c->direccion,
            'condicion_pago' => $c->condicion_pago,
        ];
    })->values();

    $lineasIniciales = old('lineas', [
        [
            'producto_id' => '',
            'proveedor_id' => '',
            'descripcion' => '',
            'unidad_medida' => 'UND',
            'cantidad' => 1,
            'precio_venta_unit' => '',
            'precio_compra_unit' => '',
            'igv_compra' => 0,
        ]
    ]);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('cotizaciones.index') }}" class="p-2 bg-gray-100 dark:bg-darker-bg text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 transition-colors">
                    ← Volver
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Nueva Cotización</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                            {{ $proximoCodigo }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-xs font-mono font-semibold bg-gray-100 text-gray-700 dark:bg-darker-bg dark:text-gray-300">
                            VERSION: 001
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        @if($ordenCompra)
                            Vinculada a O/C Cliente: <strong class="text-purple-primary">{{ $ordenCompra->numero_oc }}</strong> — {{ $ordenCompra->cliente->nombre }}
                        @else
                            Emisión de propuesta técnica y económica comercial
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" form="form-cotizacion"
                        class="px-5 py-2.5 bg-purple-primary hover:bg-purple-light text-white rounded-xl text-sm font-semibold shadow-md shadow-purple-900/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Guardar Cotización
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if($errors->any())
                <div class="p-4 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-xl text-sm">
                    <div class="font-bold mb-1">Por favor verifica los siguientes campos:</div>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('cotizaciones.store') }}" method="POST" id="form-cotizacion" class="space-y-6">
                @csrf

                @if($ordenCompra)
                    <input type="hidden" name="orden_compra_cliente_id" value="{{ $ordenCompra->id }}">
                @endif

                {{-- 1. SECCIÓN: TIPO DE COTIZACIÓN Y DATOS PRINCIPALES --}}
                <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-5 sm:p-6">
                    <div class="border-b border-gray-100 dark:border-gray-700/60 pb-4 mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-purple-primary"></span>
                                Datos del Comprobante y Cliente
                            </h3>
                            <p class="text-xs text-gray-500">Selecciona el tipo de cotización para definir la portada corporativa (Página 1)</p>
                        </div>

                        {{-- Selector Tipo de Cotización (Producto vs Servicio) --}}
                        <div class="flex items-center gap-2 bg-gray-100 dark:bg-darker-bg p-1 rounded-xl">
                            <label class="flex items-center gap-2 px-3 py-1.5 rounded-lg cursor-pointer text-xs font-semibold transition-all has-[:checked]:bg-blue-600 has-[:checked]:text-white text-gray-700 dark:text-gray-300">
                                <input type="radio" name="tipo_cotizacion" value="producto" class="hidden" {{ old('tipo_cotizacion', 'producto') === 'producto' ? 'checked' : '' }}>
                                📦 PRODUCTO (TI)
                            </label>
                            <label class="flex items-center gap-2 px-3 py-1.5 rounded-lg cursor-pointer text-xs font-semibold transition-all has-[:checked]:bg-emerald-600 has-[:checked]:text-white text-gray-700 dark:text-gray-300">
                                <input type="radio" name="tipo_cotizacion" value="servicio" class="hidden" {{ old('tipo_cotizacion') === 'servicio' ? 'checked' : '' }}>
                                🛠️ SERVICIO (Campo)
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        {{-- Cliente --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Cliente / Razón Social *</label>
                            <select name="cliente_id" id="select-cliente" required
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-purple-primary">
                                <option value="">Seleccionar cliente...</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ (string) $clienteIdPreseleccionado === (string) $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nombre }} (RUC: {{ $cliente->ruc ?? 'S/N' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Representante / Contacto --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Representante / Atención</label>
                            <input type="text" name="representante" id="input-representante"
                                   value="{{ old('representante', $clienteSeleccionado->contacto ?? '') }}"
                                   placeholder="Ej: Ing. Juan Pérez"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-purple-primary">
                        </div>

                        {{-- Fecha --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Fecha de Emisión *</label>
                            <input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-purple-primary">
                        </div>

                        {{-- Asunto / Proyecto --}}
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Asunto / Nombre del Proyecto</label>
                            <input type="text" name="asunto" value="{{ old('asunto', 'COTIZACIÓN PROYECTO TI') }}"
                                   placeholder="Ej: COTIZACION PROYECTO O_US / MANTENIMIENTO PREVENTIVO DE RACKS"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-purple-primary">
                        </div>

                        {{-- No Utilizable --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">No Utilizable (S/)</label>
                            <input type="number" name="no_utilizable" id="input-no-utilizable" value="{{ old('no_utilizable', 0) }}" min="0" step="0.01"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-purple-primary">
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN: TASAS Y COMISIONES DE ESTE DOCUMENTO --}}
                <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 overflow-hidden">
                    <button type="button" onclick="document.getElementById('tasas-panel').classList.toggle('hidden'); this.querySelector('.chevron-icon').classList.toggle('rotate-180')"
                            class="w-full px-5 py-3.5 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-darker-bg/50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">⚙️ Tasas Impositivas y Comisiones de este Documento</span>
                            <span class="text-[10px] text-gray-400 font-normal">(Precargados desde Configuración Global — modificables por documento)</span>
                        </div>
                        <svg class="chevron-icon w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="tasas-panel" class="hidden px-5 pb-5 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">IGV (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="pct_igv"
                                       value="{{ old('pct_igv', $tasasDefault['pct_igv']) }}"
                                       class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm font-mono font-bold focus:ring-2 focus:ring-cyan-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Retención (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="pct_retencion"
                                       value="{{ old('pct_retencion', $tasasDefault['pct_retencion']) }}"
                                       class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-cyan-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Comisión Venta (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="pct_comision_venta"
                                       value="{{ old('pct_comision_venta', $tasasDefault['pct_comision_venta']) }}"
                                       class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-cyan-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Comisión Ganancia (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="pct_comision_ganancia"
                                       value="{{ old('pct_comision_ganancia', $tasasDefault['pct_comision_ganancia']) }}"
                                       class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-cyan-500">
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2">Los valores se precargan desde la Configuración Global del sistema. Puedes ajustarlos para este documento sin afectar los demás.</p>
                    </div>
                </div>

                {{-- 2. SECCIÓN: MATRIZ DE COTIZACIÓN (ESTILO HOJA DE CÁLCULO / EXCEL COMERCIAL) --}}
                <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                Matriz Comercial de Cotización (Desglose de Ítems)
                            </h3>
                            <p class="text-xs text-gray-500">Calcula automáticamente el total de venta, costo de compra y margen de ganancia por línea.</p>
                        </div>
                        <button type="button" id="btn-agregar-linea"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            + Agregar Producto / Fila
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-xs text-left" id="tabla-lineas">
                            <thead class="bg-slate-900 text-white uppercase text-[10px] tracking-wider font-semibold">
                                <tr>
                                    <th class="px-3 py-3 w-[26%]">Descripción / Producto</th>
                                    <th class="px-2 py-3 w-[7%] text-center">Und</th>
                                    <th class="px-2 py-3 w-[8%] text-center">Cant</th>
                                    <th class="px-2 py-3 w-[10%] text-right bg-blue-900/40">P.U Venta</th>
                                    <th class="px-2 py-3 w-[11%] text-right bg-blue-900/60">P.T Venta</th>
                                    <th class="px-2 py-3 w-[11%] text-right bg-emerald-950/60 text-emerald-300">Ganancia</th>
                                    <th class="px-2 py-3 w-[14%]">Proveedor</th>
                                    <th class="px-2 py-3 w-[9%] text-right bg-slate-800">P.U Compra</th>
                                    <th class="px-2 py-3 w-[8%] text-right bg-slate-800">IGV Comp</th>
                                    <th class="px-2 py-3 w-[5%] text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="lineas-body" class="divide-y divide-gray-200 dark:divide-gray-700/60 font-sans"></tbody>
                        </table>
                    </div>
                </div>

                {{-- 3. SECCIÓN: RESUMEN MONETARIO Y TÉRMINOS COMERCIALES --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    {{-- Términos y Condiciones --}}
                    <div class="lg:col-span-2 bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-5 space-y-4">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                            <svg class="w-4 h-4 text-purple-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Términos y Condiciones Comerciales (Impresos en PDF)
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">• Plazo de Entrega</label>
                                <input type="text" name="plazo_entrega" value="{{ old('plazo_entrega', '2 a 5 días hábiles tras recepción de O/C') }}"
                                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2.5 py-1.5 text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">• Forma de Pago</label>
                                <input type="text" name="forma_pago" value="{{ old('forma_pago', '30 días calendario previa conformidad y factura') }}"
                                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2.5 py-1.5 text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">• Logística y Flete</label>
                                <input type="text" name="logistica" value="{{ old('logistica', 'Entrega en instalaciones del cliente / Lima Metropolitana') }}"
                                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2.5 py-1.5 text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">• Validez de la Oferta</label>
                                <input type="text" name="validez_oferta" value="{{ old('validez_oferta', '15 días calendario') }}"
                                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2.5 py-1.5 text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Observaciones adicionales</label>
                            <textarea name="observaciones" rows="2" placeholder="Notas internas o especificaciones especiales..."
                                      class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-3 py-2 text-xs resize-none">{{ old('observaciones') }}</textarea>
                        </div>
                    </div>

                    {{-- Tarjeta de Totales Financieros en Vivo --}}
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-5 shadow-lg flex flex-col justify-between">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-700 pb-2 mb-3">
                                Resumen Financiero en Tiempo Real
                            </h3>
                            <div class="space-y-2.5 text-xs font-mono">
                                <div class="flex justify-between items-center text-slate-300">
                                    <span>SUBTOTAL (S/)</span>
                                    <span id="live-subtotal" class="font-bold text-white text-sm">S/ 0.00</span>
                                </div>
                                <div class="flex justify-between items-center text-slate-300">
                                    <span>I.G.V. (18%) (S/)</span>
                                    <span id="live-igv" class="text-slate-200">S/ 0.00</span>
                                </div>
                                <div class="flex justify-between items-center text-slate-400">
                                    <span>RETENCIÓN (S/)</span>
                                    <span id="live-retencion">S/ 0.00</span>
                                </div>
                                <div class="border-t border-slate-700 pt-2.5 flex justify-between items-center">
                                    <span class="font-sans font-bold text-cyan-400 text-sm">TOTAL COTIZADO</span>
                                    <span id="live-total" class="font-bold text-cyan-300 text-lg">S/ 0.00</span>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-700/80 space-y-2 text-xs font-mono">
                                <div class="flex justify-between text-slate-400">
                                    <span>Total Costo Compra:</span>
                                    <span id="live-compra-fija">S/ 0.00</span>
                                </div>
                                <div class="flex justify-between text-emerald-400 font-bold">
                                    <span>Ganancia Bruta:</span>
                                    <span id="live-ganancia">S/ 0.00</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-700/60 flex justify-end gap-2">
                            <a href="{{ route('cotizaciones.index') }}"
                               class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-xl text-xs font-semibold transition-colors">
                                Cancelar
                            </a>
                            <button type="submit"
                                    class="px-5 py-2 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                                Guardar Cotización
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const productos = @json($productosJson);
        const clientes = @json($clientesJson);
        const lineasIniciales = @json($lineasIniciales);
        let lineaIndex = 0;

        // Autocompletar datos al cambiar de cliente
        const selectCliente = document.getElementById('select-cliente');
        const inputRepresentante = document.getElementById('input-representante');
        if (selectCliente && inputRepresentante) {
            selectCliente.addEventListener('change', () => {
                const cliente = clientes.find(c => String(c.id) === String(selectCliente.value));
                if (cliente && cliente.contacto && !inputRepresentante.value) {
                    inputRepresentante.value = cliente.contacto;
                }
            });
        }

        function obtenerProducto(id) {
            return productos.find(p => String(p.id) === String(id));
        }

        function crearFilaLinea(data = {}) {
            const idx = lineaIndex++;
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 dark:hover:bg-darker-bg/50 transition-colors';
            tr.dataset.index = idx;

            const productoOptions = productos.map(p =>
                `<option value="${p.id}" ${String(data.producto_id) === String(p.id) ? 'selected' : ''}>${p.nombre}</option>`
            ).join('');

            tr.innerHTML = `
                <td class="px-2 py-2">
                    <select name="lineas[${idx}][producto_id]" required class="linea-producto w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-2 py-1 text-xs">
                        <option value="">Seleccionar producto...</option>
                        ${productoOptions}
                    </select>
                    <input type="text" name="lineas[${idx}][descripcion]" value="${data.descripcion ?? ''}" placeholder="Detalle adicional opcional..."
                           class="linea-descripcion mt-1 w-full rounded border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-darker-bg px-1.5 py-0.5 text-[11px] text-gray-600 dark:text-gray-400">
                </td>
                <td class="px-1 py-2 text-center">
                    <input type="text" name="lineas[${idx}][unidad_medida]" value="${data.unidad_medida ?? 'UND'}" maxlength="20"
                           class="linea-unidad w-12 text-center rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-1 py-1 text-xs">
                </td>
                <td class="px-1 py-2 text-center">
                    <input type="number" name="lineas[${idx}][cantidad]" value="${data.cantidad ?? 1}" min="0.01" step="0.01" required
                           class="linea-cant w-14 text-center rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-1 py-1 text-xs font-mono font-semibold">
                </td>
                <td class="px-1 py-2 text-right bg-blue-50/40 dark:bg-blue-950/20">
                    <input type="number" name="lineas[${idx}][precio_venta_unit]" value="${data.precio_venta_unit ?? ''}" min="0" step="0.01" required placeholder="0.00"
                           class="linea-pv-unit w-20 text-right rounded border border-blue-300 dark:border-blue-700 bg-white dark:bg-darker-bg px-1.5 py-1 text-xs font-mono font-bold text-blue-700 dark:text-blue-300">
                </td>
                <td class="px-2 py-2 text-right bg-blue-50/70 dark:bg-blue-950/30 font-mono font-bold text-xs text-blue-900 dark:text-blue-200">
                    <span class="linea-pv-total">S/ 0.00</span>
                </td>
                <td class="px-2 py-2 text-right bg-emerald-50/50 dark:bg-emerald-950/20 font-mono font-bold text-xs">
                    <span class="linea-ganancia text-emerald-600 dark:text-emerald-400">S/ 0.00</span>
                </td>
                <td class="px-2 py-2">
                    <select name="lineas[${idx}][proveedor_id]" required class="linea-proveedor w-full rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-1.5 py-1 text-xs">
                        <option value="">Proveedor...</option>
                    </select>
                </td>
                <td class="px-1 py-2 text-right bg-slate-50 dark:bg-slate-900/40">
                    <input type="number" name="lineas[${idx}][precio_compra_unit]" value="${data.precio_compra_unit ?? ''}" min="0" step="0.01" required placeholder="0.00"
                           class="linea-pc-unit w-18 text-right rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-1.5 py-1 text-xs font-mono">
                </td>
                <td class="px-1 py-2 text-right bg-slate-50 dark:bg-slate-900/40">
                    <input type="number" name="lineas[${idx}][igv_compra]" value="${data.igv_compra ?? 0}" min="0" step="0.01" placeholder="0.00"
                           class="linea-igv-compra w-14 text-right rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-1 py-1 text-xs font-mono">
                </td>
                <td class="px-1 py-2 text-center">
                    <button type="button" class="btn-quitar-linea p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors" title="Eliminar fila">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>
            `;

            document.getElementById('lineas-body').appendChild(tr);

            const selectProd = tr.querySelector('.linea-producto');
            const selectProv = tr.querySelector('.linea-proveedor');
            const inputUnidad = tr.querySelector('.linea-unidad');
            const inputCant = tr.querySelector('.linea-cant');
            const inputPvUnit = tr.querySelector('.linea-pv-unit');
            const inputPcUnit = tr.querySelector('.linea-pc-unit');
            const inputIgvComp = tr.querySelector('.linea-igv-compra');
            const spanPvTotal = tr.querySelector('.linea-pv-total');
            const spanGanancia = tr.querySelector('.linea-ganancia');

            function actualizarProveedores(productoId, proveedorSeleccionado = null) {
                const producto = obtenerProducto(productoId);
                selectProv.innerHTML = '<option value="">Proveedor...</option>';
                if (!producto) return;

                if (producto.unidad_medida && !inputUnidad.value) {
                    inputUnidad.value = producto.unidad_medida;
                }

                producto.proveedores.forEach(prov => {
                    const selected = String(proveedorSeleccionado) === String(prov.id) ? 'selected' : '';
                    selectProv.innerHTML += `<option value="${prov.id}" ${selected}>${prov.nombre}</option>`;
                });

                if (producto.proveedores.length === 1 && !proveedorSeleccionado) {
                    selectProv.value = producto.proveedores[0].id;
                    if (!inputPcUnit.value) {
                        inputPcUnit.value = producto.proveedores[0].precio_compra;
                    }
                }
                recalcularTotales();
            }

            function recalcularFila() {
                const cant = parseFloat(inputCant.value) || 0;
                const pvUnit = parseFloat(inputPvUnit.value) || 0;
                const pcUnit = parseFloat(inputPcUnit.value) || 0;
                const igvComp = parseFloat(inputIgvComp.value) || 0;

                const pvTotal = cant * pvUnit;
                const pcTotal = (cant * pcUnit) + igvComp;
                const ganancia = pvTotal - pcTotal;

                spanPvTotal.textContent = 'S/ ' + pvTotal.toFixed(2);
                spanGanancia.textContent = 'S/ ' + ganancia.toFixed(2);

                if (ganancia >= 0) {
                    spanGanancia.className = 'linea-ganancia text-emerald-600 dark:text-emerald-400 font-bold';
                } else {
                    spanGanancia.className = 'linea-ganancia text-red-600 dark:text-red-400 font-bold';
                }

                recalcularTotales();
            }

            selectProd.addEventListener('change', () => actualizarProveedores(selectProd.value));
            selectProv.addEventListener('change', () => {
                const producto = obtenerProducto(selectProd.value);
                const prov = producto?.proveedores.find(p => String(p.id) === String(selectProv.value));
                if (prov && !inputPcUnit.value) {
                    inputPcUnit.value = prov.precio_compra;
                }
                recalcularFila();
            });

            [inputCant, inputPvUnit, inputPcUnit, inputIgvComp].forEach(inp => {
                inp.addEventListener('input', recalcularFila);
            });

            tr.querySelector('.btn-quitar-linea').addEventListener('click', () => {
                if (document.querySelectorAll('#lineas-body tr').length > 1) {
                    tr.remove();
                    recalcularTotales();
                }
            });

            if (data.producto_id) {
                actualizarProveedores(data.producto_id, data.proveedor_id);
            }
            recalcularFila();
        }

        function recalcularTotales() {
            let subtotal = 0;
            let compraFija = 0;

            document.querySelectorAll('#lineas-body tr').forEach(tr => {
                const cant = parseFloat(tr.querySelector('.linea-cant')?.value) || 0;
                const pv = parseFloat(tr.querySelector('.linea-pv-unit')?.value) || 0;
                const pc = parseFloat(tr.querySelector('.linea-pc-unit')?.value) || 0;
                const igvComp = parseFloat(tr.querySelector('.linea-igv-compra')?.value) || 0;

                subtotal += (cant * pv);
                compraFija += ((cant * pc) + igvComp);
            });

            const igv = subtotal * 0.18;
            const retencion = 0;
            const totalFacturar = subtotal + igv - retencion;
            const ganancia = subtotal - compraFija;

            document.getElementById('live-subtotal').textContent = 'S/ ' + subtotal.toFixed(2);
            document.getElementById('live-igv').textContent = 'S/ ' + igv.toFixed(2);
            document.getElementById('live-retencion').textContent = 'S/ ' + retencion.toFixed(2);
            document.getElementById('live-total').textContent = 'S/ ' + totalFacturar.toFixed(2);
            document.getElementById('live-compra-fija').textContent = 'S/ ' + compraFija.toFixed(2);
            document.getElementById('live-ganancia').textContent = 'S/ ' + ganancia.toFixed(2);
        }

        document.getElementById('btn-agregar-linea').addEventListener('click', () => crearFilaLinea());
        lineasIniciales.forEach(linea => crearFilaLinea(linea));
    </script>
    @endpush
</x-app-layout>
