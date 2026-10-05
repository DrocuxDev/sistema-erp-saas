<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') — @yield('title') — {{ config('app.name', 'JHardSystex ERP') }}</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #0b1329; color: #f8fafc; font-family: system-ui, sans-serif; }
        main { max-width: 32rem; padding: 2rem; text-align: center; }
        strong { color: #22d3ee; font-size: 3rem; }
        p { color: #cbd5e1; line-height: 1.6; }
        a { color: #67e8f9; }
    </style>
</head>
<body>
<main>
    <strong>@yield('code')</strong>
    <h1>@yield('title')</h1>
    <p>@yield('message')</p>
    <a href="{{ url('/') }}">Volver al inicio</a>
</main>
</body>
</html>
