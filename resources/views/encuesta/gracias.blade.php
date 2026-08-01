<x-layouts.encuesta>
    <div class="flex items-center justify-center px-4 py-14 min-h-[calc(100vh-5rem)]">
        <div class="w-full max-w-sm sm:max-w-md text-center page-enter">

            {{-- Ícono de éxito --}}
            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-emerald-50 mx-auto mb-6">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                     stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6L9 17l-5-5" />
                </svg>
            </div>

            {{-- Título --}}
            <h1 class="text-[1.7rem] font-bold text-slate-900 tracking-tight leading-tight mb-2">
                ¡Muchas gracias!
            </h1>

            {{-- Subtítulo --}}
            <p class="text-sm text-slate-500 leading-relaxed mb-8">
               Gracias por completar la encuesta. Tus respuestas fueron registradas
               de forma <strong>anónima</strong> y nos ayudarán a mejorar nuestro clima laboral.
            </p>

           <p class="text-xs text-slate-400 mt-4">
                Puedes cerrar la pestaña con seguridad.
            </p>

        </div>
    </div>
</x-layouts.encuesta>
