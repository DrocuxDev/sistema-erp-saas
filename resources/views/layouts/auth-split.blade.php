<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Iniciar sesión' }} — JHARDSYSTEX</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-dark-bg text-white">
        <main class="grid min-h-screen w-full bg-darker-bg lg:grid-cols-5">
                    <section aria-label="Acceso al sistema" class="flex min-h-screen flex-col px-6 py-8 sm:px-10 sm:py-12 lg:col-span-2 lg:px-12">
                        <div class="my-auto w-full max-w-md mx-auto">
                            {{ $slot }}
                        </div>
                        <footer class="pt-8 text-center text-xs text-slate-400">JHardSystex &copy; {{ date('Y') }} — Sistema ERP</footer>
                    </section>
                    <aside aria-label="JHardSystex ERP" class="relative hidden min-h-screen overflow-hidden bg-card-bg lg:col-span-3 lg:flex lg:flex-col">
                        <svg aria-hidden="true" focusable="false" class="absolute inset-0 h-full w-full" viewBox="0 0 600 700" preserveAspectRatio="xMidYMid slice" fill="none">
                            <defs>
                                <linearGradient id="login-wave" x1="0" y1="450" x2="600" y2="220" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#0284C7" stop-opacity="0.1" />
                                    <stop offset="0.45" stop-color="#0891B2" />
                                    <stop offset="0.7" stop-color="#22D3EE" />
                                    <stop offset="1" stop-color="#0284C7" stop-opacity="0.15" />
                                </linearGradient>
                                <linearGradient id="login-wave-secondary" x1="0" y1="550" x2="550" y2="350" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#10B981" stop-opacity="0.1" />
                                    <stop offset="0.5" stop-color="#06B6D4" stop-opacity="0.8" />
                                    <stop offset="1" stop-color="#0284C7" stop-opacity="0.1" />
                                </linearGradient>
                                <filter id="login-glow" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="15" /></filter>
                            </defs>
                            <path d="M-100 400C70 470 125 235 290 285S420 320 510 165 700 95 730 85" stroke="url(#login-wave)" stroke-width="100" opacity="0.15" />
                            <path d="M-90 465C90 465 125 310 270 335S420 345 505 245 660 180 730 190" stroke="url(#login-wave)" stroke-width="28" filter="url(#login-glow)" />
                            <path d="M-90 465C90 465 125 310 270 335S420 345 505 245 660 180 730 190" stroke="url(#login-wave)" stroke-width="5" />
                            <path d="M-70 515C70 410 180 450 280 445S400 340 500 420 630 445 710 375" stroke="url(#login-wave-secondary)" stroke-width="34" filter="url(#login-glow)" />
                            <path d="M-70 515C70 410 180 450 280 445S400 340 500 420 630 445 710 375" stroke="url(#login-wave-secondary)" stroke-width="4" />
                            <path d="M-60 565C100 475 160 560 300 515S410 475 495 510 650 560 710 450" stroke="url(#login-wave-secondary)" stroke-width="40" opacity="0.2" />
                        </svg>
                        <div class="relative p-10 xl:p-16">
                            <p class="text-xs font-semibold uppercase tracking-widest text-cyan-accent">Gestión empresarial</p>
                            <h1 class="mt-4 max-w-sm text-3xl font-bold leading-tight tracking-tight">Tu operación, en un solo lugar.</h1>
                            <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-400">Cotizaciones, ventas, compras e inventario conectados para el trabajo de cada día.</p>
                        </div>
                    </aside>
        </main>
        <link rel="stylesheet" href="{{ asset('css/toast-dialog.css') }}">
        <script src="{{ asset('js/toast-dialog.js') }}"></script>
    </body>
</html>
