<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <span>Administración</span>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Configuración</span>
                </div>
                <h2 class="font-bold text-2xl text-gray-900 dark:text-white flex items-center gap-2.5">
                    Configuración General del Sistema
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Parámetros de la empresa emisora, RUC, cuentas bancarias, tasas de IGV y comisiones comerciales.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="page-container space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-2xl text-green-800 dark:text-green-300 font-semibold text-sm">
                    ✓ {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('configuracion.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6" data-dirty-check>
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
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-600 dark:bg-cyan-500"></span>
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

                {{-- 3. APARTADO "COTIZACIÓN": PORTADA, IDENTIDAD Y TEXTOS DEL PDF --}}
                <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-6 space-y-6">
                    <div class="border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-violet-500"></span>
                            Cotización: portada, identidad y textos del PDF
                        </h3>
                        <p class="text-xs text-gray-500">Todo el contenido del PDF sale de esta configuración. Las cotizaciones ya emitidas conservan la configuración que tenían al emitirse.</p>
                    </div>

                    {{-- A. Portada --}}
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-300">A. Portada (página 1 del PDF)</h4>
                        <div class="flex flex-col sm:flex-row gap-4 sm:items-start">
                            <div class="space-y-2">
                                @if($configuracion->portada_imagen_path)
                                    <img src="{{ asset('storage/'.$configuracion->portada_imagen_path) }}" alt="Portada actual"
                                         class="w-24 rounded-lg border border-gray-300 dark:border-gray-600 object-cover" style="aspect-ratio: 1/1.4142;">
                                    <form action="{{ route('configuracion.portada.destroy') }}" method="POST" onsubmit="return confirm('¿Borrar la imagen de portada? Las cotizaciones nuevas ya no tendrán portada.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-24 px-2 py-1.5 rounded-lg bg-red-100 dark:bg-red-950/40 text-red-600 dark:text-red-400 text-[11px] font-bold hover:bg-red-200 dark:hover:bg-red-900/40 transition-colors">Borrar portada</button>
                                    </form>
                                @else
                                    <div class="w-24 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg flex items-center justify-center text-[10px] text-gray-400 text-center px-2" style="aspect-ratio: 1/1.4142;">Sin portada</div>
                                @endif
                            </div>
                            <div class="flex-1 space-y-2">
                                <input type="file" name="portada" accept=".jpg,.jpeg,.png"
                                       class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs">
                                <p class="text-[11px] text-gray-500">JPG o PNG, máx. 5 MB, proporción A4 vertical (más alta que ancha).</p>
                                @error('portada')<p class="text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
                                <div class="flex flex-wrap items-center gap-3">
                                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
                                        <input type="checkbox" name="incluir_portada" value="1" {{ old('incluir_portada', $configuracion->incluir_portada) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-emerald-600 w-4 h-4">
                                        Incluir portada en las cotizaciones
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- B. Identidad --}}
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-300">B. Identidad (logo y colores del PDF)</h4>
                        <div class="flex flex-col sm:flex-row gap-4 sm:items-start">
                            <div class="space-y-2">
                                @if($configuracion->logo_path)
                                    <img src="{{ asset('storage/'.$configuracion->logo_path) }}" alt="Logo actual"
                                         class="h-14 max-w-[160px] object-contain rounded-lg border border-gray-300 dark:border-gray-600 bg-white px-2 py-1">
                                    <form action="{{ route('configuracion.logo.destroy') }}" method="POST" onsubmit="return confirm('¿Borrar el logo? Las cotizaciones nuevas saldrán sin logo en el encabezado.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full px-2 py-1.5 rounded-lg bg-red-100 dark:bg-red-950/40 text-red-600 dark:text-red-400 text-[11px] font-bold hover:bg-red-200 dark:hover:bg-red-900/40 transition-colors">Borrar logo</button>
                                    </form>
                                @else
                                    <div class="h-14 w-40 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg flex items-center justify-center text-[10px] text-gray-400">Sin logo</div>
                                @endif
                            </div>
                            <div class="flex-1 space-y-2">
                                <input type="file" name="logo" accept=".jpg,.jpeg,.png"
                                       class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs">
                                <p class="text-[11px] text-gray-500">Logo del encabezado. JPG o PNG, máx. 2 MB.</p>
                                @error('logo')<p class="text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Color principal (franja, totales, recuadros)</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="color_principal" value="{{ old('color_principal', $configuracion->color_principal ?: '#0076A8') }}"
                                           class="w-10 h-9 rounded border border-gray-300 dark:border-gray-600 bg-white p-0.5">
                                    <input type="text" value="{{ old('color_principal', $configuracion->color_principal ?: '#0076A8') }}" readonly
                                           class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-darker-bg px-3 py-2 text-xs font-mono">
                                </div>
                                @error('color_principal')<p class="text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Color del encabezado de la tabla (negro por defecto)</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="color_tabla_encabezado" value="{{ old('color_tabla_encabezado', $configuracion->color_tabla_encabezado ?: '#111111') }}"
                                           class="w-10 h-9 rounded border border-gray-300 dark:border-gray-600 bg-white p-0.5">
                                    <input type="text" value="{{ old('color_tabla_encabezado', $configuracion->color_tabla_encabezado ?: '#111111') }}" readonly
                                           class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-darker-bg px-3 py-2 text-xs font-mono">
                                </div>
                                @error('color_tabla_encabezado')<p class="text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- C. Textos predefinidos --}}
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-300">C. Textos predefinidos del PDF</h4>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Texto de saludo (variables: {empresa}, {cliente})</label>
                            <textarea name="texto_saludo" rows="3"
                                      class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs resize-none">{{ old('texto_saludo', $configuracion->texto_saludo ?: 'Reciban un saludo cordial por parte de {empresa}, nos dirigimos hacia usted a fin de presentarle la cotización solicitada. Estaremos a su disposición por cualquier duda o consulta.') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Condiciones adicionales por defecto (se precargan en cada cotización, editables por documento)</label>
                            <textarea name="condiciones_adicionales_default" rows="2" placeholder="Ej: Stock sujeto a disponibilidad. Garantía de 12 meses por defectos de fabricación."
                                      class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs resize-none">{{ old('condiciones_adicionales_default', $configuracion->condiciones_adicionales_default) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Texto de firma (variable: {empresa})</label>
                            <textarea name="texto_firma" rows="3"
                                      class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs resize-none">{{ old('texto_firma', $configuracion->texto_firma ?: "Atentamente,\nEjecutivo de Ventas,\n{empresa}") }}</textarea>
                        </div>
                    </div>

                    {{-- D (titular). Las cuentas se gestionan en la sección siguiente. --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Titular de las cuentas bancarias (se define una vez)</label>
                        <input type="text" name="titular_cuenta" value="{{ old('titular_cuenta', $configuracion->titular_cuenta) }}"
                               placeholder="Ej: JHARDSYSTEX S.A.C."
                               class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg text-gray-900 dark:text-white px-3 py-2 text-xs">
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

                <div class="flex flex-col sm:flex-row justify-end gap-2 pt-2">
                    <a href="{{ route('configuracion.pdf-ejemplo') }}" target="_blank" rel="noopener"
                       class="px-6 py-2.5 rounded-xl text-xs font-bold border border-violet-300 dark:border-violet-700 text-violet-700 dark:text-violet-300 hover:bg-violet-50 dark:hover:bg-violet-950/30 transition-colors text-center">
                        Ver PDF de ejemplo
                    </a>
                    <button type="submit"
                            class="btn-primary px-6 py-2.5">
                        Guardar Toda la Configuración
                    </button>
                </div>
            </form>

            {{-- D. CUENTAS BANCARIAS (lista estructurada para el PDF) --}}
            <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-6 space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700/60 pb-3">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        Cuentas bancarias del PDF
                    </h3>
                    <p class="text-xs text-gray-500">Banco, moneda, cuenta y CCI. Marca cuáles se muestran en el PDF. El titular se define una vez en la sección anterior.</p>
                </div>

                @foreach($cuentas as $cuenta)
                    <form id="cuenta-update-{{ $cuenta->id }}" action="{{ route('configuracion.cuentas.update', $cuenta) }}" method="POST">
                        @csrf
                        @method('PUT')
                    </form>
                @endforeach
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 dark:bg-darker-bg text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="px-3 py-2">Banco</th>
                                <th class="px-3 py-2">Moneda</th>
                                <th class="px-3 py-2">N° de cuenta</th>
                                <th class="px-3 py-2">CCI</th>
                                <th class="px-3 py-2 text-center">En PDF</th>
                                <th class="px-3 py-2 text-center">Orden</th>
                                <th class="px-3 py-2 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700/60">
                            @forelse($cuentas as $cuenta)
                                <tr>
                                    <td class="px-2 py-1.5"><input type="text" form="cuenta-update-{{ $cuenta->id }}" name="banco" value="{{ $cuenta->banco }}" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1 text-xs"></td>
                                    <td class="px-2 py-1.5"><input type="text" form="cuenta-update-{{ $cuenta->id }}" name="moneda" value="{{ $cuenta->moneda }}" required class="w-20 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1 text-xs"></td>
                                    <td class="px-2 py-1.5"><input type="text" form="cuenta-update-{{ $cuenta->id }}" name="numero_cuenta" value="{{ $cuenta->numero_cuenta }}" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1 text-xs font-mono"></td>
                                    <td class="px-2 py-1.5"><input type="text" form="cuenta-update-{{ $cuenta->id }}" name="cci" value="{{ $cuenta->cci }}" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1 text-xs font-mono"></td>
                                    <td class="px-2 py-1.5 text-center"><input type="checkbox" form="cuenta-update-{{ $cuenta->id }}" name="mostrar_en_pdf" value="1" {{ $cuenta->mostrar_en_pdf ? 'checked' : '' }} class="rounded border-gray-300 w-4 h-4"></td>
                                    <td class="px-2 py-1.5 text-center"><input type="number" form="cuenta-update-{{ $cuenta->id }}" name="orden" value="{{ $cuenta->orden }}" min="0" max="999" class="w-14 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-1 py-1 text-xs text-center"></td>
                                    <td class="px-2 py-1.5 text-center whitespace-nowrap">
                                        <button type="submit" form="cuenta-update-{{ $cuenta->id }}" class="px-2 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold">Guardar</button>
                                        <form action="{{ route('configuracion.cuentas.destroy', $cuenta) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta cuenta bancaria?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 rounded-lg bg-red-100 dark:bg-red-950/40 text-red-600 dark:text-red-400 text-[11px] font-bold">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-3 text-center text-gray-400">Sin cuentas registradas. Agrega la primera abajo.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <form action="{{ route('configuracion.cuentas.store') }}" method="POST" class="grid grid-cols-2 sm:grid-cols-7 gap-2 items-end bg-gray-50 dark:bg-darker-bg/50 rounded-xl p-3">
                    @csrf
                    <div><label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 mb-1">Banco *</label><input type="text" name="banco" required placeholder="BCP" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1.5 text-xs"></div>
                    <div><label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 mb-1">Moneda *</label><input type="text" name="moneda" value="Soles" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1.5 text-xs"></div>
                    <div class="col-span-2"><label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 mb-1">N° de cuenta *</label><input type="text" name="numero_cuenta" required placeholder="193-XXXXXXX" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1.5 text-xs font-mono"></div>
                    <div class="col-span-2"><label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 mb-1">CCI</label><input type="text" name="cci" placeholder="002-XXXXXXX" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-darker-bg px-2 py-1.5 text-xs font-mono"></div>
                    <div class="col-span-2 sm:col-span-1">
                        <button type="submit" class="w-full px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">+ Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
