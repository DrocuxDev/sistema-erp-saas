<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('comprobantes.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">Volver</a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Nuevo Comprobante</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Puede emitir desde una venta directa, sin cotización ni orden. Serie y correlativo automáticos.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="page-container">
            <form action="{{ route('comprobantes.store') }}" method="POST" class="bg-white dark:bg-card-bg rounded-xl shadow p-6 space-y-6">
                @csrf

                @if($errors->any())
                    <div class="p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-xl text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="form-label">Venta origen *</label>
                    <select name="venta_id" required class="form-select">
                        <option value="">Seleccionar venta sin comprobante...</option>
                        @foreach($ventas as $v)
                            <option value="{{ $v->id }}" {{ (string) old('venta_id', $ventaPreseleccionada?->id) === (string) $v->id ? 'selected' : '' }}>
                                {{ $v->codigo_formateado }} — {{ $v->cliente->razon_social ?? $v->cliente->nombre }} — {{ $v->cotizacion_id || $v->orden_compra_cliente_id ? 'Con documento origen' : 'Sin cotización ni orden' }} — S/ {{ number_format($v->total, 2) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Solo se listan ventas sin comprobante emitido. <a href="{{ route('ventas.create') }}" class="underline">Registrar una venta directa</a> si no existe cotización u orden.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div><label class="form-label">Precio unitario</label><select name="precio_incluye_igv" class="form-select"><option value="">Heredar de venta</option><option value="0" @selected(old('precio_incluye_igv') === '0')>Sin IGV</option><option value="1" @selected(old('precio_incluye_igv') === '1')>Incluye IGV</option></select></div>
                    <div><label class="form-label">IGV %</label><input name="pct_igv" type="number" step="0.01" min="0" max="100" value="{{ old('pct_igv') }}" class="form-select" placeholder="Heredar"></div>
                    <div><label class="form-label">Retención %</label><input name="pct_retencion" type="number" step="0.01" min="0" max="100" value="{{ old('pct_retencion') }}" class="form-select" placeholder="Heredar"></div>
                    <div><label class="form-label">Motivo de excepción</label><input name="motivo_excepcion_tributaria" value="{{ old('motivo_excepcion_tributaria') }}" class="form-select" placeholder="Obligatorio si cambia condiciones"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Tipo de comprobante *</label>
                        <select name="tipo" required class="form-select">
                            @foreach($tipos as $key => $label)
                                <option value="{{ $key }}" {{ old('tipo') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Fecha de emisión *</label>
                        <input type="date" name="fecha_emision" value="{{ old('fecha_emision', date('Y-m-d')) }}" required class="form-select">
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('comprobantes.index') }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">Emitir comprobante</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
