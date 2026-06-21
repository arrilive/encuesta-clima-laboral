<div x-data="{ modalInfo: false }">
    {{-- Barra de progreso sticky --}}
    <div class="bg-white/90 backdrop-blur-md border-b border-slate-200 px-6 py-3 sticky top-0 z-10">
        <div class="max-w-2xl mx-auto">
            <div class="flex justify-between text-xs text-slate-500 mb-1.5">
                <div class="flex items-center gap-1.5">
                    <span class="font-medium text-slate-700">
                        Dimensión {{ $dimensionActual }} de {{ $totalDimensiones }}: {{ $dimensionNombre }}
                    </span>
                    <button
                        type="button"
                        @click="modalInfo = true"
                        class="text-slate-400 hover:text-blue-600 transition-colors duration-150"
                        title="¿Qué mide esta dimensión?">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                    </button>
                </div>
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
        @foreach ($preguntasPorSubdimension as $subdimensionId => $preguntas)
            {{-- Separador de subdimensión --}}
            <div class="flex items-center gap-3 pt-2">
                <span class="text-[0.7rem] font-semibold uppercase tracking-widest text-slate-400 whitespace-nowrap">
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
                    {{ $dimensionActual < $totalDimensiones ? 'Siguiente dimensión' : 'Continuar' }}
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

    {{-- Modal de información de la dimensión --}}
    <template x-teleport="body">
        <div
            x-show="modalInfo"
            x-cloak
            x-on:keydown.escape.window="modalInfo = false"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">

            {{-- Backdrop --}}
            <div
                x-show="modalInfo"
                x-transition.opacity
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"
                @click="modalInfo = false">
            </div>

            {{-- Panel --}}
            <div
                x-show="modalInfo"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative bg-white rounded-2xl shadow-xl w-full max-w-md
                       ring-1 ring-slate-900/5 overflow-hidden">

                {{-- Header --}}
                <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-blue-600 mb-0.5">
                            Dimensión {{ $dimensionActual }} de {{ $totalDimensiones }}
                        </p>
                        <h3 class="text-base font-semibold text-slate-900">{{ $dimensionNombre }}</h3>
                    </div>
                    <button
                        @click="modalInfo = false"
                        class="text-slate-400 hover:text-slate-500 transition-colors flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                  clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-5">
                    <p class="text-sm text-slate-600 leading-relaxed">{{ $dimensionDescripcion }}</p>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button
                        @click="modalInfo = false"
                        class="w-full inline-flex items-center justify-center bg-blue-600
                               hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5
                               rounded-xl transition-all duration-200">
                        Entendido
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
