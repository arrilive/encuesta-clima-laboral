<x-layouts.encuesta>
    <div class="max-w-lg mx-auto px-4 py-12 page-enter">

        {{-- Encabezado --}}
        <div class="text-center mb-10">
            <h1 class="text-[1.7rem] font-bold text-slate-900 tracking-tight leading-tight mb-2">
                Tu encuesta
            </h1>
            <p class="text-sm text-slate-500 leading-relaxed">
                Completa cada bloque en orden. Tus respuestas se guardan automáticamente.
            </p>
        </div>

        {{-- Cards de dimensiones --}}
        <div class="space-y-4">
            @php
                $gradientes = [
                    1 => 'from-blue-600 to-blue-500',
                    2 => 'from-green-600 to-green-500', 
                    3 => 'from-amber-500 to-amber-400',
                    4 => 'from-red-600 to-red-500', 
                    5 => 'from-purple-600 to-purple-500', 
                    6 => 'from-cyan-500 to-cyan-400', 
                ];

                $glowColors = [
                    1 => 'rgba(37,99,235,0.35)', 
                    2 => 'rgba(22,163,74,0.35)', 
                    3 => 'rgba(245,158,11,0.35)', 
                    4 => 'rgba(220,38,38,0.35)', 
                    5 => 'rgba(147,51,234,0.35)', 
                    6 => 'rgba(6,182,212,0.35)', 
                ];

                $iconPaths = [
                    1 => 'M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 01-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 10.203 4.167 9.75 5 9.75h1.053c.472 0 .745.556.5.96a8.958 8.958 0 00-1.302 4.665c0 1.194.232 2.333.654 3.375z',
                    2 => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z',
                    3 => 'M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.97zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.97z',
                    4 => 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z',
                    5 => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
                    6 => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.175-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
                ];
            @endphp

            @foreach ($dimensiones as $dim)
                @php
                    $gradiente = $gradientes[$dim->orden] ?? 'from-slate-600 to-slate-500';
                    $iconPath = $iconPaths[$dim->orden] ?? '';
                    $porcentaje = $dim->total > 0 ? round(($dim->respondidas / $dim->total) * 100) : 0;

                    $esCompletada = $dim->completada;
                    $esDisponible = $dim->orden === $disponibleOrden;
                    $esBloqueada = $dim->orden > $disponibleOrden;
                @endphp

                <div class="card-wrap">
                    @if ($esCompletada || $esDisponible)
                        {{-- Card activa (completada o disponible) --}}
                        @if ($esDisponible)
                            <a href="{{ route('encuesta.bloque', ['token' => $token, 'dimension' => $dim->orden]) }}"
                                class="block">
                            @else
                                <div>
                        @endif
                        <div class="relative bg-gradient-to-r {{ $gradiente }} rounded-2xl p-5 overflow-hidden
                                    {{ $esCompletada ? 'card-completada' : 'card-disponible' }}"
                            style="--glow-color: {{ $glowColors[$dim->orden] ?? 'rgba(37,99,235,0.35)' }}">

                            {{-- Badge completado --}}
                            @if ($esCompletada)
                                <div
                                    class="absolute top-3 right-3 bg-white/20 backdrop-blur-sm rounded-full
                                            flex items-center gap-1.5 px-2.5 py-1 check-bounce">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white"
                                        stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 6L9 17l-5-5" />
                                    </svg>
                                    <span
                                        class="text-white text-[0.6rem] font-semibold tracking-wide uppercase">Completado</span>
                                </div>
                            @endif

                            {{-- Contenido --}}
                            <div class="flex items-start gap-4">
                                {{-- Ícono --}}
                                <div
                                    class="flex-shrink-0 w-11 h-11 bg-white/15 rounded-xl
                                            flex items-center justify-center mt-0.5">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white"
                                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="{{ $iconPath }}" />
                                    </svg>
                                </div>

                                <div class="flex-1 min-w-0">
                                    {{-- Nombre --}}
                                    <h3 class="text-white font-bold text-base leading-snug mb-1">
                                        {{ $dim->nombre }}
                                    </h3>

                                    {{-- Pills de subdimensiones --}}
                                    <div class="flex flex-wrap gap-1.5 mb-3">
                                        @foreach ($dim->subdimensiones as $sub)
                                            <span
                                                class="inline-block bg-white/20 text-white/90 text-[0.65rem]
                                                         font-medium px-2 py-0.5 rounded-full leading-snug">
                                                {{ $sub->nombre }}
                                            </span>
                                        @endforeach
                                    </div>

                                    {{-- Barra de progreso --}}
                                    <div class="w-full bg-white/20 rounded-full h-1.5">
                                        <div class="bg-white h-1.5 rounded-full transition-all duration-500"
                                            style="width: {{ $porcentaje }}%"></div>
                                    </div>
                                    @if ($esCompletada)
                                        <p class="text-white/80 text-xs mt-1.5 flex items-center gap-1">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M20 6L9 17l-5-5" />
                                            </svg>
                                            {{ $dim->total }} preguntas completadas
                                        </p>
                                    @else
                                        <p class="text-white/70 text-xs mt-1.5">
                                            {{ $dim->respondidas }}/{{ $dim->total }} preguntas
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if ($esDisponible)
                            </a>
                        @else
                </div>
            @endif
        @else
            {{-- Card bloqueada --}}
            <div class="relative bg-slate-100 border border-slate-200 rounded-2xl p-5 cursor-not-allowed opacity-60">

                {{-- Badge candado --}}
                <div
                    class="absolute top-4 right-4 w-7 h-7 bg-slate-200 rounded-full
                                    flex items-center justify-center">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0110 0v4" />
                    </svg>
                </div>

                {{-- Contenido --}}
                <div class="flex items-start gap-4">
                    <div
                        class="flex-shrink-0 w-11 h-11 bg-slate-200 rounded-xl
                                        flex items-center justify-center mt-0.5">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94a3b8"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="{{ $iconPath }}" />
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-base leading-snug mb-1">
                            {{ $dim->nombre }}
                        </h3>

                        <div class="flex flex-wrap gap-1.5 mb-3">
                            @foreach ($dim->subdimensiones as $sub)
                                <span
                                    class="inline-block bg-slate-200 text-[0.65rem]
                                                     font-medium px-2 py-0.5 rounded-full leading-snug">
                                    {{ $sub->nombre }}
                                </span>
                            @endforeach
                        </div>

                        <div class="w-full bg-slate-200 rounded-full h-1.5">
                            <div class="bg-slate-300 h-1.5 rounded-full" style="width: 0%"></div>
                        </div>
                        <p class="text-slate-400 text-xs mt-2 flex items-center gap-1.5">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0110 0v4" />
                            </svg>
                            Completa el bloque anterior para desbloquear
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    </div>
</x-layouts.encuesta>
