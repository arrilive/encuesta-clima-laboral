    <div class="space-y-8">

        {{-- ── CLIMA (protagonista) ─────────────────────────────────────────── --}}
        <div>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">
                    @if(auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value)
                        Resumen general
                    @else
                        Clima laboral
                    @endif
                </p>
                
                {{-- Contenedor de filtros --}}
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Corporativo (Solo super_admin) --}}
                    @if(auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value)
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm font-medium text-slate-400">Corporativo:</span>
                            <select wire:model.live="filtroCorporativoId"
                                class="border-slate-300 rounded-xl text-sm py-2 pl-3 pr-10 bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 shadow-sm font-semibold text-slate-700 cursor-pointer transition-all">
                                <option value="">Todos</option>
                                @foreach($corporativos as $corp)
                                    <option value="{{ $corp->id }}">{{ $corp->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Empresa (super_admin y admin_corporativo) --}}
                    @if(in_array(auth()->user()->role, [\App\Enums\Role::SUPER_ADMIN->value, \App\Enums\Role::ADMIN_CORPORATIVO->value]))
                        @php
                            $empresaDeshabilitada = auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value && !$filtroCorporativoId;
                        @endphp
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm font-medium text-slate-400">Empresa:</span>
                            <select wire:model.live="filtroEmpresaId"
                                @if($empresaDeshabilitada) disabled @endif
                                class="border-slate-300 rounded-xl text-sm py-2 pl-3 pr-10 bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 shadow-sm font-semibold text-slate-700 cursor-pointer transition-all {{ $empresaDeshabilitada ? 'opacity-50 cursor-not-allowed bg-slate-50' : '' }}">
                                <option value="">Todas</option>
                                @foreach($empresas as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Sucursal (super_admin, admin_corporativo y admin_empresa) --}}
                    @if(auth()->user()->role !== \App\Enums\Role::ADMIN_SUCURSAL->value)
                        @php
                            $sucursalDeshabilitada = in_array(auth()->user()->role, [
                                \App\Enums\Role::SUPER_ADMIN->value,
                                \App\Enums\Role::ADMIN_CORPORATIVO->value,
                            ]) && !$filtroEmpresaId;
                        @endphp
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm font-medium text-slate-400">Sucursal:</span>
                            <select wire:model.live="filtroSucursalId"
                                @if($sucursalDeshabilitada) disabled @endif
                                class="border-slate-300 rounded-xl text-sm py-2 pl-3 pr-10 bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 shadow-sm font-semibold text-slate-700 cursor-pointer transition-all {{ $sucursalDeshabilitada ? 'opacity-50 cursor-not-allowed bg-slate-50' : '' }}">
                                <option value="">Todas</option>
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Lote / Período --}}
                    @if($lotes->isNotEmpty())
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm font-medium text-slate-400">Período:</span>
                            <select wire:model.live="filtroLoteId"
                                class="border-slate-300 rounded-xl text-sm py-2 pl-3 pr-10 bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 shadow-sm font-semibold text-slate-700 cursor-pointer transition-all">
                                <option value="">Todos los períodos</option>
                                @foreach ($lotes as $lote)
                                    @php
                                        $nombreEmpresa = auth()->user()->role === \App\Enums\Role::ADMIN_CORPORATIVO->value 
                                            ? '[' . $lote->empresa->nombre . '] ' 
                                            : '';
                                    @endphp
                                    <option value="{{ $lote->id }}">
                                        {{ $nombreEmpresa }}{{ $lote->nombre ?? 'Lote #'.$lote->id }} ({{ $lote->sucursal ? $lote->sucursal->nombre : 'General' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>

            @if(in_array(auth()->user()->role, [
                \App\Enums\Role::ADMIN_EMPRESA->value,
                \App\Enums\Role::ADMIN_CORPORATIVO->value,
                \App\Enums\Role::ADMIN_SUCURSAL->value,
            ]))
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Promedio General — tarjeta hero --}}
                    <div class="bg-white rounded-2xl border border-slate-200 p-8">
                        <div class="flex justify-between h-full">
                            {{-- Izquierda: label + numero --}}
                            <div class="flex flex-col justify-between">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Promedio general</p>
                                <div class="mt-4">
                                    @if($clima['promedio_general'] > 0)
                                        <p class="text-4xl font-extrabold text-slate-900 leading-none">
                                            {{ number_format($clima['promedio_general'], 1) }}
                                        </p>
                                    @else
                                        <p class="text-3xl font-bold text-slate-300 leading-none">Sin datos</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Derecha: badge + accion --}}
                            <div class="flex flex-col items-end justify-between text-right">
                                @if($clima['promedio_general'] > 0)
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
                                @endif

                                <a href="{{ route('admin.reportes') }}"
                                   class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors mt-auto">
                                    Ver análisis completo
                                    <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 18 15 12 9 6"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
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
                                <span class="text-2xl font-bold text-emerald-600 tabular-nums">{{ number_format($clima['dimension_alta']['puntaje'], 1) }}</span>
                            </div>
                            <div class="border-t border-slate-100"></div>
                        @endif
                        @if($clima['dimension_baja'])
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-xs text-slate-400 mb-0.5">Más baja</p>
                                    <p class="text-sm font-semibold text-slate-700">{{ $clima['dimension_baja']['nombre'] }}</p>
                                </div>
                                <span class="text-2xl font-bold text-red-500 tabular-nums">{{ number_format($clima['dimension_baja']['puntaje'], 1) }}</span>
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
                                <span class="text-2xl font-bold text-emerald-600 tabular-nums">{{ number_format($clima['subdimension_alta']['puntaje'], 1) }}</span>
                            </div>
                            <div class="border-t border-slate-100"></div>
                        @endif
                        @if($clima['subdimension_baja'])
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-xs text-slate-400 mb-0.5">Más baja</p>
                                    <p class="text-sm font-semibold text-slate-700">{{ $clima['subdimension_baja']['nombre'] }}</p>
                                </div>
                                <span class="text-2xl font-bold text-red-500 tabular-nums">{{ number_format($clima['subdimension_baja']['puntaje'], 1) }}</span>
                            </div>
                        @endif
                        @if(!$clima['subdimension_alta'] && !$clima['subdimension_baja'])
                            <p class="text-sm text-slate-400">Sin datos suficientes</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        @if(in_array(auth()->user()->role, [
            \App\Enums\Role::ADMIN_EMPRESA->value,
            \App\Enums\Role::ADMIN_CORPORATIVO->value,
            \App\Enums\Role::ADMIN_SUCURSAL->value,
        ]))
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

                {{-- Tokens sin actividad — advertencia progresiva --}}
                @if($kpis['en_riesgo'] > 0)
                    {{-- Riesgo rojo: 14+ días — acción disponible --}}
                    <div x-data="{ confirmar: false }"
                         class="rounded-2xl border border-red-200 bg-red-50 p-6">
                        <div class="flex justify-between h-full">
                            {{-- Izquierda: label + número --}}
                            <div class="flex flex-col justify-between">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">
                                    En riesgo <span class="normal-case font-normal">(+14 días)</span>
                                </p>
                                <p class="text-3xl font-bold text-red-500 tabular-nums">
                                    {{ $kpis['en_riesgo'] }}
                                </p>
                            </div>

                            {{-- Derecha: texto de apoyo + acción --}}
                            <div class="flex flex-col items-end justify-between text-right">
                                {{-- Texto de apoyo — cambia según estado --}}
                                <p x-show="!confirmar" class="text-xs text-red-500 font-medium">
                                    Más de 14 días sin actividad
                                </p>
                                <p x-show="confirmar" x-cloak class="text-xs text-red-700 font-medium">
                                    ¿Confirmas liberar {{ $kpis['en_riesgo'] }} token(s)?
                                </p>

                                {{-- Acción — cambia según estado --}}
                                <div x-show="!confirmar">
                                    <button x-on:click="confirmar = true"
                                            class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs px-4 py-2 rounded-xl transition-all duration-200">
                                        Liberar tokens
                                    </button>
                                </div>
                                <div x-show="confirmar" x-cloak class="flex gap-2">
                                    <button wire:click="liberarTokens"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs px-4 py-2 rounded-xl transition-all duration-200 disabled:opacity-75">
                                        <svg wire:loading wire:target="liberarTokens" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                                            <path d="M12 2a10 10 0 0 1 10 10"/>
                                        </svg>
                                        <span wire:loading.remove wire:target="liberarTokens">Sí, liberar</span>
                                        <span wire:loading wire:target="liberarTokens">Liberando…</span>
                                    </button>
                                    <button x-on:click="confirmar = false"
                                            class="text-xs font-medium text-slate-500 hover:text-slate-700 px-3 py-2 rounded-xl transition-colors">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($kpis['en_advertencia'] > 0)
                    {{-- Advertencia amarilla: 7-13 días — solo informativo --}}
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
                        <div class="flex justify-between h-full">
                            <div class="flex flex-col justify-between">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">
                                    Sin actividad <span class="normal-case font-normal">(+7 días)</span>
                                </p>
                                <p class="text-3xl font-bold text-amber-500 tabular-nums">
                                    {{ $kpis['en_advertencia'] }}
                                </p>
                            </div>
                            <div class="flex flex-col items-end justify-between text-right">
                                <p class="text-xs text-amber-600 font-medium">Monitorear</p>
                                <p class="text-xs text-amber-600 font-medium">Aún no requieren acción</p>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Todo en orden --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-6">
                        <div class="flex justify-between h-full">
                            <div class="flex flex-col justify-between">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">
                                    Sin actividad
                                </p>
                                <p class="text-3xl font-bold text-slate-300 tabular-nums">0</p>
                            </div>
                            <div class="flex flex-col items-end justify-between text-right">
                                <p class="text-xs text-slate-400 font-medium">Todos los tokens activos</p>
                            </div>
                        </div>
                    </div>
                @endif

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
                    <p class="text-sm text-slate-400 mb-1">Completados</p>
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

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <p class="text-sm text-slate-400 mb-1">Disponibles</p>
                    <p class="text-3xl font-bold text-slate-500 tabular-nums">
                        {{ $kpis['disponibles'] }}
                    </p>
                </div>

            </div>
        </div>

        {{-- ── RANKING EMPRESAS (solo super_admin) ──────────────────────────── --}}
        @if(in_array(auth()->user()->role, [
            \App\Enums\Role::SUPER_ADMIN->value,
            \App\Enums\Role::ADMIN_CORPORATIVO->value,
        ]) && $rankingEmpresas->isNotEmpty())
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
                                    <span class="text-sm font-bold text-slate-900 w-10 text-right tabular-nums">{{ number_format($empresa['puntaje'], 1) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>
