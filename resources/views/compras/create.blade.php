@php
    $productosJson = $productos->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre, 'unidad' => $p->unidad_medida])->values();
    $lineasIniciales = old('lineas');
    if (! $lineasIniciales && $lineasPrecargadas->isNotEmpty()) {
        $lineasIniciales = $lineasPrecargadas->map(fn($d) => ['producto_id' => $d->producto_id, 'cantidad' => $d->cantidad, 'precio_compra_unit' => $d->precio_compra_unit])->values()->all();
    }
    if (! $lineasIniciales) $lineasIniciales = [['producto_id' => '', 'cantidad' => 1, 'precio_compra_unit' => '']];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('compras.index') }}" class="text-gray-500 hover:text-gray-700">Volver</a>
            <div><h2 class="text-2xl font-bold">Nueva Recepción de Compra</h2><p class="text-sm text-gray-500 mt-1">@if($ordenCompra) Desde O/C {{ $ordenCompra->codigo_formateado }} — {{ $ordenCompra->proveedor->nombre }} @else Compra directa o desde O/C aprobada @endif</p></div>
        </div>
    </x-slot>

    <div class="page-container py-6 sm:py-8">
        <form action="{{ route('compras.store') }}" method="POST" class="bg-white dark:bg-card-bg rounded-xl shadow p-6 space-y-6">
            @csrf
            @if($ordenCompra)<input type="hidden" name="orden_compra_id" value="{{ $ordenCompra->id }}">@endif
            @if($errors->any())<div class="p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-xl text-sm"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Proveedor *</label>
                    <select name="proveedor_id" required {{ $ordenCompra ? 'disabled' : '' }} class="form-select">
                        <option value="">Seleccionar...</option>
                        @foreach($proveedores as $p)<option value="{{ $p->id }}" {{ (string) $proveedorPreseleccionado === (string) $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>@endforeach
                    </select>
                    @if($ordenCompra)<input type="hidden" name="proveedor_id" value="{{ $proveedorPreseleccionado }}">@endif
                </div>
                <div>
                    <label class="form-label">O/C origen</label>
                    @if($ordenCompra)
                        <input type="text" value="{{ $ordenCompra->codigo_formateado }}" disabled class="form-input bg-gray-50 dark:bg-darker-bg">
                    @else
                        <select id="orden_compra_select" class="form-select">
                            <option value="">Compra directa (sin O/C)...</option>
                            @foreach($ordenesAprobadas as $oc)<option value="{{ $oc->id }}">{{ $oc->codigo_formateado }} — {{ $oc->proveedor->nombre }}</option>@endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Al elegir una O/C se precarga el proveedor y las líneas.</p>
                    @endif
                </div>
                <div><label class="form-label">Fecha *</label><input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required class="form-select"></div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-3"><h3 class="font-bold">Líneas de compra</h3><button type="button" id="btn-agregar" class="px-3 py-1.5 bg-cyan-600 text-white rounded-lg text-sm">+ Agregar línea</button></div>
                <div class="overflow-x-auto"><table class="w-full text-sm" id="tabla-lineas">
                    <thead class="bg-gray-50 dark:bg-darker-bg"><tr><th class="px-2 py-2 text-left">Producto</th><th class="px-2 py-2">Cant.</th><th class="px-2 py-2">P. Compra</th><th class="px-2 py-2 text-right">Subtotal</th><th></th></tr></thead>
                    <tbody>
                        @foreach($lineasIniciales as $i => $l)
                            <tr class="linea border-t">
                                <td class="px-2 py-2"><select name="lineas[{{ $i }}][producto_id]" required class="prod-select w-full rounded-lg border px-2 py-1.5 text-sm dark:bg-darker-bg"><option value="">Seleccionar...</option>@foreach($productos as $p)<option value="{{ $p->id }}" {{ (string) ($l['producto_id'] ?? '') === (string) $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>@endforeach</select></td>
                                <td class="px-2 py-2"><input type="number" name="lineas[{{ $i }}][cantidad]" value="{{ $l['cantidad'] ?? 1 }}" min="0.01" step="0.01" required class="cant-input w-24 rounded-lg border px-2 py-1.5 text-sm dark:bg-darker-bg"></td>
                                <td class="px-2 py-2"><input type="number" name="lineas[{{ $i }}][precio_compra_unit]" value="{{ $l['precio_compra_unit'] ?? '' }}" min="0" step="0.01" required class="precio-input w-28 rounded-lg border px-2 py-1.5 text-sm dark:bg-darker-bg"></td>
                                <td class="px-2 py-2 text-right font-mono subtotal-cell">0.00</td>
                                <td class="px-2 py-2"><button type="button" class="btn-quitar text-rose-500 text-xs">Quitar</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table></div>
                <div class="flex justify-end mt-3"><div class="text-sm font-bold">Total: S/ <span id="total-general" class="font-mono">0.00</span></div></div>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('compras.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Confirmar recepción</button>
            </div>
        </form>
    </div>

    <script>
        const productosOptions = `{!! $productos->map(fn($p) => '<option value=\"'.$p->id.'\">'.e($p->nombre).'</option>')->implode('') !!}`;
        let idx = {{ count($lineasIniciales) }};
        const selOC = document.getElementById('orden_compra_select');
        if (selOC) selOC.addEventListener('change', () => { if (selOC.value) window.location.href = "{{ route('compras.create') }}?orden_compra_id=" + selOC.value; });

        function recalcular() {
            let total = 0;
            document.querySelectorAll('#tabla-lineas .linea').forEach(tr => {
                const cant = parseFloat(tr.querySelector('.cant-input').value) || 0;
                const precio = parseFloat(tr.querySelector('.precio-input').value) || 0;
                const sub = cant * precio;
                tr.querySelector('.subtotal-cell').textContent = sub.toFixed(2);
                total += sub;
            });
            document.getElementById('total-general').textContent = total.toFixed(2);
        }
        document.addEventListener('input', e => { if (e.target.matches('.cant-input,.precio-input')) recalcular(); });
        document.getElementById('btn-agregar').addEventListener('click', () => {
            const tbody = document.querySelector('#tabla-lineas tbody');
            const tr = document.createElement('tr');
            tr.className = 'linea border-t';
            tr.innerHTML = `<td class="px-2 py-2"><select name="lineas[${idx}][producto_id]" required class="prod-select w-full rounded-lg border px-2 py-1.5 text-sm dark:bg-darker-bg"><option value="">Seleccionar...</option>${productosOptions}</select></td><td class="px-2 py-2"><input type="number" name="lineas[${idx}][cantidad]" value="1" min="0.01" step="0.01" required class="cant-input w-24 rounded-lg border px-2 py-1.5 text-sm dark:bg-darker-bg"></td><td class="px-2 py-2"><input type="number" name="lineas[${idx}][precio_compra_unit]" value="" min="0" step="0.01" required class="precio-input w-28 rounded-lg border px-2 py-1.5 text-sm dark:bg-darker-bg"></td><td class="px-2 py-2 text-right font-mono subtotal-cell">0.00</td><td class="px-2 py-2"><button type="button" class="btn-quitar text-rose-500 text-xs">Quitar</button></td>`;
            tbody.appendChild(tr); idx++; recalcular();
        });
        document.addEventListener('click', e => { if (e.target.classList.contains('btn-quitar')) { if (document.querySelectorAll('#tabla-lineas .linea').length > 1) { e.target.closest('tr').remove(); recalcular(); } } });
        recalcular();
    </script>
</x-app-layout>
