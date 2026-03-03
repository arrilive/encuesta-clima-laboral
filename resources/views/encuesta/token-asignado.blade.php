<x-layouts.encuesta>
    <div class="flex items-center justify-center px-4 py-14 min-h-[calc(100vh-5rem)]" x-data="{ copied: false }">
        <div class="w-full max-w-sm page-enter">

            {{-- Ícono check verde --}}
            <div class="flex justify-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full transition-colors duration-300"
                    :class="copied ? 'bg-emerald-100' : 'bg-emerald-50'">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round" :class="copied ? 'check-pop' : ''">
                        <path d="M20 6L9 17l-5-5" />
                    </svg>
                </div>
            </div>

            {{-- Título --}}
            <h1 class="text-[1.6rem] font-bold text-slate-900 text-center tracking-tight mb-2">
                Tu código personal
            </h1>
            <p class="text-sm text-slate-500 text-center leading-relaxed mb-6">
                Guarda este código, te servirá por si necesitas retomar la encuesta.
            </p>

            {{-- Token card — clickeable para copiar --}}
            <div class="bg-blue-50 border-2 border-blue-200 hover:border-blue-400 rounded-2xl p-6 text-center
                       cursor-pointer transition-all duration-200 hover:bg-blue-100 mb-4"
                @click="
                    navigator.clipboard.writeText('{{ $encuesta->token }}').then(() => {
                        copied = true;
                        setTimeout(() => copied = false, 2500);
                    }); 
                "
                title="Haz clic para copiar">
                <p class="font-mono text-sm font-semibold text-blue-700 break-all leading-relaxed select-all">
                    {{ $encuesta->token }}
                </p>
                <div class="mt-3 flex items-center justify-center gap-1.5 text-xs font-medium"
                    :class="copied ? 'text-emerald-600' : 'text-blue-500'">
                    {{-- Ícono copia / check --}}
                    <template x-if="!copied">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2" />
                            <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
                        </svg>
                    </template>
                    <template x-if="copied">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5" />
                        </svg>
                    </template>
                    <span x-text="copied ? '¡Copiado!' : 'Haz clic para copiar'"></span>
                </div>
            </div>

            {{-- Alerta --}}
            <div class="flex items-start sm:items-center p-4 mb-6 text-sm text-amber-800 rounded-lg bg-amber-50 border border-amber-200"
                role="alert">
                <svg class="w-4 h-4 mr-2 shrink-0 mt-0.5 sm:mt-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <p><span class="font-medium mr-1">¡Atención!</span> Este código es único. No lo compartas con nadie.</p>
            </div>

            {{-- Botón continuar --}}
            <a href="{{ route('encuesta.demograficos', $encuesta->token) }}"
                class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                       text-white font-semibold text-sm px-6 py-3 rounded-xl
                       transition-all duration-200 hover:-translate-y-px
                       hover:shadow-[0_4px_16px_rgba(37,99,235,.25)]
                       active:translate-y-0 active:shadow-none">
                Continuar
            </a>

        </div>

        {{-- Toast "Código copiado" (fixed bottom-center) --}}
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 pointer-events-none" x-show="copied"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>
            <div
                class="flex items-center gap-2 bg-slate-900 text-white text-sm font-medium px-5 py-3 rounded-xl shadow-lg">
                Código copiado
            </div>
        </div>

    </div>
</x-layouts.encuesta>
