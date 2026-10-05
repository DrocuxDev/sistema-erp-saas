{{--
    Bloque thumbnail con overlay editar/eliminar en hover.
    Upload real y opcional: si se elige archivo se guarda (el controlador
    reemplaza el anterior); la X marca `eliminar_imagen` para borrar la
    actual sin subir otra. Sin foto, todo sigue igual.
--}}
@props(['name' => 'imagen', 'preview' => null, 'helper' => 'JPG/PNG/WebP · máx 2MB · se guarda con Guardar'])

@php $uid = 'uii-' . preg_replace('/[^a-z0-9]+/i', '-', $name); @endphp

<div class="flex-shrink-0">
    <div class="ui-thumb group">
        <img id="{{ $uid }}-preview" src="{{ $preview ?? '#' }}" alt="Vista previa"
             class="{{ $preview ? '' : 'hidden ' }}absolute inset-0 w-full h-full object-cover" />
        <svg id="{{ $uid }}-placeholder" class="w-12 h-12 text-gray-400 dark:text-gray-500 {{ $preview ? 'hidden' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <div class="absolute top-2 right-2 hidden group-hover:flex gap-1.5">
            <button type="button" id="{{ $uid }}-edit" title="Cambiar imagen"
                    class="w-7 h-7 rounded-md bg-black/60 hover:bg-cyan-500 text-white flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
            </button>
            <button type="button" id="{{ $uid }}-clear" title="Quitar imagen"
                    class="w-7 h-7 rounded-md bg-black/60 hover:bg-rose-500 text-white flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    <input type="file" id="{{ $uid }}-input" name="{{ $name }}" accept="image/*" class="hidden" />
    <input type="hidden" id="{{ $uid }}-remove" name="eliminar_imagen" value="0" />
    <x-input-error :messages="$errors->get($name)" class="mt-2 text-center" />
    @if($helper)
        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-2 text-center">{{ $helper }}</p>
    @endif
</div>
<script>
(function() {
    const input = document.getElementById('{{ $uid }}-input');
    const preview = document.getElementById('{{ $uid }}-preview');
    const placeholder = document.getElementById('{{ $uid }}-placeholder');
    const removeFlag = document.getElementById('{{ $uid }}-remove');
    if (!input || !preview || !placeholder || !removeFlag) return;
    document.getElementById('{{ $uid }}-edit').addEventListener('click', () => input.click());
    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
            removeFlag.value = '0';
        };
        reader.readAsDataURL(file);
    });
    document.getElementById('{{ $uid }}-clear').addEventListener('click', () => {
        input.value = '';
        preview.src = '#';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
        removeFlag.value = '1';
    });
})();
</script>
