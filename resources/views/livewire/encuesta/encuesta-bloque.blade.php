<div>
    {{-- Barra de progreso sticky --}}
    <div class="bg-white/90 backdrop-blur-md border-b border-slate-200 px-6 py-3 sticky top-14 z-10">
        <div class="max-w-2xl mx-auto">
            <a href="{{ route('encuesta.dimensiones', $encuesta->token) }}"
                class="text-xs text-slate-400 hover:text-blue-600 transition-colors inline-flex items-center gap-1 mb-1">
                ← Volver a mis dimensiones
            </a>
            <div class="flex justify-between text-xs text-slate-500 mb-1.5">
                <span class="font-semibold text-slate-800">
                    Dimensión {{ $dimensionActual }} de {{ $totalDimensiones }}: {{ $dimensionNombre }}
                </span>
                <span>{{ $progreso }}% completado</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-1.5">
                <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-500"
                    style="width: {{ $progreso }}%"></div>
            </div>
        </div>
    </div>

    {{-- Preguntas --}}
    <div class="max-w-2xl mx-auto py-8 px-4 space-y-4">
        @if ($dimensionDescripcion)
            <p class="text-sm text-slate-600 leading-relaxed mb-6">{{ $dimensionDescripcion }}</p>
        @endif
        @foreach ($preguntasPorSubdimension as $subdimensionId => $preguntas)
            {{-- Separador de subdimensión --}}
            <div class="flex items-center gap-3 pt-2">
                <span class="text-xs font-semibold uppercase tracking-widest text-slate-400 whitespace-nowrap">
                    {{ $preguntas->first()->subdimension->nombre }}
                </span>
                <div class="flex-1 h-px bg-slate-200"></div>
            </div>

            {{-- Preguntas de esta subdimensión --}}
            @foreach ($preguntas as $pregunta)
                <livewire:encuesta.pregunta-cerrada :encuesta="$encuesta" :pregunta="$pregunta" :mostrarError="in_array($pregunta->id, $preguntasSinRespuesta)"
                    :key="'pregunta-' . $pregunta->id" />
            @endforeach
        @endforeach

        {{-- Error de validación del bloque --}}
        @error('bloque')
            <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <svg aria-hidden="true" class="mt-0.5 flex-shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <span>{{ $message }}</span>
            </div>
        @enderror

        {{-- Botón siguiente --}}
        <div class="pt-4">
            <button wire:click="siguienteBloque" wire:loading.attr="disabled"
                class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                       text-white font-semibold text-sm px-6 py-3 rounded-xl
                       transition-all duration-200 hover:-translate-y-px
                       hover:shadow-[0_4px_16px_rgba(37,99,235,.25)]
                       active:translate-y-0 active:shadow-none
                       disabled:opacity-75 disabled:cursor-not-allowed">
                <svg wire:loading wire:target="siguienteBloque" class="animate-spin flex-shrink-0" width="14"
                    height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                    <path d="M12 2a10 10 0 0 1 10 10" />
                </svg>
                <span wire:loading.remove wire:target="siguienteBloque">
                    {{ $dimensionActual < $totalDimensiones ? 'Siguiente dimensión' : 'Finalizar dimensiones' }}
                    <span class="ml-1">→</span>
                </span>
                <span wire:loading wire:target="siguienteBloque">Guardando…</span>
            </button>
        </div>
    </div>

    {{-- Script para hacer scroll a la primera pregunta faltante --}}
    @script
        <script>
            $wire.on('scroll-to-pregunta', ({
                preguntaId
            }) => {
                const el = document.getElementById(`pregunta-${preguntaId}`);
                if (el) {
                    // Pequeño delay para asegurar que Livewire aplicó las clases rojas
                    setTimeout(() => {
                        const y = el.getBoundingClientRect().top + window.scrollY -
                            100; // offset por el header sticky
                        window.scrollTo({
                            top: y,
                            behavior: 'smooth'
                        });
                    }, 50);
                }
            });
        </script>
    @endscript
</div>
