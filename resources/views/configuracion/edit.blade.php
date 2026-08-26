<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                    <span>Administración</span>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Configuración</span>
                </div>
                <h2 class="font-bold text-2xl text-gray-900 dark:text-white flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800/60">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    Configuración General del Sistema
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Parámetros de la empresa emisora, RUC, cuentas bancarias, tasas de IGV y comisiones comerciales.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-2xl text-green-800 dark:text-green-300 font-semibold text-sm">
                    ✓ {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('configuracion.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- 1. DATOS DE LA EMPRESA EMISORA (IMPRESOS EN COTIZACIONES / COMPROBANTES / ORDENES) --}}
                <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-6 space-y-5">
                    <div class="border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            Datos de Nuestra Empresa (Membrete y Cabecera de Documentos)
                        </h3>
                        <p class="text-xs text-gray-500">Esta información se refleja automáticamente en la cabecera, recuadro de RUC y pie de página de las cotizaciones y órdenes.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nombre Comercial *</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $configuracion->nombre) }}" required
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Razón Social Fiscal *</label>
                            <input type="text" name="razon_social" value="{{ old('razon_social', $configuracion->razon_social) }}" required
                                   placeholder="Ej: JHARDSYSTEX S.A.C."
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">RUC de la Empresa *</label>
                            <input type="text" name="ruc" value="{{ old('ruc', $configuracion->ruc) }}" required maxlength="20"
                                   placeholder="Ej: 20601245781"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs font-mono font-bold">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Dirección Fiscal / Oficina Principal</label>
                            <input type="text" name="direccion" value="{{ old('direccion', $configuracion->direccion) }}"
                                   placeholder="Ej: Av. Principal N° 450 Int. 302, Lima"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Teléfonos de Contacto</label>
                            <input type="text" name="telefono" value="{{ old('telefono', $configuracion->telefono) }}"
                                   placeholder="Ej: (+51) 987 654 321 - (01) 748-2300"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">E-mails Comerciales</label>
                            <input type="text" name="email" value="{{ old('email', $configuracion->email) }}"
                                   placeholder="Ej: ventas@jhardsystex.com | info@jhardsystex.com"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Sitio Web Oficial</label>
                            <input type="text" name="sitio_web" value="{{ old('sitio_web', $configuracion->sitio_web) }}"
                                   placeholder="Ej: www.jhardsystex.com"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs">
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Cuentas Bancarias y Medios de Pago (Impresos al pie del PDF)</label>
                            <textarea name="cuentas_bancarias" rows="3"
                                      placeholder="• BCP Soles: 193-XXXXXXX | CCI: 002-XXXXXXX&#10;• BBVA Soles: 0011-XXXXXXX | CCI: 011-XXXXXXX"
                                      class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs font-mono resize-none">{{ old('cuentas_bancarias', $configuracion->cuentas_bancarias) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 2. CONDICIONES COMERCIALES PREDETERMINADAS --}}
                <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-6 space-y-4">
                    <div class="border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-primary"></span>
                            Valores y Términos Comerciales Predeterminados
                        </h3>
                        <p class="text-xs text-gray-500">Se precargarán automáticamente al crear nuevas cotizaciones y órdenes de compra.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">• Plazo de Entrega Predeterminado</label>
                            <input type="text" name="plazo_entrega_default" value="{{ old('plazo_entrega_default', $configuracion->plazo_entrega_default) }}"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">• Forma de Pago Predeterminada</label>
                            <input type="text" name="forma_pago_default" value="{{ old('forma_pago_default', $configuracion->forma_pago_default) }}"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">• Validez de Oferta Predeterminada</label>
                            <input type="text" name="validez_oferta_default" value="{{ old('validez_oferta_default', $configuracion->validez_oferta_default) }}"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">• Logística y Flete Predeterminado</label>
                            <input type="text" name="logistica_default" value="{{ old('logistica_default', $configuracion->logistica_default) }}"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs">
                        </div>
                    </div>
                </div>

                {{-- 3. EDITOR DE PLANTILLA PDF DE COTIZACIÓN --}}
                <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-6 space-y-4">
                    <div class="border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                    Plantilla HTML del PDF de Cotización
                                </h3>
                                <p class="text-xs text-gray-500">Edita directamente el HTML/CSS que genera el documento PDF. Usa las variables Blade disponibles para datos dinámicos.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" id="btn-toggle-guide"
                                        class="px-3 py-1.5 bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 rounded-lg text-xs font-semibold hover:bg-amber-200 dark:hover:bg-amber-900/50 transition-colors">
                                    📖 Ver Variables Disponibles
                                </button>
                                <button type="button" id="btn-restore-default"
                                        class="px-3 py-1.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg text-xs font-semibold hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">
                                    🔄 Restaurar Plantilla Original
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Guía de Variables Colapsable --}}
                    <div id="template-guide" class="hidden bg-slate-50 dark:bg-darker-bg rounded-xl p-4 border border-slate-200 dark:border-gray-700 text-xs space-y-3">
                        <h4 class="font-bold text-gray-900 dark:text-white text-sm">Variables Blade Disponibles</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <p class="font-bold text-gray-700 dark:text-gray-300 mb-1">📋 Datos de la Empresa (desde Configuración)</p>
                                <ul class="space-y-0.5 text-gray-600 dark:text-gray-400 font-mono text-[10px]">
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $config->razon_social }}</code> — Razón Social</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $config->ruc }}</code> — RUC</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $config->direccion }}</code> — Dirección</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $config->telefono }}</code> — Teléfonos</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $config->email }}</code> — Emails</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $config->sitio_web }}</code> — Web</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $config->cuentas_bancarias }}</code> — Cuentas Bancarias</li>
                                </ul>
                            </div>
                            <div>
                                <p class="font-bold text-gray-700 dark:text-gray-300 mb-1">📄 Datos de la Cotización</p>
                                <ul class="space-y-0.5 text-gray-600 dark:text-gray-400 font-mono text-[10px]">
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $codigoCompleto }}</code> — Código (C001-X)</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $cotizacion->fecha->format('d/m/Y') }}</code> — Fecha</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $cotizacion->cliente->nombre }}</code> — Nombre Cliente</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $cotizacion->cliente->ruc }}</code> — RUC Cliente</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $cotizacion->representante }}</code> — Atención</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $cotizacion->asunto }}</code> — Asunto / Proyecto</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $cotizacion->forma_pago }}</code> — Forma de Pago</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $cotizacion->plazo_entrega }}</code> — Plazo Entrega</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $cotizacion->validez_oferta }}</code> — Validez Oferta</li>
                                </ul>
                            </div>
                            <div>
                                <p class="font-bold text-gray-700 dark:text-gray-300 mb-1">💰 Datos Financieros</p>
                                <ul class="space-y-0.5 text-gray-600 dark:text-gray-400 font-mono text-[10px]">
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $subtotal }}</code> — Subtotal</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $igv }}</code> — IGV</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $total }}</code> — Total</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $montoEnLetras }}</code> — Monto en Letras</li>
                                </ul>
                            </div>
                            <div>
                                <p class="font-bold text-gray-700 dark:text-gray-300 mb-1">📦 Ítems (dentro de @@forelse)</p>
                                <ul class="space-y-0.5 text-gray-600 dark:text-gray-400 font-mono text-[10px]">
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $item->producto->nombre }}</code> — Nombre Producto</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $item->producto->sku }}</code> — SKU / Código</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $item->cantidad }}</code> — Cantidad</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $item->unidad_medida }}</code> — Unidad de Medida</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $item->precio_venta_unit }}</code> — Precio Unitario</li>
                                    <li><code class="bg-slate-200 dark:bg-gray-700 px-1 rounded">@{{ $item->precio_venta_total }}</code> — Precio Total</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mt-2 p-2 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                            <p class="text-amber-800 dark:text-amber-300 text-[10px]"><strong>⚠️ Importante:</strong> El template usa DomPDF que NO soporta flexbox ni CSS grid. Usa <code>&lt;table&gt;</code> para layouts. Si el template queda vacío, se usará el template original del sistema.</p>
                        </div>
                    </div>

                    {{-- Editor de Código HTML --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            Código HTML/Blade de la Plantilla
                            <span class="font-normal text-gray-400 ml-1">(dejar vacío para usar la plantilla por defecto del sistema)</span>
                        </label>
                        <textarea name="pdf_template_cotizacion" id="pdf-template-editor" rows="20" spellcheck="false"
                                  class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-slate-900 text-green-400 px-4 py-3 text-xs font-mono resize-y leading-relaxed focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                  style="tab-size: 4; -moz-tab-size: 4;"
                                  placeholder="Dejar vacío para usar la plantilla por defecto del sistema...">{{ old('pdf_template_cotizacion', $configuracion->pdf_template_cotizacion) }}</textarea>
                        <div class="flex items-center justify-between mt-2">
                            <p class="text-[10px] text-gray-400">
                                <span id="template-line-count">0</span> líneas · HTML + Blade + CSS inline
                            </p>
                            <button type="button" id="btn-preview-template"
                                    class="px-3 py-1 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg text-[10px] font-semibold transition-colors">
                                👁️ Vista Previa (próximamente)
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 4. TASAS FINANCIERAS E IMPOSITIVAS --}}
                <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-6 space-y-4">
                    <div class="border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            Tasas Impositivas y Porcentajes de Comisión
                        </h3>
                        <p class="text-xs text-gray-500">Estos valores se precargan automáticamente al crear nuevos documentos. <strong class="text-amber-600 dark:text-amber-400">Cada documento puede modificarlos individualmente</strong> desde su formulario de creación/edición.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">IGV (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" name="pct_igv" value="{{ old('pct_igv', $configuracion->pct_igv) }}" required
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs font-mono font-bold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Retención (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" name="pct_retencion" value="{{ old('pct_retencion', $configuracion->pct_retencion) }}" required
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Comisión Venta (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" name="pct_comision_venta" value="{{ old('pct_comision_venta', $configuracion->pct_comision_venta) }}" required
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Comisión Ganancia (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" name="pct_comision_ganancia" value="{{ old('pct_comision_ganancia', $configuracion->pct_comision_ganancia) }}" required
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs font-mono">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-purple-primary to-indigo-600 hover:from-purple-light hover:to-indigo-500 text-white rounded-xl text-sm font-bold shadow-md shadow-purple-900/20 transition-all">
                        Guardar Toda la Configuración
                    </button>
                </div>
            </form>
        </div>
    </div>
    {{-- Hidden input with default template for restore functionality --}}
    <textarea id="default-template-source" class="hidden">{!! e($defaultTemplate) !!}</textarea>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editor = document.getElementById('pdf-template-editor');
            const lineCount = document.getElementById('template-line-count');
            const guidePanel = document.getElementById('template-guide');
            const btnGuide = document.getElementById('btn-toggle-guide');
            const btnRestore = document.getElementById('btn-restore-default');
            const defaultSource = document.getElementById('default-template-source');

            // Line counter
            function updateLineCount() {
                const lines = editor.value ? editor.value.split('\n').length : 0;
                lineCount.textContent = lines;
            }
            editor.addEventListener('input', updateLineCount);
            updateLineCount();

            // Tab key support in textarea
            editor.addEventListener('keydown', function(e) {
                if (e.key === 'Tab') {
                    e.preventDefault();
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                    this.selectionStart = this.selectionEnd = start + 4;
                    updateLineCount();
                }
            });

            // Toggle guide
            btnGuide.addEventListener('click', function () {
                guidePanel.classList.toggle('hidden');
                this.textContent = guidePanel.classList.contains('hidden')
                    ? '📖 Ver Variables Disponibles'
                    : '📖 Ocultar Variables';
            });

            // Restore default
            btnRestore.addEventListener('click', function () {
                if (confirm('¿Restaurar la plantilla original del sistema? Se perderán los cambios personalizados.')) {
                    editor.value = defaultSource.value;
                    updateLineCount();
                }
            });
        });
    </script>
</x-app-layout>
