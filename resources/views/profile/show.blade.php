<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-3">
                <a href="{{ $rutaRegreso }}" aria-label="Volver"
                    class="btn-outline shrink-0 px-3">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="page-title truncate">Mi perfil</h1>
                    <p class="page-subtitle">Actividad de tu cuenta</p>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('profile.edit') }}" class="btn-primary">Editar perfil</a>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" class="btn-outline px-3" aria-label="Más opciones" title="Más opciones">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/>
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit').'#password'">Cambiar contraseña</x-dropdown-link>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </x-slot>

    <div class="page-container py-6 sm:py-8">
        <div class="mx-auto max-w-4xl space-y-6">
            <x-ui-card>
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-cyan-100 text-2xl font-bold text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300" aria-hidden="true">
                            {{ $iniciales ?: '?' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                                <span class="rounded-full border border-cyan-200 bg-cyan-50 px-3 py-0.5 text-xs font-semibold text-cyan-800 dark:border-cyan-800 dark:bg-cyan-950/40 dark:text-cyan-300">
                                    {{ $user->rolPersonalizado?->nombre ?: ($user->isSuperAdmin() ? 'Superadmin' : ucfirst($user->rol)) }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ '@'.$identificador }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-x-6 gap-y-3 border-t border-gray-200 pt-5 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
                            <span class="break-all">{{ $user->email }}</span>
                        </div>
                        @if ($user->punto_emision)
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z"/><circle cx="12" cy="10" r="2" stroke-width="2"/></svg>
                                <span>Punto de emisión: {{ $user->punto_emision }}</span>
                            </div>
                        @endif
                        @if ($user->created_at)
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
                                <span>Se unió el {{ $user->created_at->format('d/m/Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </x-ui-card>

            @if ($secciones)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($secciones as $clave => $etiqueta)
                        <x-ui-card class="p-5">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $etiqueta }}</p>
                            <p class="mt-1 text-2xl font-bold tabular-nums text-gray-900 dark:text-white">{{ $totales[$clave] }}</p>
                        </x-ui-card>
                    @endforeach
                </div>

                <x-ui-card>
                    <nav class="flex overflow-x-auto border-b border-gray-200 dark:border-gray-700" aria-label="Actividad del perfil">
                        @foreach ($secciones as $clave => $etiqueta)
                            <a href="{{ route('profile.show', ['seccion' => $clave]) }}"
                                @class(['ui-tab', 'ui-tab-active' => $seccion === $clave])
                                @if ($seccion === $clave) aria-current="page" @endif>
                                {{ $etiqueta }}
                            </a>
                        @endforeach
                    </nav>

                    <div class="divide-y divide-gray-200 dark:divide-gray-700/60">
                        @forelse ($registros as $registro)
                            <article class="p-5 transition-colors hover:bg-gray-50 dark:hover:bg-darker-bg/50 sm:p-6">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <a href="{{ $seccion === 'cotizaciones' ? route('cotizaciones.show', $registro) : route('ventas.show', $registro) }}"
                                            class="font-semibold text-cyan-600 hover:text-cyan-500 dark:text-cyan-400">
                                            {{ $registro->codigo_formateado }}
                                        </a>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                            {{ $seccion === 'cotizaciones' ? ($registro->asunto ?: 'Cotización comercial') : 'Venta registrada' }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $registro->cliente?->nombre ?: 'Sin cliente' }}</p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                        @if ($seccion === 'cotizaciones')
                                            <span class="rounded-full bg-gray-100 px-2.5 py-1 font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ ucfirst($registro->estado) }}</span>
                                        @else
                                            <span class="font-mono font-semibold text-gray-900 dark:text-white">S/ {{ number_format((float) $registro->total, 2) }}</span>
                                        @endif
                                        <time datetime="{{ $registro->fecha?->toDateString() }}">{{ $registro->fecha?->format('d/m/Y') }}</time>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">Aún no tienes {{ strtolower($secciones[$seccion]) }} registradas.</p>
                        @endforelse
                    </div>
                </x-ui-card>

                @if ($registros->hasPages())
                    {{ $registros->links() }}
                @endif
            @else
                <x-ui-card class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    No tienes acceso a módulos de actividad comercial.
                </x-ui-card>
            @endif
        </div>
    </div>
</x-app-layout>
