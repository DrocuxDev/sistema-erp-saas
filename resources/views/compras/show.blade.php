<x-app-layout>
    <div class="page-container py-4 sm:py-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase mb-1"><a href="{{ route('compras.index') }}" class="hover:text-cyan-600">Compras</a><span>/</span><span>#{{ $compra->id }}</span></div>
                <h1 class="text-2xl font-bold">Recepción #{{ $compra->id }} <span class="font-mono text-cyan-600 text-lg">{{ $compra->ordenCompra->codigo_formateado ?? 'Directa' }}</span></h1>
            </div>
            <div class="flex flex-wrap gap-2">
                @if(auth()->user()->puedeAccion('finanzas'))
                    @if($saldoFinanciero['saldo'] > 0)
                        <a href="{{ route('compras.pagos.create', $compra) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold">Registrar pago</a>
                    @endif
                    @if($saldoFinanciero['exceso'] > 0)
                        <a href="{{ route('compras.pagos.create', ['compra' => $compra, 'tipo' => 'reintegro']) }}" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold">Registrar reintegro</a>
                    @endif
                @endif
                @if(auth()->user()->puedeAccion('anular') && array_sum($saldosDevolucion) > 0)
                    <a href="{{ route('compras.devoluciones.create', $compra) }}" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold">Devolver al proveedor</a>
                @endif
                <a href="{{ route('compras.index') }}" class="px-3.5 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold">Volver</a>
            </div>
        </div>

        @if(session('success'))<div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="p-4 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-2xl text-xs">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="p-4 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-2xl text-xs"><ul class="list-disc pl-4 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border overflow-hidden">
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-5 text-white grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                <div class="border-r border-slate-700"><div class="text-[10px] text-slate-400 uppercase">Proveedor</div><div class="text-xs font-bold truncate">{{ $compra->proveedor->nombre ?? '—' }}</div></div>
                <div class="border-r border-slate-700"><div class="text-[10px] text-slate-400 uppercase">O/C Origen</div><div class="text-xs font-bold font-mono">{{ $compra->ordenCompra->codigo_formateado ?? 'Directa' }}</div></div>
                <div class="border-r border-slate-700"><div class="text-[10px] text-slate-400 uppercase">Fecha</div><div class="text-sm font-bold">{{ $compra->fecha?->format('d/m/Y') }}</div></div>
                <div><div class="text-[10px] text-slate-400 uppercase">Total</div><div class="text-sm font-bold text-emerald-400 font-mono">S/ {{ number_format($compra->total, 2) }}</div></div>
            </div>
            <div class="border-b"><div class="px-6 flex space-x-1 whitespace-nowrap">
                <button class="tab-btn py-3.5 px-4 border-b-2 border-cyan-500 text-cyan-600 font-bold text-xs uppercase" onclick="showTab('datos', this)">Datos</button>
                <button class="tab-btn py-3.5 px-4 border-b-2 border-transparent text-gray-500 text-xs uppercase" onclick="showTab('items', this)">Bienes ({{ count($detalles) }})</button>
                <button class="tab-btn py-3.5 px-4 border-b-2 border-transparent text-gray-500 text-xs uppercase" onclick="showTab('devoluciones', this)">Devoluciones ({{ $compra->devoluciones->count() }})</button>
                <button class="tab-btn py-3.5 px-4 border-b-2 border-transparent text-gray-500 text-xs uppercase" onclick="showTab('pagos', this)">Pagos ({{ $compra->pagos->count() }})</button>
            </div></div>
            <div class="p-6">
                <div id="datos" class="tab-content grid grid-cols-1 sm:grid-cols-3 gap-6 text-sm">
                    <div><span class="text-gray-400 text-[10px] font-semibold block">Proveedor</span><p class="font-semibold">{{ $compra->proveedor->nombre }}</p><p class="text-xs text-gray-500">RUC: {{ $compra->proveedor->ruc ?? '—' }}</p></div>
                    <div><span class="text-gray-400 text-[10px] font-semibold block">Registrado por</span><p class="font-semibold">{{ $compra->usuario->name ?? '—' }}</p></div>
                    <div><span class="text-gray-400 text-[10px] font-semibold block">Efecto en stock</span><p class="font-semibold text-emerald-600">Entrada kardex generada</p></div>
                </div>
                <div id="items" class="tab-content hidden"><div class="overflow-x-auto"><table class="w-full text-xs"><thead class="bg-slate-900 text-white uppercase text-[10px]"><tr><th class="px-3 py-2">#</th><th class="px-3 py-2">Producto</th><th class="px-3 py-2 text-right">Cant.</th><th class="px-3 py-2 text-right">P.Compra</th><th class="px-3 py-2 text-right">Subtotal</th></tr></thead><tbody class="divide-y">@foreach($detalles as $i => $d)<tr><td class="px-3 py-2">{{ $i+1 }}</td><td class="px-3 py-2 font-semibold">{{ $d->producto_nombre }}</td><td class="px-3 py-2 text-right font-mono">{{ number_format($d->cantidad, 2) }}</td><td class="px-3 py-2 text-right font-mono">{{ number_format($d->precio_compra_unit, 2) }}</td><td class="px-3 py-2 text-right font-mono font-bold">{{ number_format($d->cantidad * $d->precio_compra_unit, 2) }}</td></tr>@endforeach</tbody></table></div></div>
                <div id="devoluciones" class="tab-content hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Devoluciones al proveedor</h3>
                            <p class="text-xs text-gray-500">Cantidades retiradas, motivo, responsable e importe pendiente de compensar.</p>
                        </div>
                        @if(auth()->user()->puedeAccion('anular') && array_sum($saldosDevolucion) > 0)
                            <a href="{{ route('compras.devoluciones.create', $compra) }}" class="px-3 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold">Nueva devolución</a>
                        @endif
                    </div>
                    @forelse($compra->devoluciones as $devolucion)
                        <div class="border border-gray-200 dark:border-gray-700/60 rounded-xl p-4 mb-3">
                            <div class="flex flex-wrap justify-between gap-3 mb-3">
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Devolución #{{ $devolucion->id }} · {{ $devolucion->fecha->format('d/m/Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $devolucion->usuario?->name ?? 'Usuario eliminado' }} · {{ $devolucion->motivo }}</p>
                                </div>
                                <p class="font-mono text-xs font-bold text-cyan-700 dark:text-cyan-300">S/ {{ number_format($devolucion->total, 2) }}</p>
                            </div>
                            <ul class="space-y-1 text-xs text-gray-700 dark:text-gray-300">
                                @foreach($devolucion->detalles as $linea)
                                    <li class="flex justify-between gap-3"><span>{{ $linea->compraDetalle->producto->nombre }}</span><span class="font-mono">{{ number_format($linea->cantidad, 2) }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500">Esta recepción aún no tiene devoluciones.</p>
                    @endforelse
                </div>
                <div id="pagos" class="tab-content hidden">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5 text-xs">
                        <div><span class="text-gray-400 text-[10px] font-semibold block">Neto a pagar</span><p class="font-mono font-bold text-gray-900 dark:text-white">S/ {{ number_format($saldoFinanciero['neto'] / 100, 2) }}</p></div>
                        <div><span class="text-gray-400 text-[10px] font-semibold block">Pagado neto</span><p class="font-mono font-bold text-gray-900 dark:text-white">S/ {{ number_format($saldoFinanciero['pagado'] / 100, 2) }}</p></div>
                        <div><span class="text-gray-400 text-[10px] font-semibold block">Pendiente</span><p class="font-mono font-bold text-cyan-700 dark:text-cyan-300">S/ {{ number_format($saldoFinanciero['saldo'] / 100, 2) }}</p></div>
                        <div><span class="text-gray-400 text-[10px] font-semibold block">A reintegrar</span><p class="font-mono font-bold text-amber-700 dark:text-amber-300">S/ {{ number_format($saldoFinanciero['exceso'] / 100, 2) }}</p></div>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">El neto descuenta las devoluciones al proveedor. Pagos y reintegros se registran manualmente.</p>
                    @forelse($compra->pagos as $pago)
                        <div class="border border-gray-200 dark:border-gray-700/60 rounded-xl p-4 mb-3 text-xs">
                            <div class="flex flex-wrap justify-between gap-3">
                                <div><p class="font-bold text-gray-900 dark:text-white">{{ ucfirst($pago->tipo) }} #{{ $pago->id }} · {{ $pago->fecha->format('d/m/Y') }} @if($pago->estado === 'anulado')<span class="text-rose-700 dark:text-rose-300">· ANULADO</span>@endif</p><p class="text-gray-500">{{ ucfirst($pago->medio) }} · {{ $pago->referencia ?: 'Sin referencia' }} · {{ $pago->usuario?->name ?? 'Usuario eliminado' }}</p>@if($pago->observacion)<p class="text-gray-500 mt-1">{{ $pago->observacion }}</p>@endif</div>
                                <p class="font-mono font-bold {{ $pago->estado === 'anulado' ? 'text-gray-400 line-through' : ($pago->tipo === 'pago' ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300') }}">S/ {{ number_format($pago->monto, 2) }}</p>
                            </div>
                            @if($pago->estado === 'anulado')
                                <p class="text-xs text-rose-700 dark:text-rose-300 mt-2">Anulado por {{ $pago->anulador?->name ?? 'Usuario eliminado' }}: {{ $pago->motivo_anulacion }}</p>
                            @elseif(auth()->user()->puedeAccion('finanzas'))
                                <form action="{{ route('compras.pagos.anular', ['compra' => $compra, 'pago' => $pago]) }}" method="POST" class="flex flex-col sm:flex-row gap-2 mt-3">
                                    @csrf @method('PATCH')
                                    <input name="motivo_anulacion" type="text" minlength="10" maxlength="1000" required placeholder="Motivo de anulación (mínimo 10 caracteres)" class="flex-1 rounded-xl border-gray-300 dark:border-gray-700 dark:bg-darker-bg dark:text-white text-xs">
                                    <button type="submit" class="px-3 py-2 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 rounded-xl text-xs font-semibold">Anular registro</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-gray-500">Aún no hay pagos registrados.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <script>
        function showTab(id, btn) { document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden')); document.getElementById(id).classList.remove('hidden'); document.querySelectorAll('.tab-btn').forEach(b => { b.classList.remove('border-cyan-500','text-cyan-600','font-bold'); b.classList.add('border-transparent','text-gray-500'); }); btn.classList.add('border-cyan-500','text-cyan-600','font-bold'); }
    </script>
</x-app-layout>
