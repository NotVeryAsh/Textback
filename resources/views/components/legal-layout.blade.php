@props(['title', 'updated'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - Textback</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans text-gray-800 antialiased">
    <header class="mx-auto flex max-w-3xl items-center justify-between px-6 py-5">
        <a href="/" class="text-xl font-bold tracking-tight text-indigo-600">Textback</a>
        <nav class="flex gap-4 text-sm text-gray-500">
            <a href="{{ route('privacy') }}" class="hover:text-gray-900">Privacy</a>
            <a href="{{ route('terms') }}" class="hover:text-gray-900">Terms</a>
        </nav>
    </header>

    <main class="mx-auto max-w-3xl px-6 pb-20">
        <div class="rounded-xl bg-white p-8 shadow ring-1 ring-gray-100">
            <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm text-gray-400">Last updated {{ $updated }}</p>
            <div class="prose prose-sm mt-6 max-w-none text-gray-700 [&_h2]:mt-6 [&_h2]:mb-2 [&_h2]:text-base [&_h2]:font-semibold [&_h2]:text-gray-900 [&_p]:mb-3 [&_ul]:mb-3 [&_ul]:list-disc [&_ul]:pl-5 [&_a]:text-indigo-600 [&_a]:underline">
                {{ $slot }}
            </div>
        </div>
    </main>
</body>
</html>
