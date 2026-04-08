    <div class="space-y-8">

        @if(auth()->user()->role === 'admin_empresa')
        {{-- ── CLIMA (protagonista) ─────────────────────────────────────────── --}}
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Clima laboral</p>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Promedio General — tarjeta hero --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-8 flex flex-col justify-between">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Promedio general</p>
                    <div class="my-4">
                        @if($clima['promedio_general'] > 0)
                            <p class="text-4xl font-extrabold text-slate-900 leading-none mb-3">
                                {{ $clima['promedio_general'] }}
                            </p>
                            @php
                                $p = $clima['promedio_general'];
                                [$badgeColor, $badgeText] = match(true) {
                                    $p >= 80 => ['bg-emerald-100 text-emerald-700', 'Excelente'],
                                    $p >= 51 => ['bg-blue-100 text-blue-700',       'Buen clima'],
                                    $p >= 25 => ['bg-amber-100 text-amber-700',     'Regular'],
                                    default  => ['bg-red-100 text-red-700',         'Deficiente'],
                                };
                            @endphp
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $badgeColor }}">
                                {{ $badgeText }}
                            </span>
                        @else
                            <p class="text-3xl font-bold text-slate-300">Sin datos</p>
                        @endif
                    </div>
                    <a href="{{ route('admin.reportes') }}"
                       class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                        Ver análisis completo
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                </div>

                {{-- Dimensiones destacadas --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-6 flex flex-col gap-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Dimensiones destacadas</p>
                    @if($clima['dimension_alta'])
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs text-slate-400 mb-0.5">Más alta</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $clima['dimension_alta']['nombre'] }}</p>
                            </div>
                            <span class="text-2xl font-bold text-emerald-600 tabular-nums">{{ $clima['dimension_alta']['puntaje'] }}</span>
                        </div>
                        <div class="border-t border-slate-100"></div>
                    @endif
                    @if($clima['dimension_baja'])
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs text-slate-400 mb-0.5">Más baja</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $clima['dimension_baja']['nombre'] }}</p>
                            </div>
                            <span class="text-2xl font-bold text-red-500 tabular-nums">{{ $clima['dimension_baja']['puntaje'] }}</span>
                        </div>
                    @endif
                    @if(!$clima['dimension_alta'] && !$clima['dimension_baja'])
                        <p class="text-sm text-slate-400">Sin datos suficientes</p>
                    @endif
                </div>

                {{-- Subdimensiones destacadas --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-6 flex flex-col gap-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Subdimensiones destacadas</p>
                    @if($clima['subdimension_alta'])
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs text-slate-400 mb-0.5">Más alta</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $clima['subdimension_alta']['nombre'] }}</p>
                            </div>
                            <span class="text-2xl font-bold text-emerald-600 tabular-nums">{{ $clima['subdimension_alta']['puntaje'] }}</span>
                        </div>
                        <div class="border-t border-slate-100"></div>
                    @endif
                    @if($clima['subdimension_baja'])
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs text-slate-400 mb-0.5">Más baja</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $clima['subdimension_baja']['nombre'] }}</p>
                            </div>
                            <span class="text-2xl font-bold text-red-500 tabular-nums">{{ $clima['subdimension_baja']['puntaje'] }}</span>
                        </div>
                    @endif
                    @if(!$clima['subdimension_alta'] && !$clima['subdimension_baja'])
                        <p class="text-sm text-slate-400">Sin datos suficientes</p>
                    @endif
                </div>

            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'admin_empresa')
        {{-- ── PARTICIPACIÓN Y ALERTAS ──────────────────────────────────────── --}}
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Participación</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Tasa participación — más ancha, es el KPI operativo más importante --}}
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Tasa de participación</p>
                    <div class="flex items-end justify-between mb-3">
                        <p class="text-3xl font-bold text-emerald-600 tabular-nums">{{ $kpis['tasa_participacion'] }}%</p>
                        <p class="text-sm text-slate-400 mb-1 tabular-nums">{{ $kpis['completadas'] }} / {{ $kpis['total_tokens'] }}</p>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-700"
                             style="width: {{ $kpis['tasa_participacion'] }}%"></div>
                    </div>
                </div>

                {{-- Tokens en riesgo —alerta cuando hay pendientes viejos --}}
                <div class="bg-white rounded-2xl border p-6 {{ $kpis['en_riesgo'] > 0 ? 'border-red-200 bg-red-50' : 'border-slate-200' }}">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">
                        En riesgo <span class="normal-case font-normal">(+7 días)</span>
                    </p>
                    <p class="text-3xl font-bold tabular-nums {{ $kpis['en_riesgo'] > 0 ? 'text-red-500' : 'text-slate-300' }}">
                        {{ $kpis['en_riesgo'] }}
                    </p>
                    @if($kpis['en_riesgo'] > 0)
                        <p class="text-xs text-red-500 font-medium mt-1">Requieren seguimiento</p>
                    @endif
                </div>

            </div>
        </div>
        @endif

        {{-- ── TOKENS (detalle operativo — peso visual menor) ───────────────── --}}
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Tokens</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <p class="text-sm text-slate-400 mb-1">Total</p>
                    <p class="text-3xl font-bold text-slate-900 tabular-nums">{{ $kpis['total_tokens'] }}</p>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <p class="text-sm text-slate-400 mb-1">Completadas</p>
                    <p class="text-3xl font-bold text-emerald-600 tabular-nums">{{ $kpis['completadas'] }}</p>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <p class="text-sm text-slate-400 mb-1">En progreso</p>
                    <p class="text-3xl font-bold text-blue-600 tabular-nums">{{ $kpis['en_progreso'] }}</p>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <p class="text-sm text-slate-400 mb-1">Asignados</p>
                    <p class="text-3xl font-bold text-amber-500 tabular-nums">{{ $kpis['asignados'] }}</p>
                </div>

                <div class="bg-white rounded-xl border p-5 {{ $kpis['alerta_tokens'] ? 'border-amber-300 bg-amber-50' : 'border-slate-200' }}">
                    <p class="text-sm text-slate-400 mb-1">Disponibles</p>
                    <p class="text-3xl font-bold tabular-nums {{ $kpis['alerta_tokens'] ? 'text-amber-500' : 'text-slate-500' }}">
                        {{ $kpis['disponibles'] }}
                    </p>
                    @if($kpis['alerta_tokens'])
                        <div class="flex flex-col">
                            <p class="text-xs text-amber-600 font-medium mt-1">⚠ Pocos disponibles</p>
                            <a href="{{ route('admin.tokens') }}"
                               class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 hover:text-amber-700 transition-colors mt-1">
                                Generar tokens
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"/>
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- ── RANKING EMPRESAS (solo super_admin) ──────────────────────────── --}}
        @if(auth()->user()->role === 'super_admin' && $rankingEmpresas->isNotEmpty())
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Ranking de empresas</p>
                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <div class="divide-y divide-slate-100">
                        @foreach($rankingEmpresas as $i => $empresa)
                            <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-bold text-slate-300 w-6 text-center tabular-nums">{{ $i + 1 }}</span>
                                    <span class="text-sm font-medium text-slate-700">{{ $empresa['nombre'] }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-32 bg-slate-100 rounded-full h-1.5 hidden sm:block">
                                        <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $empresa['puntaje'] }}%"></div>
                                    </div>
                                    <span class="text-sm font-bold text-slate-900 w-10 text-right tabular-nums">{{ $empresa['puntaje'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>
