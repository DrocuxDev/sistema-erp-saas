@php
    $productosJson = $productos->map(function ($producto) {
        return [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'sku' => $producto->sku,
            'unidad_medida' => $producto->unidad_medida,
            'precio_venta' => $producto->precio_venta !== null ? (float) $producto->precio_venta : null,
            'precio_compra' => $producto->precio_compra !== null ? (float) $producto->precio_compra : null,
            'proveedores' => $producto->productoProveedores->map(function ($pp) {
                return [
                    'id' => $pp->proveedor_id,
                    'nombre' => $pp->proveedor->nombre ?? null,
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

    $lineasIniciales = old('lineas', $cotizacion->detalles->map(function ($detalle) {
        return [
            'producto_id' => $detalle->producto_id,
            'producto_nombre' => $detalle->producto->nombre ?? '',
            'proveedor_id' => $detalle->proveedor_id,
            'descripcion' => $detalle->descripcion,
            'unidad_medida' => $detalle->unidad_medida ?: 'UND',
            'cantidad' => $detalle->cantidad,
            'precio_venta_unit' => $detalle->precio_venta_unit,
            'precio_compra_unit' => $detalle->precio_compra_unit,
            'igv_compra' => $detalle->igv_compra,
        ];
    })->all());

    $proximaVersion = sprintf('%03d', ($cotizacion->version ?: 1) + 1);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('cotizaciones.show', $cotizacion) }}" class="p-2 bg-gray-100 dark:bg-darker-bg text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 transition-colors">
                    Volver
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Cotización {{ $cotizacion->codigo_formateado }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            Versión Actual: {{ $cotizacion->version_formateada }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                            Nueva Versión: {{ $proximaVersion }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Al guardar cambios, se preservará el código <strong>{{ $cotizacion->codigo_formateado }}</strong> y se incrementará la versión a <strong>{{ $proximaVersion }}</strong>.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" form="form-cotizacion"
                        class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Actualizar Cotización</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="page-container space-y-6">

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

            <form action="{{ route('cotizaciones.update', $cotizacion) }}" method="POST" id="form-cotizacion" class="space-y-6" data-dirty-check>
                @csrf
                @method('PUT')
                @if($cotizacion->orden_compra_cliente_id)
                    <input type="hidden" name="orden_compra_cliente_id" value="{{ $cotizacion->orden_compra_cliente_id }}">
                @endif

                {{-- 1. SECCIÓN: TIPO DE COTIZACIÓN Y DATOS PRINCIPALES --}}
                <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-5 sm:p-6">
                    <div class="border-b border-gray-100 dark:border-gray-700/60 pb-4 mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-cyan-600 dark:bg-cyan-500"></span>
                                Datos del Comprobante y Cliente
                            </h3>
                            <p class="text-xs text-gray-500">Configuración de portada corporativa e información del proyecto</p>
                        </div>

                        {{-- Selector Tipo de Cotización (Producto vs Servicio) --}}
                        <div class="flex items-center gap-2 bg-gray-100 dark:bg-darker-bg p-1 rounded-xl">
                            <label class="flex items-center gap-2 px-3 py-1.5 rounded-lg cursor-pointer text-xs font-semibold transition-all has-[:checked]:bg-blue-600 has-[:checked]:text-white text-gray-700 dark:text-gray-300">
                                <input type="radio" name="tipo_cotizacion" value="producto" class="hidden" {{ old('tipo_cotizacion', $cotizacion->tipo_cotizacion) === 'producto' ? 'checked' : '' }}>
                                📦 PRODUCTO (TI)
                            </label>
                            <label class="flex items-center gap-2 px-3 py-1.5 rounded-lg cursor-pointer text-xs font-semibold transition-all has-[:checked]:bg-emerald-600 has-[:checked]:text-white text-gray-700 dark:text-gray-300">
                                <input type="radio" name="tipo_cotizacion" value="servicio" class="hidden" {{ old('tipo_cotizacion', $cotizacion->tipo_cotizacion) === 'servicio' ? 'checked' : '' }}>
                                🛠️ SERVICIO (Campo)
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        {{-- Cliente --}}
                        <div class="md:col-span-2">
                            <label class="form-label">Cliente / Razón Social *</label>
                            <select name="cliente_id" id="select-cliente" required
                                    class="form-input">
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ (string) old('cliente_id', $cotizacion->cliente_id) === (string) $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nombre }} (RUC: {{ $cliente->ruc ?? 'S/N' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Representante / Contacto --}}
                        <div>
                            <label class="form-label">Representante / Atención</label>
                            <input type="text" name="representante" id="input-representante"
                                   value="{{ old('representante', $cotizacion->representante ?: ($cotizacion->cliente->contacto ?? '')) }}"
                                   placeholder="Ej: Ing. Juan Pérez"
                                   class="form-input">
                        </div>

                        {{-- Fecha --}}
                        <div>
                            <label class="form-label">Fecha de Emisión *</label>
                            <input type="date" name="fecha" value="{{ old('fecha', $cotizacion->fecha ? $cotizacion->fecha->format('Y-m-d') : date('Y-m-d')) }}" required
                                   class="form-input">
                        </div>

                        {{-- Asunto / Proyecto --}}
                        <div class="md:col-span-3">
                            <label class="form-label">Asunto / Nombre del Proyecto</label>
                            <input type="text" name="asunto" value="{{ old('asunto', $cotizacion->asunto ?: 'COTIZACIÓN PROYECTO TI') }}"
                                   placeholder="Ej: COTIZACION PROYECTO O_US / MANTENIMIENTO PREVENTIVO DE RACKS"
                                   class="form-input">
                        </div>

                        {{-- No Utilizable --}}
                        <div>
                            <label class="form-label">No Utilizable (S/)</label>
                            <input type="number" name="no_utilizable" id="input-no-utilizable" value="{{ old('no_utilizable', $cotizacion->resumen->no_utilizable ?? 0) }}" min="0" step="0.01"
                                   class="form-input">
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
                            <span class="text-[10px] text-gray-400 font-normal">(valores almacenados con esta cotización)</span>
                        </div>
                        <svg class="chevron-icon w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="tasas-panel" class="hidden px-5 pb-5 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div><label class="form-label">Precio unitario</label><select name="precio_incluye_igv" class="form-input"><option value="0" @selected((string) old('precio_incluye_igv', (int) $precioIncluyeIgvDefault) === '0')>Sin IGV</option><option value="1" @selected((string) old('precio_incluye_igv', (int) $precioIncluyeIgvDefault) === '1')>Incluye IGV</option></select></div>
                            <div class="md:col-span-3"><label class="form-label">Motivo de excepción tributaria</label><input name="motivo_excepcion_tributaria" class="form-input" value="{{ old('motivo_excepcion_tributaria', $cotizacion->motivo_excepcion_tributaria) }}" placeholder="Obligatorio si cambias base, IGV o retención del cliente"></div>
                            <div>
                                <label class="form-label">IGV (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="pct_igv"
                                       value="{{ old('pct_igv', $tasasDefault['pct_igv']) }}"
                                       class="form-input font-mono font-bold focus:ring-2 focus:ring-cyan-500">
                            </div>
                            <div>
                                <label class="form-label">Retención (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="pct_retencion"
                                       value="{{ old('pct_retencion', $tasasDefault['pct_retencion']) }}"
                                       class="form-input font-mono focus:ring-2 focus:ring-cyan-500">
                            </div>
                            <div>
                                <label class="form-label">Comisión Venta (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="pct_comision_venta"
                                       value="{{ old('pct_comision_venta', $tasasDefault['pct_comision_venta']) }}"
                                       class="form-input font-mono focus:ring-2 focus:ring-cyan-500">
                            </div>
                            <div>
                                <label class="form-label">Comisión Ganancia (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="pct_comision_ganancia"
                                       value="{{ old('pct_comision_ganancia', $tasasDefault['pct_comision_ganancia']) }}"
                                       class="form-input font-mono focus:ring-2 focus:ring-cyan-500">
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2">Estos son los porcentajes almacenados con esta cotización. Modificarlos solo afecta a este documento.</p>
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
                            <thead class="bg-slate-900 text-white text-xs font-semibold">
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
                    <p id="aviso-lineas" class="hidden mt-2 text-xs font-semibold text-red-600 dark:text-red-400">Revisa las filas: cada una debe tener un producto elegido de la lista (no basta con escribir el texto).</p>
                    <p id="aviso-duplicados" class="hidden mt-1 text-xs font-semibold text-amber-600 dark:text-amber-400">Hay un producto repetido en dos filas. Deja solo una o cambia la selección.</p>
                </div>

                {{-- 3. SECCIÓN: RESUMEN MONETARIO Y TÉRMINOS COMERCIALES --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    {{-- Términos y Condiciones --}}
                    <div class="lg:col-span-2 bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-5 space-y-4">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                            <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Términos y Condiciones Comerciales (Impresos en PDF)
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">• Plazo de Entrega</label>
                                <input type="text" name="plazo_entrega" value="{{ old('plazo_entrega', $cotizacion->plazo_entrega ?: '2 a 5 días hábiles tras recepción de O/C') }}"
                                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2.5 py-1.5 text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">• Forma de Pago</label>
                                <input type="text" name="forma_pago" value="{{ old('forma_pago', $cotizacion->forma_pago ?: '30 días calendario previa conformidad y factura') }}"
                                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2.5 py-1.5 text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">• Logística y Flete</label>
                                <input type="text" name="logistica" value="{{ old('logistica', $cotizacion->logistica ?: 'Entrega en instalaciones del cliente / Lima Metropolitana') }}"
                                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2.5 py-1.5 text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">• Validez de la Oferta</label>
                                <input type="text" name="validez_oferta" value="{{ old('validez_oferta', $cotizacion->validez_oferta ?: '15 días calendario') }}"
                                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2.5 py-1.5 text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Observaciones adicionales</label>
                            <textarea name="observaciones" rows="2" placeholder="Notas internas o especificaciones especiales..."
                                      class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-3 py-2 text-xs resize-none">{{ old('observaciones', $cotizacion->observaciones) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Condiciones adicionales (impresas en el PDF)</label>
                            <textarea name="condiciones_adicionales" rows="2" placeholder="Ej: Stock sujeto a disponibilidad. Garantía de 12 meses..."
                                      class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-3 py-2 text-xs resize-none">{{ old('condiciones_adicionales', $cotizacion->condiciones_adicionales) }}</textarea>
                        </div>
                    </div>

                    {{-- Tarjeta de Totales Financieros en Vivo --}}
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-5 shadow-lg flex flex-col justify-between">
                        <div>
                            <h3 class="text-xs font-semibold text-slate-400 border-b border-slate-700 pb-2 mb-3">
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
                            <a href="{{ route('cotizaciones.show', $cotizacion) }}"
                               class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-xl text-xs font-semibold transition-colors">
                                Cancelar
                            </a>
                            <button type="submit"
                                    class="btn-primary !min-h-[36px] !py-1.5 !px-5 text-xs">
                                Guardar Cambios
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/cotizacion-producto-combobox.js') }}"></script>
    <script>
        const productos = @json($productosJson);
        window.productos = productos;
        const clientes = @json($clientesJson);
        const lineasIniciales = @json($lineasIniciales);
        const PRODUCTOS_SEARCH_URL = @json(route('productos.buscar'));
        const PRODUCTOS_RAPIDO_URL = @json(route('productos.rapido'));
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('#form-cotizacion input[name="_token"]')?.value || '';
        let lineaIndex = 0;
        const comboApis = [];

        function obtenerProducto(id) {
            return productos.find(p => String(p.id) === String(id));
        }

        function nombreProducto(id) {
            const p = obtenerProducto(id);
            return p ? (p.nombre || '') : '';
        }

        function validarDuplicados() {
            const ids = [...document.querySelectorAll('#lineas-body tr')].map(tr => tr.querySelector('.linea-producto-id')?.value || '');
            const conteo = {};
            ids.forEach(id => { if (id) conteo[id] = (conteo[id] || 0) + 1; });
            let hayDuplicados = false;
            document.querySelectorAll('#lineas-body tr').forEach(tr => {
                const id = tr.querySelector('.linea-producto-id')?.value || '';
                const errEl = tr.querySelector('.linea-producto-error');
                const input = tr.querySelector('.combo-prod-input');
                const esDuplicado = id && conteo[id] > 1;
                if (esDuplicado) {
                    hayDuplicados = true;
                    if (errEl && errEl.textContent !== 'Selecciona un producto de la lista') {
                        errEl.textContent = 'Producto duplicado en otra fila';
                        errEl.classList.remove('hidden');
                    }
                    input?.classList.add('!border-red-500');
                } else if (errEl && errEl.textContent === 'Producto duplicado en otra fila') {
                    errEl.textContent = '';
                    errEl.classList.add('hidden');
                    input?.classList.remove('!border-red-500');
                }
            });
            const aviso = document.getElementById('aviso-duplicados');
            if (aviso) aviso.classList.toggle('hidden', !hayDuplicados);
            return !hayDuplicados;
        }

        function crearFilaLinea(data = {}) {
            const idx = lineaIndex++;
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 dark:hover:bg-darker-bg/50 transition-colors';
            tr.dataset.index = idx;

            const nombreInicial = data.producto_nombre || nombreProducto(data.producto_id);

            tr.innerHTML = `
                <td class="px-2 py-2">
                    <div class="relative">
                        <input type="text" autocomplete="off" placeholder="Escribe para buscar producto..."
                               value="${String(nombreInicial ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;')}"
                               class="combo-prod-input linea-producto-text w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-2 py-1 text-xs">
                        <input type="hidden" name="lineas[${idx}][producto_id]" value="${data.producto_id ?? ''}" class="linea-producto-id">
                        <p class="linea-producto-error hidden mt-1 text-[11px] font-semibold text-red-600 dark:text-red-400"></p>
                    </div>
                    <input type="text" name="lineas[${idx}][descripcion]" value="${String(data.descripcion ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;')}" placeholder="Detalle adicional opcional..."
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

            const inputProdText = tr.querySelector('.linea-producto-text');
            const hiddenProdId = tr.querySelector('.linea-producto-id');
            const errorProd = tr.querySelector('.linea-producto-error');
            const selectProv = tr.querySelector('.linea-proveedor');
            const inputUnidad = tr.querySelector('.linea-unidad');
            const inputCant = tr.querySelector('.linea-cant');
            const inputPvUnit = tr.querySelector('.linea-pv-unit');
            const inputPcUnit = tr.querySelector('.linea-pc-unit');
            const inputIgvComp = tr.querySelector('.linea-igv-compra');
            const spanPvTotal = tr.querySelector('.linea-pv-total');
            const spanGanancia = tr.querySelector('.linea-ganancia');

            function pintarProveedores(producto, proveedorSeleccionado = null) {
                selectProv.innerHTML = '<option value="">Proveedor...</option>';
                if (!producto) return;
                (producto.proveedores || []).forEach(prov => {
                    const selected = String(proveedorSeleccionado) === String(prov.id) ? 'selected' : '';
                    const nombre = (prov.nombre || 'Proveedor').replace(/</g, '&lt;');
                    selectProv.innerHTML += `<option value="${prov.id}" ${selected}>${nombre}</option>`;
                });
            }

            function actualizarProveedores(productoId, proveedorSeleccionado = null) {
                const producto = obtenerProducto(productoId);
                if (!producto) {
                    pintarProveedores(null);
                    recalcularTotales();
                    return;
                }
                pintarProveedores(producto, proveedorSeleccionado);

                if (producto.unidad_medida && !inputUnidad.value) {
                    inputUnidad.value = producto.unidad_medida;
                }

                if ((producto.proveedores || []).length === 1 && !proveedorSeleccionado) {
                    selectProv.value = producto.proveedores[0].id;
                    if (!inputPcUnit.value) {
                        inputPcUnit.value = producto.proveedores[0].precio_compra;
                    }
                }
                recalcularTotales();
            }

            function aplicarProductoSeleccionado(producto) {
                if (producto.unidad_medida) inputUnidad.value = producto.unidad_medida;
                if (producto.precio_venta !== null && producto.precio_venta !== undefined && producto.precio_venta !== '') {
                    inputPvUnit.value = producto.precio_venta;
                }
                pintarProveedores(producto, null);
                if ((producto.proveedores || []).length === 1) {
                    selectProv.value = producto.proveedores[0].id;
                    inputPcUnit.value = producto.proveedores[0].precio_compra;
                } else if ((producto.proveedores || []).length > 1) {
                    selectProv.value = '';
                    inputPcUnit.value = inputPcUnit.value || '';
                } else if (producto.precio_compra !== null && producto.precio_compra !== undefined && producto.precio_compra !== '') {
                    inputPcUnit.value = producto.precio_compra;
                }
                if (!obtenerProducto(producto.id)) {
                    productos.push(producto);
                }
                recalcularFila();
                validarDuplicados();
            }

            function recalcularFila() {
                const ventaCentimos = window.importeLineaCentimos(inputCant.value, inputPvUnit.value);
                const compraCentimos = window.importeLineaCentimos(inputCant.value, inputPcUnit.value)
                    + window.aCentimos(inputIgvComp.value);
                const gananciaCentimos = ventaCentimos - compraCentimos;

                spanPvTotal.textContent = 'S/ ' + window.formatearCentimos(ventaCentimos);
                spanGanancia.textContent = 'S/ ' + window.formatearCentimos(gananciaCentimos);

                if (gananciaCentimos >= 0n) {
                    spanGanancia.className = 'linea-ganancia text-emerald-600 dark:text-emerald-400 font-bold';
                } else {
                    spanGanancia.className = 'linea-ganancia text-red-600 dark:text-red-400 font-bold';
                }

                recalcularTotales();
            }

            const comboApi = window.CotizacionProductoCombobox.attach({
                input: inputProdText,
                hidden: hiddenProdId,
                errorEl: errorProd,
                productos,
                proveedores: @json($proveedores),
                searchUrl: PRODUCTOS_SEARCH_URL,
                createUrl: PRODUCTOS_RAPIDO_URL,
                csrfToken: CSRF_TOKEN,
                initialId: data.producto_id || '',
                initialNombre: nombreInicial,
                onSelect: aplicarProductoSeleccionado,
                onClear: () => { validarDuplicados(); },
                onValidate: () => { validarDuplicados(); },
            });
            comboApis.push(comboApi);
            selectProv.addEventListener('change', () => {
                const producto = obtenerProducto(hiddenProdId.value);
                const prov = producto?.proveedores?.find(p => String(p.id) === String(selectProv.value));
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
                    comboApis.splice(comboApis.indexOf(comboApi), 1);
                    tr.remove();
                    recalcularTotales();
                    validarDuplicados();
                }
            });

            if (data.producto_id) {
                actualizarProveedores(data.producto_id, data.proveedor_id);
            }
            recalcularFila();
        }

        function validarFormularioLineas() {
            let ok = true;
            comboApis.forEach(api => {
                if (!api.validarFila()) ok = false;
            });
            document.querySelectorAll('#lineas-body tr').forEach(tr => {
                const hidden = tr.querySelector('.linea-producto-id');
                const errEl = tr.querySelector('.linea-producto-error');
                const input = tr.querySelector('.combo-prod-input');
                if (!hidden?.value) {
                    ok = false;
                    if (errEl) {
                        errEl.textContent = 'Selecciona un producto de la lista';
                        errEl.classList.remove('hidden');
                    }
                    input?.classList.add('!border-red-500');
                }
            });
            if (!validarDuplicados()) ok = false;
            const aviso = document.getElementById('aviso-lineas');
            if (aviso) aviso.classList.toggle('hidden', ok);
            return ok;
        }

        function recalcularTotales() {
            const lineas = [...document.querySelectorAll('#lineas-body tr')].map(tr => ({
                cantidad: tr.querySelector('.linea-cant')?.value || '0',
                precio_venta_unit: tr.querySelector('.linea-pv-unit')?.value || '0',
                precio_compra_unit: tr.querySelector('.linea-pc-unit')?.value || '0',
                igv_compra: tr.querySelector('.linea-igv-compra')?.value || '0',
            }));
            const resumen = window.calcularResumenCotizacion({
                lineas,
                pctIgv: document.querySelector('[name="pct_igv"]').value || '0',
                pctRetencion: document.querySelector('[name="pct_retencion"]').value || '0',
                pctComisionVenta: document.querySelector('[name="pct_comision_venta"]').value || '0',
                pctComisionGanancia: document.querySelector('[name="pct_comision_ganancia"]').value || '0',
                precioIncluyeIgv: document.querySelector('[name="precio_incluye_igv"]').value === '1',
                noUtilizable: document.querySelector('[name="no_utilizable"]').value || '0',
            });

            document.getElementById('live-subtotal').textContent = 'S/ ' + resumen.subtotal;
            document.getElementById('live-igv').textContent = 'S/ ' + resumen.igv;
            document.getElementById('live-retencion').textContent = 'S/ ' + resumen.retencion;
            document.getElementById('live-total').textContent = 'S/ ' + resumen.totalFacturar;
            document.getElementById('live-compra-fija').textContent = 'S/ ' + resumen.compraFija;
            document.getElementById('live-ganancia').textContent = 'S/ ' + resumen.ganancia;
        }

        document.querySelectorAll('[name="no_utilizable"]').forEach(input => input.addEventListener('input', recalcularTotales));

        document.getElementById('btn-agregar-linea').addEventListener('click', () => crearFilaLinea());
        const perfilesClientes = @json($clientes->mapWithKeys(fn ($cliente) => [$cliente->id => ['precio_incluye_igv' => (bool) $cliente->precio_incluye_igv, 'pct_igv' => (float) $cliente->pct_igv, 'pct_retencion' => (float) $cliente->pct_retencion]]));
        document.getElementById('select-cliente').addEventListener('change', event => {
            const perfil = perfilesClientes[event.target.value];
            if (!perfil) return;
            document.querySelector('[name="precio_incluye_igv"]').value = perfil.precio_incluye_igv ? '1' : '0';
            document.querySelector('[name="pct_igv"]').value = perfil.pct_igv;
            document.querySelector('[name="pct_retencion"]').value = perfil.pct_retencion;
            const cliente = clientes.find(c => String(c.id) === String(event.target.value));
            if (cliente?.condicion_pago) document.querySelector('[name="forma_pago"]').value = cliente.condicion_pago;
            recalcularTotales();
        });
        ['pct_igv', 'pct_retencion', 'precio_incluye_igv'].forEach(name => document.querySelector(`[name="${name}"]`).addEventListener('input', recalcularTotales));
        document.getElementById('form-cotizacion').addEventListener('submit', (e) => {
            if (!validarFormularioLineas()) {
                e.preventDefault();
                document.getElementById('tabla-lineas')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
        window.addEventListener('load', () => {
            lineasIniciales.forEach(linea => crearFilaLinea(linea));
            recalcularTotales();
        }, { once: true });
    </script>
    @endpush
</x-app-layout>
