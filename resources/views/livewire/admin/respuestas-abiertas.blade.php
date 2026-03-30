<div class="bg-white rounded-2xl shadow-sm p-4">

    {{-- Header con toggle --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-slate-900 font-semibold">Respuestas abiertas</h2>
            <p class="text-slate-500 text-sm mt-0.5">Comentarios textuales de los encuestados</p>
        </div>
        <button wire:click="toggleRespuestasAbiertas"
            class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600
                   hover:text-blue-700 transition-colors duration-200">
            <span>{{ $mostrarRespuestasAbiertas ? 'Ocultar' : 'Ver respuestas' }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor"
                class="w-4 h-4 transition-transform duration-200
                       {{ $mostrarRespuestasAbiertas ? 'rotate-90' : '' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        </button>
    </div>

    {{-- Contenido colapsable --}}
    @if ($mostrarRespuestasAbiertas)
        <div class="mt-6 page-enter">

            {{-- Tabs de preguntas --}}
            <div class="flex gap-2 flex-wrap mb-6 border-b border-slate-100 pb-3">
                @foreach ($preguntasAbiertas as $preguntaAbierta)
                    <button wire:click="seleccionarPreguntaAbierta({{ $preguntaAbierta->id }})"
                        class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200
                               {{ $preguntaAbiertaActiva === $preguntaAbierta->id
                                   ? 'bg-blue-600 text-white shadow-sm'
                                   : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100' }}">
                        {{ $preguntaAbierta->texto }}
                    </button>
                @endforeach
            </div>

            {{-- Lista de respuestas --}}
            @if ($respuestasAbiertas && $respuestasAbiertas->count() > 0)
                <div class="space-y-3 page-enter" wire:key="respuestas-lista-{{ $preguntaAbiertaActiva }}-page-{{ $respuestasAbiertas->currentPage() }}">
                    @foreach ($respuestasAbiertas as $respuesta)
                        <div class="bg-slate-50 rounded-xl px-5 py-4 border border-slate-100">
                            <p class="text-slate-700 text-sm leading-relaxed">
                                {{ $respuesta->texto }}
                            </p>
                        </div>
                    @endforeach
                </div>

                {{-- Paginación --}}
                <div class="mt-6">
                    {{ $respuestasAbiertas->links() }}
                </div>
            @else
                <div class="text-center py-10 page-enter" wire:key="sin-respuestas-{{ $preguntaAbiertaActiva }}">
                    <p class="text-slate-400 text-sm">
                        No hay respuestas para esta pregunta con los filtros seleccionados.
                    </p>
                </div>
            @endif

        </div>
    @endif

</div>
