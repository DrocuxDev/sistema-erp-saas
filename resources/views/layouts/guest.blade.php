<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50 dark:bg-dark-bg">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50 dark:bg-dark-bg">
            <div>
                <a href="/">
                    <x-application-logo class="w-16 h-16 fill-current text-gray-700 dark:text-gray-300" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white dark:bg-card-bg border border-gray-200/80 dark:border-gray-700/60 shadow-xs overflow-hidden rounded-2xl">
                {{ $slot }}
            </div>
        </div>

        <link rel="stylesheet" href="{{ asset('css/toast-dialog.css') }}">
        <script src="{{ asset('js/toast-dialog.js') }}"></script>
    </body>
</html>
