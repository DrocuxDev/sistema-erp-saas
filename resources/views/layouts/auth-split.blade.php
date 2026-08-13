<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Iniciar sesión' }} — JHARDSYSTEX</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col lg:flex-row">
            {{-- Panel izquierdo --}}
            <aside class="relative hidden lg:flex lg:w-[42%] xl:w-[40%] bg-brand-dark flex-col overflow-hidden">
                <svg class="absolute inset-0 w-full h-full opacity-30 pointer-events-none" viewBox="0 0 800 900" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="120" cy="180" r="4" fill="#2BB099"/>
                    <circle cx="280" cy="120" r="4" fill="#2BB099"/>
                    <circle cx="420" cy="200" r="4" fill="#2BB099"/>
                    <circle cx="560" cy="140" r="4" fill="#2BB099"/>
                    <circle cx="680" cy="220" r="4" fill="#2BB099"/>
                    <circle cx="200" cy="340" r="4" fill="#2BB099"/>
                    <circle cx="380" cy="380" r="4" fill="#2BB099"/>
                    <circle cx="520" cy="320" r="4" fill="#2BB099"/>
                    <circle cx="640" cy="400" r="4" fill="#2BB099"/>
                    <circle cx="160" cy="520" r="4" fill="#2BB099"/>
                    <circle cx="320" cy="480" r="4" fill="#2BB099"/>
                    <circle cx="480" cy="540" r="4" fill="#2BB099"/>
                    <circle cx="600" cy="500" r="4" fill="#2BB099"/>
                    <circle cx="240" cy="660" r="4" fill="#2BB099"/>
                    <circle cx="400" cy="620" r="4" fill="#2BB099"/>
                    <circle cx="540" cy="680" r="4" fill="#2BB099"/>
                    <line x1="120" y1="180" x2="280" y2="120" stroke="#2BB099" stroke-width="1"/>
                    <line x1="280" y1="120" x2="420" y2="200" stroke="#2BB099" stroke-width="1"/>
                    <line x1="420" y1="200" x2="560" y2="140" stroke="#2BB099" stroke-width="1"/>
                    <line x1="560" y1="140" x2="680" y2="220" stroke="#2BB099" stroke-width="1"/>
                    <line x1="120" y1="180" x2="200" y2="340" stroke="#2BB099" stroke-width="1"/>
                    <line x1="200" y1="340" x2="380" y2="380" stroke="#2BB099" stroke-width="1"/>
                    <line x1="380" y1="380" x2="520" y2="320" stroke="#2BB099" stroke-width="1"/>
                    <line x1="520" y1="320" x2="640" y2="400" stroke="#2BB099" stroke-width="1"/>
                    <line x1="200" y1="340" x2="160" y2="520" stroke="#2BB099" stroke-width="1"/>
                    <line x1="160" y1="520" x2="320" y2="480" stroke="#2BB099" stroke-width="1"/>
                    <line x1="320" y1="480" x2="480" y2="540" stroke="#2BB099" stroke-width="1"/>
                    <line x1="480" y1="540" x2="600" y2="500" stroke="#2BB099" stroke-width="1"/>
                    <line x1="160" y1="520" x2="240" y2="660" stroke="#2BB099" stroke-width="1"/>
                    <line x1="240" y1="660" x2="400" y2="620" stroke="#2BB099" stroke-width="1"/>
                    <line x1="400" y1="620" x2="540" y2="680" stroke="#2BB099" stroke-width="1"/>
                    <line x1="680" y1="220" x2="640" y2="400" stroke="#2BB099" stroke-width="1"/>
                    <line x1="420" y1="200" x2="380" y2="380" stroke="#2BB099" stroke-width="1"/>
                </svg>

                <div class="relative z-10 flex flex-col h-full p-10 xl:p-12">
                    <div class="flex items-center gap-3">
                        <svg class="h-8 w-8 text-white shrink-0" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="3" y="5" width="26" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M11 24h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M16 23v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M8 14h16" stroke="#2BB099" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M8 14c2-3 4-4 8-4s6 1 8 4" stroke="#2BB099" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <span class="text-white font-bold text-lg tracking-[0.2em] uppercase border-b border-white/40 pb-0.5">JHARDSYSTEX</span>
                    </div>

                    <div class="mt-auto pb-8">
                        <p class="text-brand-teal text-xs font-semibold tracking-[0.25em] uppercase mb-4">Panel interno</p>
                        <h1 class="text-white text-3xl xl:text-4xl font-bold leading-tight max-w-md">
                            Un solo acceso para todos tus sistemas
                        </h1>
                        <p class="text-gray-400 text-sm xl:text-base mt-4 max-w-sm leading-relaxed">
                            Gestiona operaciones, proyectos y equipos desde una sola cuenta corporativa.
                        </p>
                    </div>
                </div>
            </aside>

            {{-- Panel derecho --}}
            <main class="flex-1 flex flex-col min-h-screen bg-white">
                <div class="lg:hidden bg-brand-dark px-6 py-5 flex items-center gap-3">
                    <svg class="h-7 w-7 text-white shrink-0" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <rect x="3" y="5" width="26" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M11 24h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M16 23v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M8 14h16" stroke="#2BB099" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M8 14c2-3 4-4 8-4s6 1 8 4" stroke="#2BB099" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <span class="text-white font-bold text-sm tracking-[0.15em] uppercase">JHARDSYSTEX</span>
                </div>

                <div class="flex-1 flex flex-col justify-center px-6 sm:px-12 lg:px-16 xl:px-24 py-10">
                    <div class="w-full max-w-md mx-auto">
                        {{ $slot }}
                    </div>
                </div>

                <footer class="py-6 text-center text-xs text-gray-400">
                    jhardsystex &copy; {{ date('Y') }} — Acceso interno
                </footer>
            </main>
        </div>
    </body>
</html>
