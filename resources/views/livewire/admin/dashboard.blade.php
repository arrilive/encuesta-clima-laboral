    <div class="space-y-8">

        {{-- ── CLIMA (protagonista) ─────────────────────────────────────────── --}}
        <div>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
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
                            <span class="text-sm font-semibold text-slate-600">Corporativo:</span>
                            <div class="w-48 sm:w-56">
                                <x-admin.combobox-entidad
                                    wire-model="filtroCorporativoId"
                                    placeholder="Buscar corporativo..."
                                    :has-error="$errors->has('filtroCorporativoId')"
                                    :disabled="false">
                                    <option value="">Todos</option>
                                    @foreach($corporativos as $corp)
                                        <option value="{{ $corp->id }}">{{ $corp->nombre }}</option>
                                    @endforeach
                                </x-admin.combobox-entidad>
                            </div>
                        </div>
                    @endif

                    {{-- Empresa (super_admin y admin_corporativo) --}}
                    @if(in_array(auth()->user()->role, [\App\Enums\Role::SUPER_ADMIN->value, \App\Enums\Role::ADMIN_CORPORATIVO->value]))
                        @php
                            $empresaDeshabilitada = auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value && !$filtroCorporativoId;
                        @endphp
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm font-semibold text-slate-600">Empresa:</span>
                            <div class="w-48 sm:w-56">
                                <x-admin.combobox-entidad
                                    wire-model="filtroEmpresaId"
                                    placeholder="Buscar empresa..."
                                    :has-error="$errors->has('filtroEmpresaId')"
                                    :disabled="$empresaDeshabilitada">
                                    <option value="">Todas</option>
                                    @foreach($empresas as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                                    @endforeach
                                </x-admin.combobox-entidad>
                            </div>
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
                            <span class="text-sm font-semibold text-slate-600">Sucursal:</span>
                            <div class="w-48 sm:w-56">
                                <x-admin.combobox-entidad
                                    wire-model="filtroSucursalId"
                                    placeholder="Buscar sucursal..."
                                    :has-error="$errors->has('filtroSucursalId')"
                                    :disabled="$sucursalDeshabilitada">
                                    <option value="">Todas</option>
                                    @foreach($sucursales as $suc)
                                        <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                                    @endforeach
                                </x-admin.combobox-entidad>
                            </div>
                        </div>
                    @endif

                    {{-- Lote / Período --}}
                    @if($lotes->isNotEmpty())
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm font-semibold text-slate-600">Período:</span>
                            <div class="w-56 sm:w-64">
                                <x-admin.combobox-entidad
                                    wire-model="filtroLoteId"
                                    placeholder="Buscar período..."
                                    :has-error="$errors->has('filtroLoteId')"
                                    :disabled="false">
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
                                </x-admin.combobox-entidad>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if(in_array(auth()->user()->role, [
                \App\Enums\Role::ADMIN_EMPRESA->value,
                \App\Enums\Role::ADMIN_CORPORATIVO->value,
                \App\Enums\Role::ADMIN_SUCURSAL->value,
            ]))
                {{-- Banners Informativos de Escenarios --}}
                <div class="mb-4 space-y-2">
                    @if($clima['is_multi'])
                        @foreach($clima['banners_multi'] as $mensajeBanner)
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                <span class="flex h-2 w-2 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                                </span>
                                <span>{{ $mensajeBanner }}</span>
                            </div>
                        @endforeach
                    @else
                        @if($clima['escenario'] === 2)
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                <span class="flex h-2 w-2 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                                </span>
                                Ronda <strong>{{ $clima['lote_nombre'] }}</strong> en curso. Resultados parciales.
                            </div>
                        @elseif($clima['escenario'] === 3)
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-50 text-slate-700 border border-slate-200/80">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Estado actual: ronda <strong>{{ $clima['lote_nombre'] }}</strong>. cerrada el {{ $clima['lote_fecha_fin'] }}.
                            </div>
                        @elseif($clima['escenario'] === 4)
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 p-3 rounded-xl text-xs bg-amber-50 text-amber-800 border border-amber-200/80">
                                <div class="flex items-center gap-2 font-medium shrink-0">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Estado actual: ronda <strong>{{ $clima['lote_nombre'] }}</strong>. cerrada el {{ $clima['lote_fecha_fin'] }}.</span>
                                </div>
                                <span class="hidden sm:inline text-amber-300">•</span>
                                <span>Hay una nueva ronda en curso (<strong>{{ $clima['lote_activo_nombre'] }}</strong>), este panorama se actualizará cuando cierre.</span>
                            </div>
                        @endif
                    @endif
                </div>

                @if($clima['sinDatos'])
                    <x-admin.empty-state titulo="Sin datos de clima" mensaje="No hay datos de encuestas disponibles para esta entidad." :conBotonFiltros="false" />
                @else

                    {{-- Grid normal de clima --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {{-- Promedio General — tarjeta hero --}}
                        <div class="bg-white rounded-2xl border border-slate-200 p-8">
                            <div class="flex justify-between h-full">
                                {{-- Izquierda: label + numero --}}
                                <div class="flex flex-col justify-between">
                                    <p class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Promedio general</p>
                                    <div class="mt-4">
                                        @if($clima['promedio_general'] !== null && $clima['promedio_general'] > 0)
                                            <p class="text-3xl font-bold text-slate-900 leading-none tabular-nums">
                                                {{ number_format($clima['promedio_general'], 1) }}
                                            </p>
                                        @else
                                            <p class="text-2xl font-bold text-slate-300 leading-none">Sin datos</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Derecha: badge + accion --}}
                                <div class="flex flex-col items-end justify-between text-right">
                                    @if($clima['promedio_general'] !== null && $clima['promedio_general'] > 0)
                                        @php
                                            $p = $clima['promedio_general'];
                                            $climaBadge = \App\Support\ClimaBadge::resolver($p);
                                        @endphp
                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $climaBadge['standard'] }}">
                                            {{ $climaBadge['label'] }}
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
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Dimensiones destacadas</p>
                            @if($clima['dimension_alta'])
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-slate-500 mb-0.5">Más alta</p>
                                        <p class="text-sm font-medium text-slate-700">{{ $clima['dimension_alta']['nombre'] }}</p>
                                    </div>
                                    <span class="text-2xl font-bold text-emerald-600 tabular-nums">{{ number_format($clima['dimension_alta']['puntaje'], 1) }}</span>
                                </div>
                                <div class="border-t border-slate-100"></div>
                            @endif
                            @if($clima['dimension_baja'])
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-slate-500 mb-0.5">Más baja</p>
                                        <p class="text-sm font-medium text-slate-700">{{ $clima['dimension_baja']['nombre'] }}</p>
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
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Subdimensiones destacadas</p>
                            @if($clima['subdimension_alta'])
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-slate-500 mb-0.5">Más alta</p>
                                        <p class="text-sm font-medium text-slate-700">{{ $clima['subdimension_alta']['nombre'] }}</p>
                                    </div>
                                    <span class="text-2xl font-bold text-emerald-600 tabular-nums">{{ number_format($clima['subdimension_alta']['puntaje'], 1) }}</span>
                                </div>
                                <div class="border-t border-slate-100"></div>
                            @endif
                            @if($clima['subdimension_baja'])
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-slate-500 mb-0.5">Más baja</p>
                                        <p class="text-sm font-medium text-slate-700">{{ $clima['subdimension_baja']['nombre'] }}</p>
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
            @endif
        </div>

        @if(in_array(auth()->user()->role, [
            \App\Enums\Role::ADMIN_EMPRESA->value,
            \App\Enums\Role::ADMIN_CORPORATIVO->value,
            \App\Enums\Role::ADMIN_SUCURSAL->value,
        ]))
        {{-- ── PARTICIPACIÓN Y ALERTAS ──────────────────────────────────────── --}}
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Participación</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Tasa participación — más ancha, es el KPI operativo más importante --}}
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tasa de participación</p>
                    <div class="flex items-end justify-between mb-3">
                        <p class="text-3xl font-bold text-emerald-600 tabular-nums">{{ $kpis['tasa_participacion'] }}%</p>
                        <p class="text-xs font-medium text-slate-500 mb-1 tabular-nums">{{ $kpis['completadas'] }} / {{ $kpis['total_tokens'] }}</p>
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
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                                    En riesgo <span class="normal-case font-normal">(+14 días)</span>
                                </p>
                                <p class="text-2xl font-bold text-red-500 tabular-nums">
                                    {{ $kpis['en_riesgo'] }}
                                </p>
                            </div>

                            {{-- Derecha: texto de apoyo + acción --}}
                            <div class="flex flex-col items-end justify-between text-right">
                                {{-- Texto de apoyo — cambia según estado --}}
                                <p x-show="!confirmar" class="text-xs text-red-500 font-medium">
                                    Más de 14 días sin actividad
                                </p>
                                <p x-show="confirmar" x-cloak class="text-xs text-red-700 font-semibold">
                                    ¿Confirmas liberar {{ $kpis['en_riesgo'] }} token(s)?
                                </p>

                                {{-- Acción — cambia según estado --}}
                                @if(auth()->user()->role !== \App\Enums\Role::ADMIN_CORPORATIVO->value)
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
                                @else
                                    <span class="text-[10px] text-red-400 font-medium mt-auto">
                                        Solo lectura corporativa
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @elseif($kpis['en_advertencia'] > 0)
                    {{-- Advertencia amarilla: 7-13 días — solo informativo --}}
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
                        <div class="flex justify-between h-full">
                            <div class="flex flex-col justify-between">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                                    Sin actividad <span class="normal-case font-normal">(+7 días)</span>
                                </p>
                                <p class="text-2xl font-bold text-amber-500 tabular-nums">
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
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                                    Sin actividad
                                </p>
                                <p class="text-2xl font-bold text-slate-300 tabular-nums">0</p>
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
            <p class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">Tokens</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Total</p>
                    <p class="text-2xl font-bold text-slate-900 tabular-nums">{{ $kpis['total_tokens'] }}</p>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Completados</p>
                    <p class="text-2xl font-bold text-emerald-600 tabular-nums">{{ $kpis['completadas'] }}</p>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">En progreso</p>
                    <p class="text-2xl font-bold text-blue-600 tabular-nums">{{ $kpis['en_progreso'] }}</p>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Asignados</p>
                    <p class="text-2xl font-bold text-amber-500 tabular-nums">{{ $kpis['asignados'] }}</p>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Disponibles</p>
                    <p class="text-2xl font-bold text-slate-500 tabular-nums">
                        {{ $kpis['disponibles'] }}
                    </p>
                </div>

            </div>
        </div>

        {{-- ── RANKING EMPRESAS (solo super_admin y admin_corporativo) ──────────────────────────── --}}
        @if(in_array(auth()->user()->role, [
            \App\Enums\Role::SUPER_ADMIN->value,
            \App\Enums\Role::ADMIN_CORPORATIVO->value,
        ]))
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Ranking de empresas</p>
                @if($rankingEmpresas->isNotEmpty())
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
                                            <div class="h-1.5 rounded-full" style="width: {{ $empresa['puntaje'] }}%; background-color: {{ \App\Support\ClimaBadge::resolver($empresa['puntaje'])['color_hex'] }};"></div>
                                        </div>
                                        <span class="text-sm font-bold text-slate-900 w-10 text-right tabular-nums">{{ number_format($empresa['puntaje'], 1) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <x-admin.empty-state titulo="Sin datos de ranking" mensaje="No hay empresas con datos de clima laboral para mostrar en el ranking." :conBotonFiltros="false" />
                @endif
            </div>
        @endif

    </div>
