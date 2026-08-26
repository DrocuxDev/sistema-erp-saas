@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center gap-3 px-4 py-3 text-white bg-cyan-600 dark:bg-cyan-950/80 dark:text-cyan-300 dark:border dark:border-cyan-500/40 rounded-xl text-sm font-semibold shadow-sm transition-all duration-200'
            : 'flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-300 hover:text-cyan-700 dark:hover:text-cyan-300 hover:bg-cyan-50/70 dark:hover:bg-slate-800/60 rounded-xl text-sm font-medium transition-all duration-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
