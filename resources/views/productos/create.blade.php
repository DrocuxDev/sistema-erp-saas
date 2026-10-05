<x-app-layout>
    <div class="py-6">
        <div class="page-container">
            <!-- Encabezado con navegación -->
            <div class="mb-6">
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <a href="{{ route('productos.index') }}" class="flex items-center hover:text-cyan-500 dark:hover:text-cyan-300 transition-colors">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Productos
                    </a>
                    <span class="mx-2">/</span>
                    <span class="text-gray-900 dark:text-white font-semibold">Nuevo</span>
                </div>
            </div>

            <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <x-ui-card data-ui-tabs>
                    <!-- Área superior: título + thumbnail + flags -->
                    <div class="p-6 border-b border-gray-200 dark:border-white/10">
                        <div class="flex items-start justify-between gap-6">
                            <div class="flex-1 min-w-0">
                                <x-ui-title-input name="nombre" placeholder="Nombre del producto" :required="true" :autofocus="true" :maxlength="200" />
                            </div>

                            <x-ui-image-input name="imagen" />
                        </div>

                        <!-- Checkboxes con acento cyan -->
                        <div class="flex flex-wrap gap-6 mt-4">
                            <x-ui-check name="puede_venderse" label="Puede venderse" :checked="old('puede_venderse', true)" />
                            <x-ui-check name="puede_comprarse" label="Puede comprarse" :checked="old('puede_comprarse', true)" />
                        </div>
                    </div>

                    <!-- Pestañas: uppercase, cyan underline activo -->
                    <div class="border-b border-gray-200 dark:border-white/10">
                        <nav class="flex -mb-px px-2">
                            <x-ui-tab-button tab="general" label="General" :active="true" />
                            <x-ui-tab-button tab="inventario" label="Inventario" />
                        </nav>
                    </div>

                    <!-- Contenido de pestañas -->
                    <div class="p-6">
                        <!-- Tab: General -->
                        <x-ui-tab-panel tab="general" :active="true">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <label for="sku" class="ui-label">
                                        SKU / Código <span class="text-rose-500">*</span>
                                    </label>
                                    <input id="sku" type="text" name="sku" value="{{ old('sku') }}" placeholder="Ej: PROD-001" required maxlength="50" class="ui-input" />
                                    <x-input-error :messages="$errors->get('sku')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="categoria" class="ui-label">Categoría</label>
                                    <input id="categoria" type="text" name="categoria" value="{{ old('categoria') }}" placeholder="Ej: Materiales, Servicios" maxlength="100" class="ui-input" />
                                    <x-input-error :messages="$errors->get('categoria')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="unidad_medida" class="ui-label">
                                        Unidad de medida <span class="text-rose-500">*</span>
                                    </label>
                                    <select id="unidad_medida" name="unidad_medida" required class="ui-input">
                                        <option value="UND" {{ old('unidad_medida', 'UND') == 'UND' ? 'selected' : '' }}>UND - Unidad</option>
                                        <option value="KG" {{ old('unidad_medida') == 'KG' ? 'selected' : '' }}>KG - Kilogramo</option>
                                        <option value="LT" {{ old('unidad_medida') == 'LT' ? 'selected' : '' }}>LT - Litro</option>
                                        <option value="MT" {{ old('unidad_medida') == 'MT' ? 'selected' : '' }}>MT - Metro</option>
                                        <option value="CJ" {{ old('unidad_medida') == 'CJ' ? 'selected' : '' }}>CJ - Caja</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('unidad_medida')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="tipo_producto" class="ui-label">Tipo de producto</label>
                                    <select id="tipo_producto" name="tipo_producto" class="ui-input">
                                        <option value="">Seleccionar...</option>
                                        @foreach($listas['tipos_producto'] as $t)
                                            <option value="{{ $t }}" {{ old('tipo_producto') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('tipo_producto')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="tipo_existencia" class="ui-label">Tipo de existencia</label>
                                    <select id="tipo_existencia" name="tipo_existencia" class="ui-input">
                                        <option value="">Seleccionar...</option>
                                        @foreach($listas['tipos_existencia'] as $t)
                                            <option value="{{ $t }}" {{ old('tipo_existencia') === $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('tipo_existencia')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="precio_compra" class="ui-label">Precio compra (referencial)</label>
                                    <input id="precio_compra" type="number" name="precio_compra" value="{{ old('precio_compra') }}" min="0" step="0.01" placeholder="0.00" class="ui-input" />
                                    <x-input-error :messages="$errors->get('precio_compra')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="precio_venta" class="ui-label">Precio venta (referencial)</label>
                                    <input id="precio_venta" type="number" name="precio_venta" value="{{ old('precio_venta') }}" min="0" step="0.01" placeholder="0.00" class="ui-input" />
                                    <x-input-error :messages="$errors->get('precio_venta')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="marca" class="ui-label">Marca</label>
                                    <input id="marca" type="text" name="marca" value="{{ old('marca') }}" maxlength="100" placeholder="Escribe o elige..." list="datalist-marcas" class="ui-input" />
                                    <datalist id="datalist-marcas">
                                        @foreach($marcas as $m)
                                            <option value="{{ $m }}"></option>
                                        @endforeach
                                    </datalist>
                                    <x-input-error :messages="$errors->get('marca')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="modelo" class="ui-label">Modelo</label>
                                    <input id="modelo" type="text" name="modelo" value="{{ old('modelo') }}" maxlength="100" placeholder="Escribe o elige..." list="datalist-modelos" class="ui-input" />
                                    <datalist id="datalist-modelos">
                                        @foreach($modelos as $m)
                                            <option value="{{ $m }}"></option>
                                        @endforeach
                                    </datalist>
                                    <x-input-error :messages="$errors->get('modelo')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="condicion" class="ui-label">Condición</label>
                                    <select id="condicion" name="condicion" class="ui-input">
                                        <option value="">Seleccionar...</option>
                                        @foreach($listas['condiciones'] as $c)
                                            <option value="{{ $c }}" {{ old('condicion') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('condicion')" class="mt-1" />
                                </div>

                                <div class="md:col-span-2">
                                    <label for="descripcion" class="ui-label">Descripción</label>
                                    <textarea id="descripcion" name="descripcion" rows="3" maxlength="255" placeholder="Descripción detallada del producto..." class="ui-input">{{ old('descripcion') }}</textarea>
                                    <x-input-error :messages="$errors->get('descripcion')" class="mt-1" />
                                </div>
                            </div>
                        </x-ui-tab-panel>

                        <!-- Tab: Inventario -->
                        <x-ui-tab-panel tab="inventario">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <label for="stock_actual" class="ui-label">
                                        Existencias actuales <span class="text-rose-500">*</span>
                                    </label>
                                    <input id="stock_actual" type="number" name="stock_actual" value="{{ old('stock_actual', 0) }}" min="0" step="0.01" required class="ui-input" />
                                    <x-input-error :messages="$errors->get('stock_actual')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="stock_minimo" class="ui-label">
                                        Existencias mínimas <span class="text-rose-500">*</span>
                                    </label>
                                    <input id="stock_minimo" type="number" name="stock_minimo" value="{{ old('stock_minimo', 0) }}" min="0" step="0.01" required class="ui-input" />
                                    <x-input-error :messages="$errors->get('stock_minimo')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="stock_maximo" class="ui-label">Existencias máximas</label>
                                    <input id="stock_maximo" type="number" name="stock_maximo" value="{{ old('stock_maximo') }}" min="0" step="0.01" placeholder="0.00" class="ui-input" />
                                    <x-input-error :messages="$errors->get('stock_maximo')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="peso_bruto" class="ui-label">Peso bruto (kg)</label>
                                    <input id="peso_bruto" type="number" name="peso_bruto" value="{{ old('peso_bruto') }}" min="0" step="0.001" placeholder="0.000" class="ui-input" />
                                    <x-input-error :messages="$errors->get('peso_bruto')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="seguimiento" class="ui-label">Seguimiento</label>
                                    <select id="seguimiento" name="seguimiento" class="ui-input">
                                        <option value="">Seleccionar...</option>
                                        @foreach($listas['seguimientos'] as $s)
                                            <option value="{{ $s }}" {{ old('seguimiento') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('seguimiento')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="envase" class="ui-label">Envase</label>
                                    <select id="envase" name="envase" class="ui-input">
                                        <option value="">Seleccionar...</option>
                                        @foreach($listas['envases'] as $e)
                                            <option value="{{ $e }}" {{ old('envase') === $e ? 'selected' : '' }}>{{ ucfirst($e) }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('envase')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="tipo_area_produccion" class="ui-label">Tipo área producción</label>
                                    <select id="tipo_area_produccion" name="tipo_area_produccion" class="ui-input">
                                        <option value="">Seleccionar...</option>
                                        @foreach($listas['tipos_area'] as $t)
                                            <option value="{{ $t }}" {{ old('tipo_area_produccion') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('tipo_area_produccion')" class="mt-1" />
                                </div>

                                <div class="flex items-end pb-1">
                                    <input type="hidden" name="se_fabrica" value="0" />
                                    <x-ui-check name="se_fabrica" label="Se fabrica (producción interna)" :checked="old('se_fabrica', false)" />
                                </div>
                            </div>
                        </x-ui-tab-panel>
                    </div>

                    <!-- Botones de acción -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-white/[0.02] border-t border-gray-200 dark:border-white/10 flex flex-wrap justify-start gap-3">
                        <button type="submit" class="btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Guardar
                        </button>
                        <a href="{{ route('productos.index') }}" class="btn-outline">
                            Cancelar
                        </a>
                    </div>
                </x-ui-card>
            </form>
        </div>
    </div>
</x-app-layout>
