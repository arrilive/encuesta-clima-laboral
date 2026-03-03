<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Encuesta de Clima Laboral</title>

    <!-- DM Sans — Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

</head>

<body class="antialiased bg-slate-50 min-h-screen">

    <!-- Header sticky -->
    <header class="bg-white/90 backdrop-blur-md border-b border-slate-200 sticky top-0 z-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">

            <!-- Izquierda: título -->
            <span class="text-sm font-semibold text-slate-800 tracking-tight">Clima Laboral</span>

            <!-- Derecha: badge -->
            <span
                class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500 select-none">
                Anónima · Confidencial
            </span>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="min-h-[calc(100vh-5rem)]">
        {{ $slot }}
    </main>

    @livewireScripts
</body>

</html>
