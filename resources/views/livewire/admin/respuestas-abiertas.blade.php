<div class="bg-white rounded-2xl shadow-sm p-4">

    {{-- Header con toggle --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-slate-800 tracking-tight">Respuestas abiertas</h2>
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
            @if($bajoUmbral)
                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                    <div class="flex items-center justify-center w-14 h-14 bg-slate-100 rounded-2xl mb-5">
                        <svg class="w-7 h-7 text-slate-400" xmlns="http://www.w3.org/2000/svg"
                             fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25
                                     2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25
                                     2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-700 mb-1">Comentarios protegidos</p>
                    <p class="text-sm text-slate-400 max-w-sm">
                        Se necesitan al menos {{ $umbralRespuestasAbiertas }} respuestas para mostrar
                        los comentarios y proteger la confidencialidad de los participantes.
                        Este segmento tiene <span class="font-semibold text-slate-600">
                        {{ $totalRespondientes }} {{ $totalRespondientes === 1 ? 'respuesta' : 'respuestas' }}
                        </span> completada{{ $totalRespondientes === 1 ? '' : 's' }}.
                    </p>
                </div>
            @else
                @if($preguntasAbiertas->isEmpty())
                    <div class="text-center py-10">
                        <p class="text-slate-400 text-sm">No hay preguntas abiertas configuradas para esta encuesta.</p>
                    </div>
                @else
                    {{-- Tabs de preguntas --}}
                    <div role="tablist" class="flex gap-2 flex-wrap mb-6 border-b border-slate-100 pb-3">
                        @foreach ($preguntasAbiertas as $preguntaAbierta)
                            <button wire:click="seleccionarPreguntaAbierta({{ $preguntaAbierta->id }})"
                                role="tab"
                                id="tab-{{ $preguntaAbierta->id }}"
                                aria-selected="{{ $preguntaAbiertaActiva === $preguntaAbierta->id ? 'true' : 'false' }}"
                                class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                       {{ $preguntaAbiertaActiva === $preguntaAbierta->id
                                           ? 'bg-blue-600 text-white shadow-sm'
                                           : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100' }}">
                                {{ $preguntaAbierta->texto }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Lista de respuestas --}}
                    <div role="tabpanel" aria-labelledby="tab-{{ $preguntaAbiertaActiva }}">
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
                                {{ $respuestasAbiertas->links(data: ['scrollTo' => false]) }}
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
            @endif
        </div>
    @endif

</div>
