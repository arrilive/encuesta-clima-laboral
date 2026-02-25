<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Encuesta de Clima Laboral</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-50 min-h-screen">

        <!-- Header minimalista -->
        <header class="bg-white shadow-sm">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <span class="text-lg font-semibold text-gray-700">Encuesta de Clima Laboral</span>
            </div>
        </header>

        <!-- Contenido principal -->
        <main class="min-h-screen">
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>
