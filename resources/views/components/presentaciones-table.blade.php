{{--
    Tabla de presentaciones de un producto (compra o venta).
    Misma fuente de datos y mismas rutas en show y edit (single source of truth).

    Modo standalone: formularios propios autocontenidos.
    Modo nested (edit, nested=true): SIN etiquetas <form> — anidarlas dentro del
    form principal es HTML inválido y rompería el guardado. Los controles usan
    el atributo `form="..."` apuntando a los stubs de la página
    (#pres-add-compra, #pres-add-venta, #pres-del, #pres-prefer + presPrefer()).
    Modo readonly (show): solo lectura, sin alta/baja ni cambio de preferida.
--}}
@props(['producto', 'tipo' => 'compra', 'nested' => false, 'readonly' => false])

@php
    $esVenta = $tipo === 'venta';
    $ruta = $esVenta ? 'presentaciones-venta' : 'presentaciones';
    $items = $esVenta ? $producto->presentacionesVenta : $producto->presentacionesCompra;
    $titulo = $esVenta ? 'Presentaciones para venta' : 'Presentaciones para compra';
    $addFormId = 'pres-add-' . $tipo;
@endphp

<div class="rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/60 dark:bg-white/[0.03] p-4 space-y-4">
    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-200">
        {{ $titulo }}
        <span class="ml-1 font-mono text-cyan-600 dark:text-cyan-400">({{ $items->count() }})</span>
    </h4>

    @unless($readonly)
    @if($nested)
    <div class="grid grid-cols-1 sm:grid-cols-[1fr_130px_auto_auto] gap-3 items-end">
    @else
    <form action="{{ route("productos.{$ruta}.add", $producto) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-[1fr_130px_auto_auto] gap-3 items-end">
        @csrf
    @endif
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">1 unidad de</label>
            <input type="text" name="unidad_medida" placeholder="Ej: Caja x 12" required maxlength="20"
                   @if($nested) form="{{ $addFormId }}" @endif
                   class="w-full rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all" />
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">= este valor ({{ $producto->unidad_medida }})</label>
            <input type="number" name="equivalencia" step="0.0001" min="0.0001" required placeholder="12"
                   @if($nested) form="{{ $addFormId }}" @endif
                   class="w-full rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all" />
        </div>
        <label class="flex items-center gap-1.5 pb-2 cursor-pointer text-xs text-gray-600 dark:text-gray-400">
            <input type="checkbox" name="preferida" value="1"
                   @if($nested) form="{{ $addFormId }}" @endif
                   class="w-4 h-4 rounded accent-cyan-500" />
            Pref.
        </label>
        <button type="submit" @if($nested) form="{{ $addFormId }}" @endif
                class="btn-primary !min-h-[38px] !px-3 !text-xs whitespace-nowrap">+ Agregar nuevo item</button>
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
                    <th class="px-3 py-2 text-left font-semibold">1 unidad de</th>
                    <th class="px-3 py-2 text-right font-semibold">= valor ({{ $producto->unidad_medida }})</th>
                    <th class="px-3 py-2 text-center font-semibold">Pref.</th>
                    @unless($readonly)
                    <th class="px-3 py-2 text-center font-semibold"><span class="sr-only">Eliminar</span></th>
                    @endunless
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-gray-900 dark:text-gray-100">
                @forelse ($items as $pres)
                    <tr>
                        <td class="px-3 py-2 font-medium">{{ $pres->unidad_medida }}</td>
                        <td class="px-3 py-2 text-right font-mono font-bold">{{ number_format($pres->equivalencia, 4) }}</td>
                        <td class="px-3 py-2 text-center">
                            @if($readonly)
                                @if($pres->preferida)
                                    <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-400 rounded-full text-xs font-semibold">Sí</span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            @elseif($nested)
                                <input type="radio" name="pref-{{ $tipo }}-{{ $pres->id }}" value="1" {{ $pres->preferida ? 'checked' : '' }}
                                       data-url="{{ route("productos.{$ruta}.prefer", [$producto, $pres->id]) }}"
                                       onchange="presPrefer(this)" title="Marcar como preferida"
                                       class="w-4 h-4 accent-cyan-500 cursor-pointer" />
                            @else
                                <form action="{{ route("productos.{$ruta}.prefer", [$producto, $pres->id]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="radio" name="preferida" value="1" {{ $pres->preferida ? 'checked' : '' }}
                                           onchange="this.form.submit()" title="Marcar como preferida"
                                           class="w-4 h-4 accent-cyan-500 cursor-pointer" />
                                </form>
                            @endif
                        </td>
                        @unless($readonly)
                        <td class="px-3 py-2 text-center">
                            @if($nested)
                                <button type="submit" form="pres-del" formaction="{{ route("productos.{$ruta}.remove", [$producto, $pres->id]) }}" formmethod="post"
                                        title="Eliminar" onclick="event.preventDefault(); confirmStubSubmit(this, '¿Eliminar presentación?', 'Se quitará de la lista del producto.')"
                                        class="text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            @else
                                <form action="{{ route("productos.{$ruta}.remove", [$producto, $pres->id]) }}" method="POST" class="inline" onsubmit="confirmFormSubmit(event, '¿Eliminar presentación?', 'Se quitará de la lista del producto.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Eliminar" class="text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300 transition-colors">
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
                        <td colspan="{{ $readonly ? 3 : 4 }}" class="px-3 py-5 text-center text-xs text-gray-400 dark:text-gray-500">Sin presentaciones registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
