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

    <div class="flex min-h-screen" x-data="{ sidebarAbierto: false }">

        <div
            x-show="sidebarAbierto"
            x-cloak
            @click="sidebarAbierto = false"
            class="fixed inset-0 bg-black/40 z-30 md:hidden"
            aria-hidden="true">
        </div>

        <x-admin.sidebar />

        <div class="flex-1 flex flex-col min-w-0">

            {{-- Header --}}
            <header class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between">
                <div class="flex items-center">
                    <button
                        @click="sidebarAbierto = !sidebarAbierto"
                        :aria-expanded="sidebarAbierto"
                        aria-label="Abrir menú de navegación"
                        class="md:hidden mr-4 text-slate-500 hover:text-slate-700">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ $heading ?? '' }}</h1>
                </div>
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
