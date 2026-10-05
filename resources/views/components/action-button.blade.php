{{--
    Botón de acción icon-only para columnas "Acciones" de listados.
    Semántica: icono (forma) + color. Nunca solo color.

    Uso: <x-action-button icon="view|pdf|edit|delete" color="violet|slate|amber|rose"
                          label="Ver cotización" href="..." />
    Sin href renderiza <button type="button"> (pásale onclick/... por atributos).
    Con `submit` renderiza <button type="submit"> para forms inline (ej. eliminar).
    Accesibilidad: 36px de target (h-9 w-9), title (tooltip hover/foco) + aria-label.
--}}
@props(['icon' => 'view', 'color' => 'cyan', 'label' => '', 'href' => null, 'submit' => false])

@php
    $icons = [
        'view' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
        'pdf' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
        'edit' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
        'delete' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>',
    ];
    $colors = [
        // Ver: cyan (acorde a la paleta del sistema)
        'cyan' => 'text-cyan-600 dark:text-cyan-400 hover:bg-cyan-50 dark:hover:bg-cyan-950/40 focus-visible:ring-cyan-500',
        'violet' => 'text-cyan-600 dark:text-cyan-400 hover:bg-cyan-50 dark:hover:bg-cyan-950/40 focus-visible:ring-cyan-500', // alias compatibilidad
        // PDF: gris neutro
        'slate' => 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 focus-visible:ring-slate-400',
        // Editar: ámbar
        'amber' => 'text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 focus-visible:ring-amber-500',
        // Eliminar: rosa/rojo, solo destructivas
        'rose' => 'text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 focus-visible:ring-rose-500',
    ];
    $base = 'inline-flex items-center justify-center h-9 w-9 rounded-xl transition-colors focus:outline-none focus-visible:ring-2';
    $svg = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">' . ($icons[$icon] ?? $icons['view']) . '</svg>';
@endphp

@if($href)
    <a href="{{ $href }}" title="{{ $label }}" aria-label="{{ $label }}" {{ $attributes->merge(['class' => $base . ' ' . ($colors[$color] ?? $colors['violet'])]) }}>{!! $svg !!}</a>
@else
    <button type="{{ $submit ? 'submit' : 'button' }}" title="{{ $label }}" aria-label="{{ $label }}" {{ $attributes->merge(['class' => $base . ' ' . ($colors[$color] ?? $colors['violet'])]) }}>{!! $svg !!}</button>
@endif
