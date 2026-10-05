{{--
    Proveedores asociados a un producto con su precio de compra.
    Misma fuente de datos y mismas rutas en show y edit.

    Modo readonly (show): solo lectura, sin alta/baja.
    Modo nested (edit, nested=true): SIN etiquetas <form> — los controles usan
    el atributo `form="pp-add"` / stub #pp-add de la página; la baja reutiliza
    el stub genérico #pres-del (DELETE) vía formaction.
--}}
@props(['producto', 'proveedoresDisponibles' => [], 'readonly' => false, 'nested' => false])

@php
    $items = $producto->productoProveedores;
@endphp

<div class="rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/60 dark:bg-white/[0.03] p-4 space-y-4">
    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-200">
        Proveedores asociados
        <span class="ml-1 font-mono text-cyan-600 dark:text-cyan-400">({{ $items->count() }})</span>
    </h4>

    @unless($readonly)
    @if($nested)
    <div class="grid grid-cols-1 sm:grid-cols-[1fr_150px_auto] gap-3 items-end">
    @else
    <form action="{{ route('productos.proveedores.add', $producto) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-[1fr_150px_auto] gap-3 items-end">
        @csrf
    @endif
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Proveedor</label>
            <select name="proveedor_id" required
                    @if($nested) form="pp-add" @endif
                    class="w-full rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all">
                <option value="">Seleccionar proveedor...</option>
                @foreach ($proveedoresDisponibles as $prov)
                    <option value="{{ $prov->id }}">{{ $prov->nombre }} ({{ $prov->ruc }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Precio compra</label>
            <input type="number" step="0.01" min="0" name="precio_compra" required placeholder="0.00"
                   @if($nested) form="pp-add" @endif
                   class="w-full rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all" />
        </div>
        <button type="submit" @if($nested) form="pp-add" @endif
                class="btn-primary !min-h-[38px] !px-3 !text-xs whitespace-nowrap">+ Asociar</button>
    @if($nested)
    </div>
    @else
    </form>
    @endif
    @endunless

    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-100 dark:bg-white/[0.04] text-[11px] font-medium text-gray-500 dark:text-gray-400">
                    <th class="px-3 py-2 text-left font-semibold">Proveedor</th>
                    <th class="px-3 py-2 text-left font-semibold">RUC</th>
                    <th class="px-3 py-2 text-right font-semibold">Precio compra</th>
                    <th class="px-3 py-2 text-center font-semibold">Última act.</th>
                    @unless($readonly)
                    <th class="px-3 py-2 text-center font-semibold"><span class="sr-only">Desasociar</span></th>
                    @endunless
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-gray-900 dark:text-gray-100">
                @forelse ($items as $pp)
                    <tr>
                        <td class="px-3 py-2 font-medium">{{ $pp->proveedor->nombre ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $pp->proveedor->ruc ?? '-' }}</td>
                        <td class="px-3 py-2 text-right font-mono font-bold">S/ {{ number_format($pp->precio_compra, 2) }}</td>
                        <td class="px-3 py-2 text-center text-xs text-gray-500 dark:text-gray-400">{{ $pp->fecha_actualizacion ? \Carbon\Carbon::parse($pp->fecha_actualizacion)->format('d/m/Y') : '-' }}</td>
                        @unless($readonly)
                        <td class="px-3 py-2 text-center">
                            @if($nested)
                                <button type="submit" form="pres-del" formaction="{{ route('productos.proveedores.remove', [$producto, $pp->proveedor_id]) }}" formmethod="post"
                                        title="Desasociar" onclick="event.preventDefault(); confirmStubSubmit(this, '¿Desasociar proveedor?', 'Se quitará de la lista del producto.')"
                                        class="text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            @else
                                <form action="{{ route('productos.proveedores.remove', [$producto, $pp->proveedor_id]) }}" method="POST" class="inline" onsubmit="confirmFormSubmit(event, '¿Desasociar proveedor?', 'Se quitará de la lista del producto.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Desasociar" class="text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </td>
                        @endunless
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $readonly ? 4 : 5 }}" class="px-3 py-5 text-center text-xs text-gray-400 dark:text-gray-500">Sin proveedores asociados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
