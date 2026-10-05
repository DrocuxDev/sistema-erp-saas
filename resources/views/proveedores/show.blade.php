<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="page-title">
                {{ $proveedor->nombre }}
            </h2>
            <a href="{{ route('proveedores.index') }}" class="btn-secondary text-xs">
                Volver al listado
            </a>
        </div>
    </x-slot>

    <x-detail-card>
        <div class="flex items-center gap-4 mb-2">
            @if($proveedor->imagen)
                <img src="{{ Storage::url($proveedor->imagen) }}" alt="Foto de {{ $proveedor->nombre }}"
                     class="w-32 h-32 rounded-xl object-cover border border-gray-200 dark:border-white/10" />
            @else
                <div class="w-32 h-32 rounded-xl border border-dashed border-gray-300 dark:border-white/20 bg-gray-50 dark:bg-white/5 flex items-center justify-center" title="Sin foto">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif
        </div>
        <x-detail-section title="Información general">
            <x-detail-field label="Nombre Comercial">{{ $proveedor->nombre }}</x-detail-field>
            <x-detail-field label="RUC">{{ $proveedor->ruc }}</x-detail-field>
            <x-detail-field label="Razón Social">{{ $proveedor->razon_social }}</x-detail-field>
            <x-detail-field label="Estado">
                <span class="px-3 py-1 rounded-full text-sm font-semibold
                    {{ $proveedor->estado === 'activo' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' }}">
                    {{ ucfirst($proveedor->estado) }}
                </span>
            </x-detail-field>
        </x-detail-section>

        <x-detail-section title="Contacto">
            <x-detail-field label="Dirección" :span="2">{{ $proveedor->direccion }}</x-detail-field>
            <x-detail-field label="Teléfono">{{ $proveedor->telefono ?? 'No registrado' }}</x-detail-field>
            <x-detail-field label="Correo electrónico">{{ $proveedor->email ?? 'No registrado' }}</x-detail-field>
            <x-detail-field label="Contacto">{{ $proveedor->contacto ?? 'No registrado' }}</x-detail-field>
        </x-detail-section>

        <x-detail-section title="Condiciones comerciales">
            <x-detail-field label="Condición de Pago">{{ $proveedor->condicion_pago }}</x-detail-field>
        </x-detail-section>

        <x-detail-actions>
            <a href="{{ route('proveedores.edit', $proveedor) }}" class="btn-primary flex-1">
                Editar
            </a>
            <a href="{{ route('proveedores.index') }}" class="btn-secondary flex-1">
                Volver
            </a>
        </x-detail-actions>
    </x-detail-card>
</x-app-layout>
