<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="page-title">
                {{ $cliente->nombre }}
            </h2>
            <a href="{{ route('clientes.index') }}" class="btn-secondary text-xs">
                Volver al listado
            </a>
        </div>
    </x-slot>

    <x-detail-card>
        <div class="flex items-center gap-4 mb-2">
            @if($cliente->imagen)
                <img src="{{ Storage::url($cliente->imagen) }}" alt="Foto de {{ $cliente->nombre }}"
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
            <x-detail-field label="Nombre Comercial">{{ $cliente->nombre }}</x-detail-field>
            <x-detail-field label="RUC">{{ $cliente->ruc }}</x-detail-field>
            <x-detail-field label="Razón Social">{{ $cliente->razon_social }}</x-detail-field>
            <x-detail-field label="Estado">
                <span class="px-3 py-1 rounded-full text-sm font-semibold
                    {{ $cliente->estado === 'activo' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' }}">
                    {{ ucfirst($cliente->estado) }}
                </span>
            </x-detail-field>
        </x-detail-section>

        <x-detail-section title="Contacto">
            <x-detail-field label="Dirección" :span="2">{{ $cliente->direccion }}</x-detail-field>
            <x-detail-field label="Teléfono">{{ $cliente->telefono ?? 'No registrado' }}</x-detail-field>
            <x-detail-field label="Correo electrónico">{{ $cliente->email ?? 'No registrado' }}</x-detail-field>
            <x-detail-field label="Contacto">{{ $cliente->contacto ?? 'No registrado' }}</x-detail-field>
        </x-detail-section>

        <x-detail-section title="Condiciones comerciales">
            <x-detail-field label="Condición de Pago">{{ $cliente->condicion_pago }}</x-detail-field>
            <x-detail-field label="Precio unitario">{{ $cliente->precio_incluye_igv ? 'Incluye IGV' : 'Sin IGV' }}</x-detail-field>
            <x-detail-field label="IGV">{{ number_format($cliente->pct_igv, 2) }}%</x-detail-field>
            <x-detail-field label="Retención">{{ number_format($cliente->pct_retencion, 2) }}%</x-detail-field>
        </x-detail-section>

        <x-detail-actions>
            <a href="{{ route('clientes.edit', $cliente) }}" class="btn-primary flex-1">
                Editar
            </a>
            <a href="{{ route('clientes.index') }}" class="btn-secondary flex-1">
                Volver
            </a>
        </x-detail-actions>
    </x-detail-card>
</x-app-layout>
