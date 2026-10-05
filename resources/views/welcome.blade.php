@php
    if (auth()->check()) {
        header('Location: ' . route('dashboard'));
    } else {
        header('Location: ' . route('login'));
    }
    exit;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="refresh" content="0; url={{ auth()->check() ? route('dashboard') : route('login') }}">
        <title>{{ config('app.name', 'JHardSystex ERP') }}</title>
    </head>
    <body style="background-color: #0B1329; color: #fff; font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0;">
        <p>Redirigiendo a JHardSystex ERP...</p>
    </body>
</html>
