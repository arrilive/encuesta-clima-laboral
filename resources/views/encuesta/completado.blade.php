<x-layouts.encuesta>
    <div class="flex items-center justify-center px-4 py-14 min-h-[calc(100vh-5rem)]">
        <div class="w-full max-w-sm sm:max-w-md text-center page-enter">

            {{-- Ícono de éxito --}}
            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-emerald-50 mx-auto mb-6">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6L9 17l-5-5" />
                </svg>
            </div>

            {{-- Título --}}
            <h1 class="text-[1.7rem] font-bold text-slate-900 tracking-tight leading-tight mb-2">
                ¡Completaste {{ $dimensionActual->nombre }}!
            </h1>

            {{-- Subtítulo motivacional --}}
            <p class="text-sm text-slate-500 leading-relaxed mb-8">
                @if ($siguienteDimension)
                    @php
                        $frases = [
                            'Excelente, cada dimensión cuenta.',
                            '¡Vas muy bien! Tu opinión hace la diferencia.',
                            '¡Genial! Sigue así, cada respuesta suma.',
                            '¡Buen trabajo! Tu participación es muy valiosa.',
                            '¡Avanzando con todo! Gracias por tu tiempo.',
                            '¡Muy bien! Tu opinión es importante en cada dimensión.',
                            '¡Excelente progreso! Tu opinión nos ayuda a mejorar.',
                        ];
                    @endphp
                    {{ $frases[array_rand($frases)] }}
                @else
                    ¡Ya casi terminas! Solo faltan unas preguntas finales.
                @endif
            </p>

            @if ($siguienteDimension)
                {{-- Preview de la siguiente dimensión --}}
                @php
                    $gradientes = [
                        1 => 'from-blue-600 to-blue-500',
                        2 => 'from-green-600 to-green-500',
                        3 => 'from-amber-800 to-amber-700',
                        4 => 'from-red-600 to-red-500',
                        5 => 'from-purple-600 to-purple-500',
                        6 => 'from-cyan-800 to-cyan-700',
                    ];

                    $iconPaths = [
                        1 => 'M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 01-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 10.203 4.167 9.75 5 9.75h1.053c.472 0 .745.556.5.96a8.958 8.958 0 00-1.302 4.665c0 1.194.232 2.333.654 3.375z',
                        2 => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z',
                        3 => 'M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.97zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.97z',
                        4 => 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z',
                        5 => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
                        6 => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.175-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
                    ];

                    $gradiente = $gradientes[$siguienteDimension->orden] ?? 'from-slate-600 to-slate-500';
                    $iconPath = $iconPaths[$siguienteDimension->orden] ?? '';
                @endphp

                <div class="bg-gradient-to-r {{ $gradiente }} rounded-2xl p-5 mb-6 text-left">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex-shrink-0 w-11 h-11 bg-white/15 rounded-xl
                                    flex items-center justify-center">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="{{ $iconPath }}" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-white/70 text-xs font-medium mb-0.5">Siguiente dimensión</p>
                            <h3 class="text-white font-bold text-base leading-snug">
                                {{ $siguienteDimension->nombre }}
                            </h3>
                        </div>
                    </div>
                </div>

                <a href="{{ route('encuesta.bloque', ['token' => $token, 'dimension' => $siguienteDimension->orden]) }}"
                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                          text-white font-semibold text-sm px-4 sm:px-6 py-3 rounded-xl
                          transition-all duration-200 hover:-translate-y-px
                          hover:shadow-[0_4px_16px_rgba(37,99,235,.25)]
                          active:translate-y-0 active:shadow-none">
                    <span class="text-center leading-snug">Comenzar {{ $siguienteDimension->nombre }} →</span>
                </a>
            @else
                {{-- Última dimensión completada --}}
                <a href="{{ route('encuesta.abiertas', $token) }}"
                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                          text-white font-semibold text-sm px-4 sm:px-6 py-3 rounded-xl
                          transition-all duration-200 hover:-translate-y-px
                          hover:shadow-[0_4px_16px_rgba(37,99,235,.25)]
                          active:translate-y-0 active:shadow-none">
                    <span class="text-center leading-snug">Ir a preguntas finales →</span>
                </a>
            @endif

        </div>
    </div>
</x-layouts.encuesta>
