<x-app-layout>
    <div class="page-container py-4 sm:py-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('comprobantes.index') }}" class="hover:text-cyan-600">Comprobantes</a><span>/</span><span class="text-gray-900 dark:text-white">{{ $comprobante->codigo_formateado }}</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="font-mono text-cyan-600 dark:text-cyan-400">{{ $comprobante->codigo_formateado }}</span>
                    <span class="px-3 py-0.5 rounded-full text-xs font-bold uppercase border {{ $comprobante->estado === 'emitido' ? 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800' }}">{{ $comprobante->estado }}</span>
                </h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($comprobante->estado === 'emitido' && auth()->user()->puedeAccion('anular'))
                    <form action="{{ route('comprobantes.anular', $comprobante) }}" method="POST" onsubmit="confirmFormSubmit(event, '¿Anular este comprobante?', 'El comprobante quedará anulado.', 'Anular')">
                        @csrf @method('PATCH')
                        <button type="submit" class="px-3 py-2 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 rounded-xl text-xs font-semibold">Anular</button>
                    </form>
                @endif
                <a href="{{ route('comprobantes.pdf', $comprobante) }}" target="_blank" class="px-3.5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold">Ver PDF</a>
                <a href="{{ route('comprobantes.index') }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold">Volver</a>
            </div>
        </div>

        @if(session('success'))<div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-2xl text-xs">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 rounded-2xl text-xs">{{ session('error') }}</div>@endif

        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 overflow-hidden">
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-5 text-white">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                    <div class="border-r border-slate-700"><div class="text-[10px] text-slate-400 uppercase">Documento</div><div class="text-sm font-bold mt-0.5">{{ strtoupper($comprobante->tipo_nombre) }}</div></div>
                    <div class="border-r border-slate-700"><div class="text-[10px] text-slate-400 uppercase">Código</div><div class="text-sm font-bold text-cyan-400 mt-0.5 font-mono">{{ $comprobante->codigo_formateado }}</div></div>
                    <div class="border-r border-slate-700"><div class="text-[10px] text-slate-400 uppercase">Venta origen</div><div class="text-xs font-bold mt-0.5">{{ $comprobante->venta->codigo_formateado ?? '—' }}</div></div>
                    <div><div class="text-[10px] text-slate-400 uppercase">Total</div><div class="text-sm font-bold text-emerald-400 mt-0.5 font-mono">S/ {{ number_format($comprobante->total, 2) }}</div></div>
                </div>
            </div>

            <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                <div class="px-6 flex space-x-1 whitespace-nowrap">
                    <button class="tab-btn py-3.5 px-4 border-b-2 border-cyan-500 text-cyan-600 dark:text-cyan-400 font-bold text-xs uppercase" onclick="showTab('datos', this)">Datos del comprobante</button>
                    <button class="tab-btn py-3.5 px-4 border-b-2 border-transparent text-gray-500 font-semibold text-xs uppercase" onclick="showTab('items', this)">Ítems ({{ count($detalle) }})</button>
                    <button class="tab-btn py-3.5 px-4 border-b-2 border-transparent text-gray-500 font-semibold text-xs uppercase" onclick="showTab('pdf', this)">Visor PDF</button>
                </div>
            </div>

            <div class="p-6">
                <div id="datos" class="tab-content">
                    <p class="text-xs text-gray-500 mb-4">{{ $comprobante->venta->cotizacion_id || $comprobante->venta->orden_compra_cliente_id ? 'Con cotización u orden de compra de origen.' : 'Factura de venta directa, sin cotización ni orden de compra.' }}</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div><span class="text-gray-400 text-[10px] font-semibold block mb-1">Cliente</span><p class="font-bold text-sm">{{ $comprobante->venta->cliente->razon_social ?? $comprobante->venta->cliente->nombre }}</p><p class="text-xs text-gray-500">RUC/DNI: {{ $comprobante->venta->cliente->ruc ?? '—' }}</p></div>
                        <div><span class="text-gray-400 text-[10px] font-semibold block mb-1">Fecha emisión</span><p class="font-semibold text-sm">{{ $comprobante->fecha_emision?->format('d/m/Y') }}</p></div>
                        <div><span class="text-gray-400 text-[10px] font-semibold block mb-1">Emitido por</span><p class="font-semibold text-sm">{{ $comprobante->usuario->name ?? '—' }}</p></div>
                        <div><span class="text-gray-400 text-[10px] font-semibold block mb-1">Totales</span><p class="font-mono font-bold text-cyan-600">S/ {{ number_format($comprobante->total, 2) }}</p><p class="text-xs text-gray-400 font-mono">Subtotal S/ {{ number_format($comprobante->subtotal, 2) }} | IGV S/ {{ number_format($comprobante->igv, 2) }}</p><p class="text-xs text-gray-400">IGV {{ number_format($comprobante->pct_igv, 2) }}% · Retención S/ {{ number_format($comprobante->retencion, 2) }}</p>@if($comprobante->motivo_excepcion_tributaria)<p class="text-xs text-amber-700">Excepción: {{ $comprobante->motivo_excepcion_tributaria }}</p>@endif</div>
                    </div>
                </div>
                <div id="items" class="tab-content hidden">
                    <div class="overflow-x-auto"><table class="w-full text-xs"><thead class="bg-slate-900 text-white uppercase text-[10px]"><tr><th class="px-3 py-2">#</th><th class="px-3 py-2">Producto</th><th class="px-3 py-2 text-right">Cant.</th><th class="px-3 py-2 text-right">P.Unit</th><th class="px-3 py-2 text-right">Importe</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">@foreach($detalle as $i => $item)<tr><td class="px-3 py-2">{{ $i+1 }}</td><td class="px-3 py-2 font-semibold">{{ $item->producto_nombre }} <span class="text-gray-400 font-mono">({{ $item->producto_sku }})</span></td><td class="px-3 py-2 text-right font-mono">{{ number_format($item->cantidad, 2) }}</td><td class="px-3 py-2 text-right font-mono">{{ number_format($item->precio_venta_unit, 2) }}</td><td class="px-3 py-2 text-right font-mono font-bold">{{ number_format($item->cantidad * $item->precio_venta_unit, 2) }}</td></tr>@endforeach</tbody></table></div>
                </div>
                <div id="pdf" class="tab-content hidden">
                    <object data="{{ route('comprobantes.pdf', $comprobante) }}" type="application/pdf" class="w-full rounded-xl border" style="height:70vh;"><iframe src="{{ route('comprobantes.pdf', $comprobante) }}" class="w-full" style="height:70vh;" frameborder="0"></iframe></object>
                    <div class="mt-3 flex justify-end"><a href="{{ route('comprobantes.pdf.descargar', $comprobante) }}" class="px-3 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold">Descargar PDF</a></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(id, btn) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.getElementById(id).classList.remove('hidden');
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('border-cyan-500','text-cyan-600','font-bold');
                b.classList.add('border-transparent','text-gray-500','font-semibold');
            });
            btn.classList.add('border-cyan-500','text-cyan-600','font-bold');
            btn.classList.remove('border-transparent','text-gray-500','font-semibold');
        }
    </script>
</x-app-layout>
