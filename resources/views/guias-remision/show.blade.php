<x-app-layout>
    <div class="page-container py-4 sm:py-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase mb-1"><a href="{{ route('guias-remision.index') }}" class="hover:text-cyan-600">Guías</a><span>/</span><span class="text-gray-900 dark:text-white">{{ $guia->codigo_formateado }}</span></div>
                <h1 class="text-2xl font-bold flex items-center gap-3"><span class="font-mono text-cyan-600">{{ $guia->codigo_formateado }}</span><span class="px-3 py-0.5 rounded-full text-xs font-bold uppercase border {{ $guia->estado === 'emitida' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-rose-100 text-rose-800 border-rose-300' }}">{{ $guia->estado }}</span></h1>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($guia->estado === 'emitida' && auth()->user()->puedeAccion('anular'))
                    <form action="{{ route('guias-remision.anular', $guia) }}" method="POST" onsubmit="confirmFormSubmit(event, '¿Anular esta guía?', 'La guía quedará anulada.', 'Anular')">@csrf @method('PATCH')<button class="px-3 py-2 bg-rose-50 text-rose-700 rounded-xl text-xs font-semibold">Anular</button></form>
                @endif
                <a href="{{ route('guias-remision.pdf', $guia) }}" target="_blank" class="px-3.5 py-2 bg-cyan-600 text-white rounded-xl text-xs font-bold">Ver PDF</a>
                <a href="{{ route('guias-remision.index') }}" class="px-3.5 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold">Volver</a>
            </div>
        </div>

        @if(session('success'))<div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs">{{ session('error') }}</div>@endif

        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border overflow-hidden">
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-5 text-white grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                <div class="border-r border-slate-700"><div class="text-[10px] text-slate-400 uppercase">Documento</div><div class="text-sm font-bold">GUÍA DE REMISIÓN</div></div>
                <div class="border-r border-slate-700"><div class="text-[10px] text-slate-400 uppercase">Código</div><div class="text-sm font-bold text-cyan-400 font-mono">{{ $guia->codigo_formateado }}</div></div>
                <div class="border-r border-slate-700"><div class="text-[10px] text-slate-400 uppercase">Venta</div><div class="text-xs font-bold">{{ $guia->venta->codigo_formateado ?? '—' }}</div></div>
                <div><div class="text-[10px] text-slate-400 uppercase">Traslado</div><div class="text-sm font-bold">{{ $guia->fecha_traslado?->format('d/m/Y') }}</div></div>
            </div>
            <div class="border-b overflow-x-auto"><div class="px-6 flex space-x-1 whitespace-nowrap">
                <button class="tab-btn py-3.5 px-4 border-b-2 border-cyan-500 text-cyan-600 font-bold text-xs uppercase" onclick="showTab('datos', this)">Datos de traslado</button>
                <button class="tab-btn py-3.5 px-4 border-b-2 border-transparent text-gray-500 text-xs uppercase" onclick="showTab('items', this)">Bienes ({{ $guia->detalles->count() }})</button>
                <button class="tab-btn py-3.5 px-4 border-b-2 border-transparent text-gray-500 text-xs uppercase" onclick="showTab('pdf', this)">Visor PDF</button>
            </div></div>
            <div class="p-6">
                <div id="datos" class="tab-content grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
                    <div><span class="text-gray-400 text-[10px] font-semibold block">Motivo</span><p class="font-semibold">{{ $guia->motivo_traslado }}</p></div>
                    <div><span class="text-gray-400 text-[10px] font-semibold block">Partida Llegada</span><p class="font-semibold">{{ $guia->punto_partida ?? '—' }} {{ $guia->punto_llegada ?? '—' }}</p></div>
                    <div><span class="text-gray-400 text-[10px] font-semibold block">Transportista / Placa</span><p class="font-semibold">{{ $guia->transportista ?? '—' }} ({{ $guia->placa_vehiculo ?? '—' }})</p></div>
                    <div><span class="text-gray-400 text-[10px] font-semibold block">Emitido por</span><p class="font-semibold">{{ $guia->usuario->name ?? '—' }}</p></div>
                </div>
                <div id="items" class="tab-content hidden"><div class="overflow-x-auto"><table class="w-full text-xs"><thead class="bg-slate-900 text-white uppercase text-[10px]"><tr><th class="px-3 py-2">#</th><th class="px-3 py-2">Producto</th><th class="px-3 py-2 text-right">Cant.</th><th class="px-3 py-2">U.M.</th></tr></thead><tbody class="divide-y">@foreach($guia->detalles as $i => $d)<tr><td class="px-3 py-2">{{ $i+1 }}</td><td class="px-3 py-2 font-semibold">{{ $d->producto->nombre ?? '—' }}</td><td class="px-3 py-2 text-right font-mono">{{ number_format($d->cantidad, 2) }}</td><td class="px-3 py-2">{{ $d->unidad_medida }}</td></tr>@endforeach</tbody></table></div></div>
                <div id="pdf" class="tab-content hidden"><object data="{{ route('guias-remision.pdf', $guia) }}" type="application/pdf" class="w-full rounded-xl border" style="height:70vh;"><iframe src="{{ route('guias-remision.pdf', $guia) }}" class="w-full" style="height:70vh;" frameborder="0"></iframe></object><div class="mt-3 flex justify-end"><a href="{{ route('guias-remision.pdf.descargar', $guia) }}" class="px-3 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold">Descargar PDF</a></div></div>
            </div>
        </div>
    </div>
    <script>
        function showTab(id, btn) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.getElementById(id).classList.remove('hidden');
            document.querySelectorAll('.tab-btn').forEach(b => { b.classList.remove('border-cyan-500','text-cyan-600','font-bold'); b.classList.add('border-transparent','text-gray-500'); });
            btn.classList.add('border-cyan-500','text-cyan-600','font-bold');
        }
    </script>
</x-app-layout>
