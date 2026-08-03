<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Textback') }}</title>

        @include('partials.pwa-head')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-4 py-10">
            <a href="/" class="mb-6 text-3xl font-bold tracking-tight text-indigo-600">Textback</a>

            <div class="w-full rounded-xl bg-white px-6 py-8 shadow ring-1 ring-gray-100 sm:max-w-md">
                {{ $slot }}
            </div>

            <div class="mt-6 flex items-center gap-3 text-xs text-gray-400">
                <a href="{{ route('privacy') }}" class="hover:text-gray-600">Privacy</a>
                <span>&middot;</span>
                <a href="{{ route('terms') }}" class="hover:text-gray-600">Terms</a>
            </div>
        </div>
    </body>
</html>
