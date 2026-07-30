<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heading ?? $title ?? 'Panel Admin' }} — Clima Laboral</title>

    <!-- Fonts — DM Sans (registrada en tailwind.config.js como font-sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 antialiased">

    <x-toast />

    <div class="flex min-h-screen">

        <x-admin.sidebar />

        <div class="flex-1 flex flex-col min-w-0">

            {{-- Header --}}
            <header class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ $heading ?? '' }}</h1>
                <p class="text-sm font-medium text-slate-500">
                    {{ auth()->user()->empresa->nombre ?? 'Todas las empresas' }}
                </p>
            </header>

            {{-- Contenido --}}
            <main class="flex-1 p-8 page-enter">
                {{ $slot }}
            </main>

        </div>
    </div>

    @livewireScripts
</body>
</html>
