<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Iniciar sesión' }} — JHARDSYSTEX</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col lg:flex-row">
            {{-- Panel izquierdo --}}
            <aside class="relative hidden lg:flex lg:w-[42%] xl:w-[40%] bg-darker-bg border-r border-slate-800/80 flex-col overflow-hidden">
                {{-- Fondo técnico arquitectónico sutil (reemplaza constelación de IA) --}}
                <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(circle at 1px 1px, #ffffff 1px, transparent 0); background-size: 24px 24px;"></div>

                <div class="relative z-10 flex flex-col h-full p-10 xl:p-12">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 font-bold text-sm font-mono">
                            JH
                        </div>
                        <div>
                            <span class="text-white font-bold text-base tracking-tight block">JHardSystex S.A.C.</span>
                            <span class="text-xs text-slate-400 font-mono">Sistema ERP Empresarial</span>
                        </div>
                    </div>

                    <div class="mt-auto pb-8 space-y-4">
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-white/5 border border-white/10 text-xs font-mono text-cyan-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>Entorno Corporativo Seguro</span>
                        </div>
                        <h1 class="text-white text-3xl xl:text-4xl font-bold tracking-tight leading-tight max-w-md">
                            Control Comercial y Operaciones
                        </h1>
                        <p class="text-slate-400 text-sm mt-3 max-w-sm leading-relaxed">
                            Plataforma centralizada para facturación electrónica, cotizaciones, órdenes de compra e inventario valorizado.
                        </p>
                        <div class="pt-4 border-t border-slate-800/60 text-[11px] text-slate-500 flex items-center gap-2 font-mono">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Acceso restringido a personal autorizado. Sesiones auditadas.</span>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Panel derecho --}}
            <main class="flex-1 flex flex-col min-h-screen bg-white dark:bg-card-bg">
                <div class="lg:hidden bg-darker-bg px-6 py-4 flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 font-bold text-xs font-mono">
                            JH
                        </div>
                        <span class="text-white font-bold text-sm tracking-tight">JHardSystex ERP</span>
                    </div>
                    <span class="text-[10px] text-emerald-400 font-mono">● Conexión Segura</span>
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

        <link rel="stylesheet" href="{{ asset('css/toast-dialog.css') }}">
        <script src="{{ asset('js/toast-dialog.js') }}"></script>
    </body>
</html>
