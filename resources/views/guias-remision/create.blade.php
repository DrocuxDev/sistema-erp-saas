@php
    $lineasIniciales = old('lineas');
    if (! $lineasIniciales && $lineasPrecargadas->isNotEmpty()) {
        $lineasIniciales = $lineasPrecargadas->map(fn($l) => [
            'producto_id' => $l->producto_id,
            'cantidad' => $l->cantidad,
            'unidad_medida' => $l->unidad_medida ?? 'UND',
        ])->values()->all();
    }
    if (! $lineasIniciales) {
        $lineasIniciales = [['producto_id' => '', 'cantidad' => 1, 'unidad_medida' => 'UND']];
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('guias-remision.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">Volver</a>
            <div>
                <h2 class="page-title">Nueva Guía de Remisión</h2>
                <p class="page-subtitle">Serie T001 automática. Precarga ítems desde la venta si la seleccionas.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="page-container">
            <form action="{{ route('guias-remision.store') }}" method="POST" class="form-card">
                @csrf
                @if($errors->any())
                    <div class="p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-xl text-sm">
                        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <section>
                    <h3 class="form-section-title">Documento y traslado</h3>
                    <div class="form-grid-3 mt-4">
                        <div>
                            <label class="form-label" for="venta_id">Venta origen (opcional)</label>
                            <select id="venta_id" name="venta_id" class="form-select">
                                <option value="">Traslado sin venta...</option>
                                @foreach($ventas as $v)
                                    <option value="{{ $v->id }}" {{ (string) old('venta_id', $ventaPreseleccionada?->id) === (string) $v->id ? 'selected' : '' }}>{{ $v->codigo_formateado }} — {{ $v->cliente->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="fecha_traslado">Fecha traslado *</label>
                            <input id="fecha_traslado" type="date" name="fecha_traslado" value="{{ old('fecha_traslado', date('Y-m-d')) }}" required class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="motivo_traslado">Motivo *</label>
                            <select id="motivo_traslado" name="motivo_traslado" required class="form-select">
                                <option value="venta" {{ old('motivo_traslado', 'venta') === 'venta' ? 'selected' : '' }}>Venta</option>
                                <option value="compra" {{ old('motivo_traslado') === 'compra' ? 'selected' : '' }}>Compra</option>
                                <option value="traslado_interno" {{ old('motivo_traslado') === 'traslado_interno' ? 'selected' : '' }}>Traslado interno</option>
                                <option value="otro" {{ old('motivo_traslado') === 'otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>
                    </div>
                </section>

                <section>
                    <h3 class="form-section-title">Ruta y transporte</h3>
                    <div class="form-grid-2 mt-4">
                        <div><label class="form-label" for="punto_partida">Punto de partida</label><input id="punto_partida" type="text" name="punto_partida" value="{{ old('punto_partida', 'Almacén Central JHARDSYSTEX') }}" maxlength="255" class="form-input"></div>
                        <div><label class="form-label" for="punto_llegada">Punto de llegada</label><input id="punto_llegada" type="text" name="punto_llegada" value="{{ old('punto_llegada') }}" maxlength="255" placeholder="Dirección del cliente / destino" class="form-input"></div>
                        <div><label class="form-label" for="transportista">Transportista</label><input id="transportista" type="text" name="transportista" value="{{ old('transportista') }}" maxlength="150" class="form-input"></div>
                        <div><label class="form-label" for="placa_vehiculo">Placa vehículo</label><input id="placa_vehiculo" type="text" name="placa_vehiculo" value="{{ old('placa_vehiculo') }}" maxlength="20" class="form-input"></div>
                    </div>
                </section>

                <section>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="form-section-title !border-0 !pb-0">Bienes a trasladar</h3>
                        <button type="button" id="btn-agregar" class="btn-primary !min-h-[36px] !px-3 !text-xs">+ Agregar línea</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" id="tabla-lineas">
                            <thead class="bg-gray-50 dark:bg-darker-bg"><tr><th class="px-2 py-2 text-left">Producto</th><th class="px-2 py-2">Cant.</th><th class="px-2 py-2">U.M.</th><th class="px-2 py-2"></th></tr></thead>
                            <tbody>
                                @foreach($lineasIniciales as $i => $linea)
                                    <tr class="linea border-t">
                                        <td class="px-2 py-2"><select name="lineas[{{ $i }}][producto_id]" required class="w-full rounded-lg border px-2 py-1.5 text-sm dark:bg-darker-bg"><option value="">Seleccionar...</option>@foreach($productos as $p)<option value="{{ $p->id }}" {{ (string) ($linea['producto_id'] ?? '') === (string) $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>@endforeach</select></td>
                                        <td class="px-2 py-2"><input type="number" name="lineas[{{ $i }}][cantidad]" value="{{ $linea['cantidad'] ?? 1 }}" min="0.01" step="0.01" required class="w-24 rounded-lg border px-2 py-1.5 text-sm dark:bg-darker-bg"></td>
                                        <td class="px-2 py-2"><input type="text" name="lineas[{{ $i }}][unidad_medida]" value="{{ $linea['unidad_medida'] ?? 'UND' }}" maxlength="20" class="w-24 rounded-lg border px-2 py-1.5 text-sm dark:bg-darker-bg"></td>
                                        <td class="px-2 py-2"><button type="button" class="btn-quitar text-rose-500 text-xs">Quitar</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                </section>

                <div class="flex flex-wrap justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                    <a href="{{ route('guias-remision.index') }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">Emitir guía</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const productosOptions = `{!! collect($productos)->map(fn($p) => '<option value=\"'.$p->id.'\">'.e($p->nombre).'</option>')->implode('') !!}`;
        let idx = {{ count($lineasIniciales) }};
        document.getElementById('btn-agregar').addEventListener('click', () => {
            const tbody = document.querySelector('#tabla-lineas tbody');
            const tr = document.createElement('tr');
            tr.className = 'linea border-t';
            tr.innerHTML = `<td class="px-2 py-2"><select name="lineas[${idx}][producto_id]" required class="w-full rounded-lg border px-2 py-1.5 text-sm dark:bg-darker-bg"><option value="">Seleccionar...</option>${productosOptions}</select></td><td class="px-2 py-2"><input type="number" name="lineas[${idx}][cantidad]" value="1" min="0.01" step="0.01" required class="w-24 rounded-lg border px-2 py-1.5 text-sm dark:bg-darker-bg"></td><td class="px-2 py-2"><input type="text" name="lineas[${idx}][unidad_medida]" value="UND" maxlength="20" class="w-24 rounded-lg border px-2 py-1.5 text-sm dark:bg-darker-bg"></td><td class="px-2 py-2"><button type="button" class="btn-quitar text-rose-500 text-xs">Quitar</button></td>`;
            tbody.appendChild(tr);
            idx++;
        });
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-quitar')) {
                if (document.querySelectorAll('#tabla-lineas .linea').length > 1) e.target.closest('tr').remove();
            }
        });
    </script>
</x-app-layout>
