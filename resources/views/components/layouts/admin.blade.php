<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Panel Admin' }} — Clima Laboral</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 antialiased">

    <div class="flex min-h-screen">

        <x-admin.sidebar />

        <div class="flex-1 flex flex-col min-w-0">

            {{-- Header --}}
            <header class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between">
                <h1 class="text-lg font-semibold text-slate-900">{{ $heading ?? '' }}</h1>
                <p class="text-xs text-slate-400">
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
