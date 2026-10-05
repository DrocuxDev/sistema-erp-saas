<x-app-layout>
    <div class="page-container py-4 sm:py-6 space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('series-documentos.index') }}" class="hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">Series & Correlativos</a>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Nueva Serie</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    Registrar Nueva Serie
                </h1>
            </div>
            <a href="{{ route('series-documentos.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold transition-colors">
                Volver al listado
            </a>
        </div>

        <div class="bg-white dark:bg-card-bg rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/60 p-6">
            <form method="POST" action="{{ route('series-documentos.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    
                    {{-- Tipo de Documento --}}
                    <div>
                        <label class="form-label-xs">
                            Tipo de Documento <span class="text-rose-500">*</span>
                        </label>
                        <select name="tipo_documento" required
                                class="form-input">
                            <option value="">Seleccione el tipo de documento...</option>
                            @foreach($tipos as $k => $lbl)
                                <option value="{{ $k }}" {{ old('tipo_documento') === $k ? 'selected' : '' }}>
                                    {{ $lbl }} ({{ $k }})
                                </option>
                            @endforeach
                        </select>
                        @error('tipo_documento') <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Prefijo / Serie --}}
                    <div>
                        <label class="form-label-xs">
                            Prefijo de Serie <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="serie" value="{{ old('serie') }}" required placeholder="Ej: V001, OC01, F001, C001"
                               class="w-full uppercase font-mono text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                        @error('serie') <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Correlativo Actual --}}
                    <div>
                        <label class="form-label-xs">
                            Correlativo Actual (Último emitido) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="correlativo_actual" value="{{ old('correlativo_actual', 0) }}" required min="0"
                               class="w-full font-mono text-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-darker-bg px-3.5 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                        <p class="text-[10px] text-gray-400 mt-1">El próximo documento emitido tendrá este número + 1.</p>
                        @error('correlativo_actual') <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Longitud de Relleno --}}
                    <div>
                        <label class="form-label-xs">
                            Ceros de Relleno (Longitud) <span class="text-rose-500">*</span>
                        </label>
                        <select name="longitud_correlativo" required
                                class="form-input">
                            <option value="6" {{ old('longitud_correlativo', 6) == 6 ? 'selected' : '' }}>6 dígitos (ej: V001-000001)</option>
                            <option value="8" {{ old('longitud_correlativo') == 8 ? 'selected' : '' }}>8 dígitos (ej: F001-00000001)</option>
                            <option value="4" {{ old('longitud_correlativo') == 4 ? 'selected' : '' }}>4 dígitos (ej: C001-0001)</option>
                        </select>
                        @error('longitud_correlativo') <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Descripción --}}
                    <div class="sm:col-span-2">
                        <label class="form-label-xs">
                            Descripción / Propósito de la Serie
                        </label>
                        <input type="text" name="descripcion" value="{{ old('descripcion') }}" placeholder="Ej: Serie de facturas electrónicas para oficina principal"
                               class="form-input">
                        @error('descripcion') <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                </div>

                {{-- Checkboxes --}}
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60 flex flex-col sm:flex-row gap-6">
                    <label class="inline-flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="es_predeterminado" value="1" {{ old('es_predeterminado', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-cyan-600 focus:ring-cyan-500 w-4 h-4">
                        <span class="text-xs text-gray-800 dark:text-gray-200 font-semibold">Marcar como Serie Predeterminada para este Tipo de Documento</span>
                    </label>

                    <label class="inline-flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-cyan-600 focus:ring-cyan-500 w-4 h-4">
                        <span class="text-xs text-gray-800 dark:text-gray-200 font-semibold">Serie Activa para Emisión</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end gap-3">
                    <a href="{{ route('series-documentos.index') }}" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold shadow-md shadow-cyan-600/20 transition-all">
                        Guardar Serie
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
