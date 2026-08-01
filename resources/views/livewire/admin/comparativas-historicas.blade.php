<div class="space-y-6"
     x-data="{ modo: @entangle('modo') }">

    {{-- Tabs de Selección de Modo --}}
    {{-- Mismo patrón que GenerarTokens: border-b-2 con border-blue-600 activo --}}
    <div class="flex border-b border-slate-200">
        <button
            type="button"
            @click="modo = 'comparar'"
            :class="modo === 'comparar' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
            class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm mr-8 transition-colors duration-150 focus:outline-none flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            Comparar periodos
        </button>
        <button
            type="button"
            @click="modo = 'historial'"
            :class="modo === 'historial' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
            class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm transition-colors duration-150 focus:outline-none flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
            </svg>
            Historial completo
        </button>
    </div>

    {{-- Filtros superiores de acotación del dropdown (por rol) con Comboboxes --}}
    @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin_corporativo' || count($empresas) > 0 || count($sucursales) > 0)
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-wrap items-center gap-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mr-2 flex items-center gap-1.5 shrink-0">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Acotar selectores:
            </div>

            @if(auth()->user()->role === 'super_admin' && count($corporativos) > 0)
                <div class="w-full sm:w-auto min-w-[200px] max-w-xs">
                    <x-admin.combobox-entidad
                        wire-model="filtroCorporativoId"
                        placeholder="Todos los corporativos"
                        :disabled="false">
                        <option value="">Todos los corporativos</option>
                        @foreach($corporativos as $corp)
                            <option value="{{ $corp->id }}">{{ $corp->nombre }}</option>
                        @endforeach
                    </x-admin.combobox-entidad>
                </div>
            @endif

            @if(in_array(auth()->user()->role, ['super_admin', 'admin_corporativo']) && count($empresas) > 0)
                <div class="w-full sm:w-auto min-w-[200px] max-w-xs">
                    <x-admin.combobox-entidad
                        wire-model="filtroEmpresaId"
                        placeholder="Todas las empresas"
                        :disabled="false">
                        <option value="">Todas las empresas</option>
                        @foreach($empresas as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                        @endforeach
                    </x-admin.combobox-entidad>
                </div>
            @endif

            @if(count($sucursales) > 0)
                <div class="w-full sm:w-auto min-w-[200px] max-w-xs">
                    <x-admin.combobox-entidad
                        wire-model="filtroSucursalId"
                        placeholder="Todas las sucursales"
                        :disabled="false">
                        <option value="">Todas las sucursales</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                        @endforeach
                    </x-admin.combobox-entidad>
                </div>
            @endif
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- TAB: Comparar periodos — x-show para cambio instantáneo   --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div
        x-show="modo === 'comparar'"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="space-y-6">

        {{-- Selectores de Periodo Base y Periodo Comparado con Comboboxes --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Selector Periodo Base --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-blue-500 space-y-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-blue-700">
                    Periodo Base (Origen)
                </label>
                <x-admin.combobox-entidad
                    wire-model="loteIdA"
                    placeholder="-- Seleccionar Periodo Base --"
                    :disabled="false">
                    <option value="">-- Seleccionar Periodo Base --</option>
                    @foreach($lotes as $l)
                        <option value="{{ $l->id }}">{{ $this->formatLoteLabel($l) }}</option>
                    @endforeach
                </x-admin.combobox-entidad>
            </div>

            {{-- Selector Periodo Comparativo --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-purple-500 space-y-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-purple-700">
                    Periodo Comparado (Destino)
                </label>
                <x-admin.combobox-entidad
                    wire-model="loteIdB"
                    placeholder="-- Seleccionar Periodo Comparado --"
                    :disabled="false">
                    <option value="">-- Seleccionar Periodo Comparado --</option>
                    @foreach($lotes as $l)
                        <option value="{{ $l->id }}">{{ $this->formatLoteLabel($l) }}</option>
                    @endforeach
                </x-admin.combobox-entidad>
            </div>
        </div>

        {{-- Estado vacío si no se han seleccionado ambos periodos --}}
        @if(!$loteA || !$loteB)
            <div class="bg-white rounded-2xl p-8 text-center shadow-sm border border-slate-100 flex flex-col items-center justify-center my-6">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-800">Selecciona dos periodos de encuesta</h3>
                <p class="text-slate-500 text-xs mt-1 max-w-md">
                    Elige el <strong>Periodo Base</strong> y el <strong>Periodo Comparado</strong> en los selectores superiores para visualizar las métricas y la tendencia del clima laboral.
                </p>
            </div>
        @else
            {{-- Tarjeta de Comparación del Promedio General --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                    {{-- Columna Periodo Base --}}
                    <div class="flex-1 text-center sm:text-left">
                        <span class="text-xs font-semibold uppercase tracking-wider text-blue-600 block mb-1">
                            {{ $this->formatLoteNombreSimple($loteA) }}
                        </span>
                        <div class="text-3xl font-bold text-black tracking-tight">
                            @if($promedioGeneralA !== null)
                                {{ number_format($promedioGeneralA, 1) }} <span class="text-sm font-normal text-slate-400">pts</span>
                            @else
                                <span class="text-slate-400 text-lg font-medium">N/A</span>
                            @endif
                        </div>
                    </div>

                    {{-- Badge de Cambio (Delta) --}}
                    <div class="flex flex-col items-center justify-center px-5 py-2.5 bg-slate-50 rounded-2xl border border-slate-100 min-w-[150px]">
                        <span class="text-[11px] font-semibold text-black uppercase tracking-wider mb-1">Variación Global</span>
                        <span class="{{ $badgeGeneral['class'] }} text-sm px-3 py-1">
                            {{ $badgeGeneral['formatted'] }}
                        </span>
                    </div>

                    {{-- Columna Periodo Comparado --}}
                    <div class="flex-1 text-center sm:text-right">
                        <span class="text-xs font-semibold uppercase tracking-wider text-purple-600 block mb-1">
                            {{ $this->formatLoteNombreSimple($loteB) }}
                        </span>
                        <div class="text-3xl font-bold text-black tracking-tight">
                            @if($promedioGeneralB !== null)
                                {{ number_format($promedioGeneralB, 1) }} <span class="text-sm font-normal text-slate-400">pts</span>
                            @else
                                <span class="text-slate-400 text-lg font-medium">N/A</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Nivel 1: Vista General por Dimensiones --}}
            @if($nivel === 1)
                <div class="space-y-6">
                    {{-- Gráfica de Barras Agrupadas Nivel 1 --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                        <div class="mb-4">
                            <h2 class="text-base font-semibold text-slate-800 tracking-tight">Comparativa por Dimensiones</h2>
                            <p class="text-slate-500 text-xs mt-0.5">Puntaje acumulado (0–100) en cada una de las 6 dimensiones</p>
                        </div>

                        <div x-data="{ chart: null }"
                             x-init="
                                 let initialData = @js($chartNivel1);
                                 if (chart) { chart.destroy(); }
                                 let opts = { ...window.tendenciasNivel1Options };
                                 opts.series = initialData.series;
                                 opts.xaxis = { ...opts.xaxis, categories: initialData.categorias };
                                 chart = new ApexCharts($el.querySelector('#tendencias-nivel1-chart'), opts);
                                 chart.render();
                             "
                             x-effect="
                                 let data = @js($chartNivel1);
                                 if (chart) {
                                     chart.destroy();
                                     let opts = { ...window.tendenciasNivel1Options };
                                     opts.series = data.series;
                                     opts.xaxis = { ...opts.xaxis, categories: data.categorias };
                                     chart = new ApexCharts($el.querySelector('#tendencias-nivel1-chart'), opts);
                                     chart.render();
                                 }
                             ">
                            <div x-ignore>
                                <div id="tendencias-nivel1-chart" class="w-full min-h-[360px]"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Desglose de Dimensiones en Tabla/Tarjetas clickeables --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-800">Desglose por Dimensión</h3>
                            <span class="text-xs text-slate-400">Haz clic en una dimensión para más detalles</span>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach($datosDimensiones as $item)
                                <div wire:click="irNivel2({{ $item['id'] }})"
                                     class="p-4 sm:px-6 hover:bg-blue-50/40 transition-colors cursor-pointer flex items-center justify-between gap-4 group">
                                    
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm font-medium text-slate-800 group-hover:text-blue-600 transition-colors block truncate">
                                            {{ $item['nombre'] }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-6">
                                        <div class="text-right">
                                            <span class="text-[10px] uppercase font-semibold text-slate-400 block truncate max-w-[140px]" title="{{ $this->formatLoteNombreSimple($loteA) }}">
                                                {{ $this->formatLoteNombreSimple($loteA) }}
                                            </span>
                                            <span class="text-sm font-semibold text-slate-700">
                                                {{ $item['puntajeA'] !== null ? number_format($item['puntajeA'], 1) : 'N/A' }}
                                            </span>
                                        </div>

                                        <div class="text-right">
                                            <span class="text-[10px] uppercase font-semibold text-slate-400 block truncate max-w-[140px]" title="{{ $this->formatLoteNombreSimple($loteB) }}">
                                                {{ $this->formatLoteNombreSimple($loteB) }}
                                            </span>
                                            <span class="text-sm font-semibold text-slate-700">
                                                {{ $item['puntajeB'] !== null ? number_format($item['puntajeB'], 1) : 'N/A' }}
                                            </span>
                                        </div>

                                        <div class="min-w-[95px] text-right">
                                            <span class="{{ $item['badge']['class'] }}">
                                                {{ $item['badge']['formatted'] }}
                                            </span>
                                        </div>

                                        <div class="text-slate-300 group-hover:text-blue-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Nivel 2: Vista Detallada por Subdimensiones --}}
            @if($nivel === 2 && $dimensionActiva)
                <div class="space-y-6">
                    {{-- Breadcrumb y Navegación de regreso --}}
                    <div class="flex items-center gap-3">
                        <button wire:click="irNivel1"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Volver a Dimensiones
                        </button>
                        <span class="text-slate-300">/</span>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            {{ $dimensionActiva->nombre }}
                        </span>
                    </div>

                    {{-- Gráfica de Barras Horizontales Agrupadas Nivel 2 --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                        <div class="mb-4">
                            <h2 class="text-base font-semibold text-slate-800 tracking-tight">Subdimensiones: {{ $dimensionActiva->nombre }}</h2>
                            <p class="text-slate-500 text-xs mt-0.5">Comparación puntual de cada subdimensión en el alcance seleccionado</p>
                        </div>

                        <div x-data="{ chart: null }"
                             x-init="
                                 let initialData = @js($chartNivel2);
                                 if (chart) { chart.destroy(); }
                                 let opts = { ...window.tendenciasNivel2Options };
                                 opts.series = initialData.series;
                                 opts.xaxis = { ...opts.xaxis, categories: initialData.categorias };
                                 chart = new ApexCharts($el.querySelector('#tendencias-nivel2-chart'), opts);
                                 chart.render();
                             "
                             x-effect="
                                 let data = @js($chartNivel2);
                                 if (chart) {
                                     chart.destroy();
                                     let opts = { ...window.tendenciasNivel2Options };
                                     opts.series = data.series;
                                     opts.xaxis = { ...opts.xaxis, categories: data.categorias };
                                     chart = new ApexCharts($el.querySelector('#tendencias-nivel2-chart'), opts);
                                     chart.render();
                                 }
                             ">
                            <div x-ignore>
                                <div id="tendencias-nivel2-chart" class="w-full min-h-[360px]"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Desglose de Subdimensiones en Lista --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100">
                            <h3 class="text-sm font-semibold text-slate-800">Detalle de Subdimensiones</h3>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach($datosSubdimensiones as $sub)
                                <div class="p-4 sm:px-6 flex items-center justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm font-medium text-slate-800 block truncate">
                                            {{ $sub['nombre'] }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-6">
                                        <div class="text-right">
                                            <span class="text-[10px] uppercase font-semibold text-slate-400 block truncate max-w-[140px]" title="{{ $this->formatLoteNombreSimple($loteA) }}">
                                                {{ $this->formatLoteNombreSimple($loteA) }}
                                            </span>
                                            <span class="text-sm font-semibold text-slate-700">
                                                {{ $sub['puntajeA'] !== null ? number_format($sub['puntajeA'], 1) : 'N/A' }}
                                            </span>
                                        </div>

                                        <div class="text-right">
                                            <span class="text-[10px] uppercase font-semibold text-slate-400 block truncate max-w-[140px]" title="{{ $this->formatLoteNombreSimple($loteB) }}">
                                                {{ $this->formatLoteNombreSimple($loteB) }}
                                            </span>
                                            <span class="text-sm font-semibold text-slate-700">
                                                {{ $sub['puntajeB'] !== null ? number_format($sub['puntajeB'], 1) : 'N/A' }}
                                            </span>
                                        </div>

                                        <div class="min-w-[95px] text-right">
                                            <span class="{{ $sub['badge']['class'] }}">
                                                {{ $sub['badge']['formatted'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endif

    </div>{{-- fin x-show comparar --}}

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- TAB: Historial completo — x-show para cambio instantáneo  --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div
        x-show="modo === 'historial'"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="space-y-6">

        @if($lotesHistorial->isEmpty())
            {{-- Estado vacío: no hay lotes cerrados en el alcance actual --}}
            <div class="bg-white rounded-2xl p-8 text-center shadow-sm border border-slate-100 flex flex-col items-center justify-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-800">Sin historial disponible</h3>
                <p class="text-slate-500 text-xs mt-1 max-w-sm">
                    No existen periodos cerrados en el alcance actual. El historial solo incluye lotes cuya fecha de cierre ya pasó.
                </p>
            </div>
        @else
            {{-- Gráfica de evolución histórica (Promedio General) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-slate-800 tracking-tight">Evolución histórica del clima laboral</h2>
                    <p class="text-slate-500 text-xs mt-0.5">Solo periodos cerrados, ordenados cronológicamente por fecha de inicio</p>
                </div>

                <div x-data="{ chart: null }"
                     x-init="
                         let initialData = @js($chartTendenciaHistorial);
                         if (chart) { chart.destroy(); }
                         let opts = { ...window.historialEvolucionOptions };
                         opts.series = initialData.series;
                         opts.xaxis = { ...opts.xaxis, categories: initialData.categorias };
                         chart = new ApexCharts($el.querySelector('#historial-evolucion-chart'), opts);
                         chart.render();
                     "
                     x-effect="
                         let data = @js($chartTendenciaHistorial);
                         if (chart) {
                             chart.destroy();
                             let opts = { ...window.historialEvolucionOptions };
                             opts.series = data.series;
                             opts.xaxis = { ...opts.xaxis, categories: data.categorias };
                             chart = new ApexCharts($el.querySelector('#historial-evolucion-chart'), opts);
                             chart.render();
                         }
                     ">
                    <div x-ignore>
                        <div id="historial-evolucion-chart" class="w-full min-h-[360px]"></div>
                    </div>
                </div>
            </div>

            {{-- Tabla de lotes históricos --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-800">Detalle por periodo</h3>
                    <span class="text-xs text-slate-400">Solo periodos cerrados</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">Periodo</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">Entidad</th>
                                <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-slate-400">Inicio</th>
                                <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-slate-400">Cierre</th>
                                <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-slate-400">Promedio</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($timeline as $punto)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-3.5 font-medium text-slate-800">
                                        {{ $punto['label'] }}
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-500 text-xs">
                                        {{ $punto['lote']->sucursal?->nombre ?? $punto['lote']->empresa?->nombre ?? 'General' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center text-slate-500 text-xs tabular-nums">
                                        {{ $punto['lote']->fecha_inicio?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center text-slate-500 text-xs tabular-nums">
                                        {{ $punto['lote']->fecha_fin?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if($punto['promedio_general'] !== null)
                                            <span class="inline-flex items-center gap-2 justify-center">
                                                <span class="font-semibold text-slate-800 tabular-nums">
                                                    {{ number_format($punto['promedio_general'], 1) }}
                                                    <span class="text-[11px] font-normal text-slate-400">pts</span>
                                                </span>
                                                <x-badge-clima :puntaje="$punto['promedio_general']" variant="compact" />
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-xs">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @endif {{-- $lotesHistorial->isEmpty() --}}

    </div>{{-- fin x-show historial --}}

    {{-- Script Global de ApexCharts Options --}}
    @script
        <script>
            window.tendenciasNivel1Options = {
                chart: {
                    type: 'bar',
                    height: 380,
                    fontFamily: 'DM Sans, sans-serif',
                    toolbar: { show: false }
                },
                colors: ['#3b82f6', '#8b5cf6'],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '50%',
                        borderRadius: 4
                    }
                },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 2, colors: ['transparent'] },
                series: [],
                xaxis: {
                    categories: [],
                    labels: { style: { colors: '#64748b', fontSize: '12px' } }
                },
                yaxis: {
                    min: 0,
                    max: 100,
                    tickAmount: 5,
                    labels: {
                        style: { colors: '#64748b', fontSize: '12px' },
                        formatter: val => Math.round(val)
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontSize: '13px',
                    markers: { radius: 12 }
                },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                tooltip: {
                    y: { formatter: val => val.toFixed(1) + ' pts' }
                }
            };

            window.tendenciasNivel2Options = {
                chart: {
                    type: 'bar',
                    height: 380,
                    fontFamily: 'DM Sans, sans-serif',
                    toolbar: { show: false }
                },
                colors: ['#3b82f6', '#8b5cf6'],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '55%',
                        borderRadius: 4
                    }
                },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 2, colors: ['transparent'] },
                series: [],
                xaxis: {
                    categories: [],
                    min: 0,
                    max: 100,
                    labels: { style: { colors: '#64748b', fontSize: '12px' } }
                },
                yaxis: {
                    labels: { style: { colors: '#64748b', fontSize: '12px' } }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontSize: '13px',
                    markers: { radius: 12 }
                },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                tooltip: {
                    y: { formatter: val => val.toFixed(1) + ' pts' }
                }
            };

            window.historialEvolucionOptions = {
                chart: {
                    type: 'area',
                    height: 400,
                    fontFamily: 'DM Sans, sans-serif',
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    animations: { enabled: true, speed: 400 }
                },
                colors: ['#3b82f6'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.35,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                stroke: { curve: 'smooth', width: 2.5 },
                markers: {
                    size: 5,
                    strokeColors: '#fff',
                    strokeWidth: 2,
                    hover: { size: 7 }
                },
                dataLabels: { enabled: false },
                series: [],
                xaxis: {
                    categories: [],
                    labels: {
                        style: { colors: '#64748b', fontSize: '11px' },
                        rotate: -30,
                        rotateAlways: false,
                        hideOverlappingLabels: true
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    min: 0,
                    max: 100,
                    tickAmount: 5,
                    labels: {
                        style: { colors: '#64748b', fontSize: '12px' },
                        formatter: val => val !== null ? Math.round(val) : ''
                    }
                },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                tooltip: {
                    y: {
                        formatter: val => val !== null ? val.toFixed(1) + ' pts' : 'Sin datos'
                    }
                },
                legend: { show: false },
                noData: {
                    text: 'Sin datos para este periodo',
                    style: { color: '#94a3b8', fontSize: '13px' }
                }
            };
        </script>
    @endscript

</div>
