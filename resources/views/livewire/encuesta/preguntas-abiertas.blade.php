<div class="max-w-2xl mx-auto py-8 px-4">

    {{-- Header --}}
    <div class="text-center mb-8 page-enter">
        <h1 class="text-[1.7rem] font-bold text-slate-900 tracking-tight leading-tight mb-2">
            ¡Ya casi terminamos!
        </h1>
        <p class="text-sm text-slate-500 leading-relaxed">
            Compártenos tu opinión final. Nos ayuda a mejorar.
        </p>
    </div>

    {{-- Preguntas abiertas --}}
    <div class="space-y-4">
        @foreach($preguntas as $pregunta)
            <div x-data="{ count: {{ strlen($respuestas[$pregunta->id] ?? '') }} }" class="bg-white border border-slate-200 rounded-2xl p-6">

                {{-- Label --}}
                <label for="pregunta-abierta-{{ $pregunta->id }}" class="block text-sm font-semibold text-slate-700 leading-relaxed mb-3">
                    {{ $pregunta->texto }}
                    <span class="text-xs font-normal text-slate-400 ml-1">(opcional)</span>
                </label>

                {{-- Textarea --}}
                <textarea
                    id="pregunta-abierta-{{ $pregunta->id }}"
                    wire:model.live.debounce.800ms="respuestas.{{ $pregunta->id }}"
                    x-on:input="count = $event.target.value.length"
                    maxlength="300"
                    rows="4"
                    placeholder="Escribe tu respuesta aquí…"
                    class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900
                           placeholder-slate-400 bg-white resize-none
                           focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10
                           transition-all duration-200"></textarea>

                {{-- Contador de caracteres --}}
                <p class="text-right text-xs mt-1.5" :class="count >= 300 ? 'text-red-500 font-medium' : (count >= 270 ? 'text-amber-500' : 'text-slate-400')">
                    <span x-text="count"></span>/300
                </p>

            </div>
        @endforeach

        {{-- Botón finalizar --}}
        <div class="pt-4">
            <button
                x-on:click="if (confirm('¿Estás seguro? Una vez finalizada no podrás modificar tus respuestas.')) { $wire.finalizar() }"
                wire:loading.attr="disabled"
                class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                       text-white font-semibold text-sm px-6 py-3 rounded-xl
                       transition-all duration-200 hover:-translate-y-px
                       hover:shadow-[0_4px_16px_rgba(37,99,235,.25)]
                       active:translate-y-0 active:shadow-none
                       disabled:opacity-75 disabled:cursor-not-allowed">
                <svg wire:loading wire:target="finalizar"
                     class="animate-spin flex-shrink-0" width="14" height="14"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                    <path d="M12 2a10 10 0 0 1 10 10"/>
                </svg>
                <span wire:loading.remove wire:target="finalizar">Finalizar encuesta ✓</span>
                <span wire:loading wire:target="finalizar">Guardando…</span>
            </button>
        </div>

    </div>
</div>
