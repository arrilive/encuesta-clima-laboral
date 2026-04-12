<div class="space-y-6">
    {{-- SECCIÓN 1 — Panel de filtros --}}
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <h2 class="text-slate-900 font-semibold">Filtros</h2>

            <div class="flex items-center gap-3">
                <button wire:click="limpiarFiltros" wire:loading.attr="disabled"
                    class="text-blue-600 hover:text-blue-700 text-sm font-semibold flex items-center gap-2
                           transition-all duration-200 hover:-translate-y-px active:translate-y-0
                           disabled:opacity-50 disabled:cursor-not-allowed">

                    <svg wire:loading wire:target="limpiarFiltros" class="animate-spin w-4 h-4" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                        <path d="M12 2a10 10 0 0 1 10 10" />
                    </svg>

                    <svg wire:loading.remove wire:target="limpiarFiltros" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>

                    <span wire:loading.remove wire:target="limpiarFiltros">Limpiar filtros</span>
                    <span wire:loading wire:target="limpiarFiltros">Limpiando...</span>
                </button>

                <div class="hidden md:block w-px h-6 bg-slate-200"></div>

                <button @click="$dispatch('abrir-modal-pdf')"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all duration-200 hover:-translate-y-px active:translate-y-0 whitespace-nowrap">
                    <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    Exportar PDF
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 @if (auth()->user()->role === 'super_admin') lg:grid-cols-4 @endif gap-3">
            {{-- Edad --}}
            <div class="space-y-1.5">
                <label class="text-slate-500 text-sm font-medium">Edad</label>
                <select wire:model.live="filtroEdadId"
                    class="w-full border border-slate-300 rounded-xl text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    <option value="">Todas las edades</option>
                    @foreach ($edades as $edad)
                        <option value="{{ $edad->id }}">{{ $edad->opcion }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Sexo --}}
            <div class="space-y-1.5">
                <label class="text-slate-500 text-sm font-medium">Sexo</label>
                <select wire:model.live="filtroSexoId"
                    class="w-full border border-slate-300 rounded-xl text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    <option value="">Todos los sexos</option>
                    @foreach ($sexos as $sexo)
                        <option value="{{ $sexo->id }}">{{ $sexo->opcion }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Cargo --}}
            <div class="space-y-1.5">
                <label class="text-slate-500 text-sm font-medium">Cargo</label>
                <select wire:model.live="filtroCargoId"
                    class="w-full border border-slate-300 rounded-xl text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    <option value="">Todos los cargos</option>
                    @foreach ($cargos as $cargo)
                        <option value="{{ $cargo->id }}">{{ $cargo->opcion }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Lugar de Trabajo --}}
            <div class="space-y-1.5">
                <label class="text-slate-500 text-sm font-medium">Lugar de Trabajo</label>
                <select wire:model.live="filtroLugarTrabajoId"
                    class="w-full border border-slate-300 rounded-xl text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    <option value="">Todos los lugares</option>
                    @foreach ($lugares as $lugar)
                        <option value="{{ $lugar->id }}">{{ $lugar->opcion }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Grado Académico --}}
            <div class="space-y-1.5">
                <label class="text-slate-500 text-sm font-medium">Grado Académico</label>
                <select wire:model.live="filtroGradoAcademicoId"
                    class="w-full border border-slate-300 rounded-xl text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    <option value="">Todos los grados</option>
                    @foreach ($grados as $grado)
                        <option value="{{ $grado->id }}">{{ $grado->opcion }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Antigüedad --}}
            <div class="space-y-1.5">
                <label class="text-slate-500 text-sm font-medium">Antigüedad</label>
                <select wire:model.live="filtroAntiguedadId"
                    class="w-full border border-slate-300 rounded-xl text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    <option value="">Todas las antigüedades</option>
                    @foreach ($antiguedades as $antiguedad)
                        <option value="{{ $antiguedad->id }}">{{ $antiguedad->opcion }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Empresa (Solo super_admin) --}}
            @if (auth()->user()->role === 'super_admin')
                <div class="space-y-1.5">
                    <label class="text-slate-500 text-sm font-medium">Empresa</label>
                    <select wire:model.live="filtroEmpresaId"
                        class="w-full border border-slate-300 rounded-xl text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                        <option value="">Todas las empresas</option>
                        @foreach ($empresas as $empresa)
                            <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>

    {{-- SECCIÓN 2 — Breadcrumb --}}
    <nav class="flex items-center gap-2 px-1 text-xs">
        @if ($nivel === 1)
            <span class="text-slate-400 font-medium uppercase tracking-wider">Dimensiones</span>
        @else
            <button wire:click="irNivel1"
                class="text-blue-600 hover:text-blue-700 font-medium uppercase tracking-wider transition-colors">Dimensiones</button>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                stroke="currentColor" class="w-3 h-3 text-slate-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>

            @if ($nivel === 2)
                <span class="text-slate-900 text-sm font-semibold">{{ $dimensionActiva->nombre ?? '' }}</span>
            @endif

            @if ($nivel === 3)
                <button wire:click="irNivel2({{ $dimensionActivaId }})"
                    class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    {{ $dimensionActiva->nombre ?? '' }}
                </button>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor" class="w-3 h-3 text-slate-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
                <span class="text-slate-900 text-sm font-semibold">{{ $subdimensionActiva->nombre ?? '' }}</span>
            @endif
        @endif
    </nav>

    {{-- SECCIÓN 3 — Contenido nivel 1 --}}
    @if ($nivel === 1)
        @if ($sinDatos || empty($datosNivel1))
            <x-admin.empty-state mensaje="No hay encuestas completadas que coincidan con los filtros seleccionados." />
        @else
            <div class="space-y-6">
                {{-- 3a. KPIs --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Promedio General --}}
                    <div class="bg-white rounded-2xl shadow-sm p-4">
                        <p class="text-slate-500 text-sm mb-1">Promedio General</p>
                        <div class="flex items-end justify-between">
                            <h3 class="text-2xl font-bold text-slate-900">{{ number_format($promedioGeneral, 1) }}</h3>
                            <x-badge-clima :puntaje="$promedioGeneral" variant="compact" />
                        </div>
                    </div>

                    {{-- Encuestas Completadas --}}
                    <div class="bg-white rounded-2xl shadow-sm p-4">
                        <p class="text-slate-500 text-sm mb-1">Completadas</p>
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold text-slate-900">{{ $completadasFiltradas }}</h3>
                                <p class="text-slate-400 text-[10px]">de {{ $totalTokens }} tokens</p>
                            </div>
                            <div class="p-2 bg-blue-50 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="w-5 h-5 text-blue-600">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Dimensión más alta --}}
                    <div class="bg-white rounded-2xl shadow-sm p-4">
                        <p class="text-slate-500 text-sm mb-1">Más alto</p>
                        @php
                            $maxDim =
                                count($datosNivel1) > 0 ? collect($datosNivel1)->sortByDesc('puntaje')->first() : null;
                        @endphp
                        <h3 class="text-lg font-bold text-slate-900 truncate"
                            title="{{ $maxDim['nombre'] ?? 'N/A' }}">
                            {{ $maxDim['nombre'] ?? 'N/A' }}
                        </h3>
                        <p class="text-emerald-600 text-sm font-bold">{{ number_format($maxDim['puntaje'] ?? 0, 1) }}
                            pts
                        </p>
                    </div>

                    {{-- Dimensión más baja --}}
                    <div class="bg-white rounded-2xl shadow-sm p-4">
                        <p class="text-slate-500 text-sm mb-1">Más bajo</p>
                        @php
                            $minDim =
                                count($datosNivel1) > 0 ? collect($datosNivel1)->sortBy('puntaje')->first() : null;
                        @endphp
                        <h3 class="text-lg font-bold text-slate-900 truncate"
                            title="{{ $minDim['nombre'] ?? 'N/A' }}">
                            {{ $minDim['nombre'] ?? 'N/A' }}
                        </h3>
                        <p class="text-red-500 text-sm font-bold">{{ number_format($minDim['puntaje'] ?? 0, 1) }} pts
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-[55fr_45fr] gap-6 items-start">
                    {{-- 3b. Radar chart --}}
                    <div class="bg-white rounded-2xl shadow-sm p-3">
                        <h2 class="text-slate-900 font-semibold mb-4">Mapa de clima laboral</h2>
                        <div class="flex-1 min-h-[480px]"
                            x-data="{ chart: null }"
                            x-init="
                                const datosActuales = @js($datosNivel1);
                                window.radarDatos = datosActuales;
                                window.radarOptions.series = [{ name: 'Puntaje', data: datosActuales.map(d => d.puntaje) }];
                                window.radarOptions.xaxis = {
                                    categories: datosActuales.map(d => d.nombre),
                                    labels: { style: { colors: datosActuales.map(() => '#64748b'), fontSize: '12px' } }
                                };
                                if (chart) { chart.destroy(); }
                                chart = new ApexCharts($el.querySelector('#radar-chart'), window.radarOptions);
                                window.radarChartInstance = chart;
                                chart.render();"
                            x-on:radar-update.window="
                                window.radarDatos = $event.detail.datos;
                                window.radarOptions.series = [{ name: 'Puntaje', data: $event.detail.datos.map(d => d.puntaje) }];
                                window.radarOptions.xaxis = { categories: $event.detail.datos.map(d => d.nombre), labels: { style: { colors: $event.detail.datos.map(() => '#64748b'), fontSize: '12px' } } };
                                if (chart) { chart.destroy(); }
                                chart = new ApexCharts($el.querySelector('#radar-chart'), window.radarOptions);
                                window.radarChartInstance = chart;
                                chart.render();">
                            <div x-ignore>
                                <div id="radar-chart" style="height: 480px"></div>
                            </div>
                        </div>
                    </div>

                    {{-- 3c. Ranking --}}
                    <div class="bg-white rounded-2xl shadow-sm p-4 flex flex-col">
                        <h2 class="text-slate-900 font-semibold mb-4">Ranking de Dimensiones</h2>
                        <div>
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="text-slate-400 border-b border-slate-100">
                                        <th class="pb-3 font-medium px-2">#</th>
                                        <th class="pb-3 font-medium">Dimensión</th>
                                        <th class="pb-3 font-medium text-center">Puntaje</th>
                                        <th class="pb-3 font-medium">Interpretación</th>
                                        <th class="pb-3 font-medium text-right"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @php
                                        $ranking = collect($datosNivel1)->sortByDesc('puntaje');
                                    @endphp
                                    @foreach ($ranking as $item)
                                        <tr class="group hover:bg-slate-50/50 transition-colors">
                                            <td class="py-3 px-2 text-slate-400">{{ $loop->iteration }}</td>
                                            <td class="py-3 font-medium text-slate-900">{{ $item['nombre'] }}</td>
                                            <td class="py-3 text-center">
                                                <span
                                                    class="font-bold text-slate-700">{{ number_format($item['puntaje'], 1) }}</span>
                                            </td>
                                            <td class="py-3">
                                                <x-badge-clima :puntaje="$item['puntaje']" />
                                            </td>
                                            <td class="py-3 text-right">
                                                <button wire:click="irNivel2({{ $item['id'] }})"
                                                    class="text-blue-600 hover:text-blue-700 inline-flex items-center group-hover:translate-x-1 transition-transform p-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                                                        class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- 3d. Comparativas demográficas --}}
                <livewire:admin.comparativas-demograficas :filtro-edad-id="$filtroEdadId" :filtro-sexo-id="$filtroSexoId" :filtro-cargo-id="$filtroCargoId"
                    :filtro-lugar-trabajo-id="$filtroLugarTrabajoId" :filtro-grado-academico-id="$filtroGradoAcademicoId" :filtro-antiguedad-id="$filtroAntiguedadId" :filtro-empresa-id="$filtroEmpresaId" />

                {{-- 3e. Respuestas abiertas --}}
                <livewire:admin.respuestas-abiertas :filtro-edad-id="$filtroEdadId" :filtro-sexo-id="$filtroSexoId" :filtro-cargo-id="$filtroCargoId"
                    :filtro-lugar-trabajo-id="$filtroLugarTrabajoId" :filtro-grado-academico-id="$filtroGradoAcademicoId" :filtro-antiguedad-id="$filtroAntiguedadId" :filtro-empresa-id="$filtroEmpresaId" />
            </div>
        @endif
    @endif

    {{-- SECCIÓN 4 — Contenido nivel 2 --}}
    @if ($nivel === 2)
        @if ($sinDatos)
            <x-admin.empty-state mensaje="No hay secciones con datos para los filtros seleccionados." />
        @else
            {{-- Grid 55/45: barras + donut --}}
            <div class="grid grid-cols-1 lg:grid-cols-[55fr_45fr] gap-6 items-start">

                {{-- Chart de barras horizontales --}}
                <div class="bg-white rounded-2xl shadow-sm p-4">
                    <h2 class="text-slate-900 font-semibold mb-4">Puntaje por Subdimensión</h2>
                    <div x-data="{ chart: null }" x-init="window.barrasNivel2Datos = @js($datosNivel2);
                    if (chart) { chart.destroy(); }
                    chart = new ApexCharts(
                        $el.querySelector('#barras-nivel2-container'),
                        window.barrasNivel2Options
                    );
                    chart.render();"
                        x-on:barras-nivel2-update.window="
                            if (chart) { chart.destroy(); }
                            window.barrasNivel2Options.series = [{ name: 'Puntaje', data: $event.detail.datos.map(d => d.puntaje) }];
                            window.barrasNivel2Options.xaxis = { ...window.barrasNivel2Options.xaxis, categories: $event.detail.datos.map(d => d.nombre) };
                            chart = new ApexCharts(
                                $el.querySelector('#barras-nivel2-container'),
                                window.barrasNivel2Options
                            );
                            chart.render();
                        ">
                        <div x-ignore>
                            <div id="barras-nivel2-container" style="min-height: 340px;"></div>
                        </div>
                    </div>
                </div>

                {{-- Chart donut distribución --}}
                <div class="bg-white rounded-2xl shadow-sm p-4">
                    <h2 class="text-slate-900 font-semibold mb-4">Distribución de Respuestas</h2>
                    <div x-data="{ chart: null }" x-init="window.donutNivel2Datos = @js($distribucionAgregada);
                    const colorMap = {
                        'Verdadero': '#10b981',
                        'A veces falso/a veces verdadero': '#f59e0b',
                        'Falso': '#ef4444',
                        'Prefiero no responder': '#cbd5e1',
                    };
                    window.donutNivel2Options.series = window.donutNivel2Datos.map(d => d.total);
                    window.donutNivel2Options.labels = window.donutNivel2Datos.map(d => d.opcion);
                    window.donutNivel2Options.colors = window.donutNivel2Datos.map(d => colorMap[d.opcion] ?? '#94a3b8');
                    if (chart) { chart.destroy(); }
                    chart = new ApexCharts(
                        $el.querySelector('#donut-nivel2-container'),
                        window.donutNivel2Options
                    );
                    chart.render();"
                        x-on:donut-nivel2-update.window="
                            const colorMap = {
                                'Verdadero':                        '#10b981',
                                'A veces falso/a veces verdadero':  '#f59e0b',
                                'Falso':                            '#ef4444',
                                'Prefiero no responder':            '#cbd5e1',
                            };
                            if (chart) { chart.destroy(); }
                            window.donutNivel2Options.series  = $event.detail.datos.map(d => d.total);
                            window.donutNivel2Options.labels  = $event.detail.datos.map(d => d.opcion);
                            window.donutNivel2Options.colors  = $event.detail.datos.map(d => colorMap[d.opcion] ?? '#94a3b8');
                            chart = new ApexCharts(
                                $el.querySelector('#donut-nivel2-container'),
                                window.donutNivel2Options
                            );
                            chart.render();
                        ">
                        <div x-ignore>
                            <div id="donut-nivel2-container" style="min-height: 340px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cards de secciones --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($datosNivel2 as $sub)

                    <div wire:click="irNivel3({{ $sub['id'] }})"
                        class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center justify-between gap-4
                               cursor-pointer hover:border-blue-300 hover:shadow-md transition-all duration-200">
                        <div class="flex-1 min-w-0">
                            <p class="text-slate-900 font-semibold truncate">{{ $sub['nombre'] }}</p>
                            <p class="text-slate-400 text-xs mt-0.5">Ver detalles</p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="text-right">
                                <p class="text-xl font-bold text-slate-900">{{ number_format($sub['puntaje'], 1) }}
                                </p>
                                <x-badge-clima :puntaje="$sub['puntaje']" />
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-blue-400 shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- SECCIÓN 5 — Contenido nivel 3 --}}
    @if ($nivel === 3)
        @php
            if (!function_exists('interpretacion')) {
                function interpretacion(float $score): array
                {
                    if ($score >= 80) {
                        return ['label' => 'Excelente', 'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'];
                    }
                    if ($score >= 51) {
                        return ['label' => 'Buen clima', 'bg' => 'bg-blue-100', 'text' => 'text-blue-700'];
                    }
                    if ($score >= 25) {
                        return ['label' => 'Regular', 'bg' => 'bg-amber-100', 'text' => 'text-amber-700'];
                    }
                    return ['label' => 'Deficiente', 'bg' => 'bg-red-100', 'text' => 'text-red-700'];
                }
            }
        @endphp

        @if ($sinDatos)
            <x-admin.empty-state mensaje="No hay respuestas para los filtros seleccionados en esta subdimensión." />
        @elseif (empty($datosNivel3))
            <x-admin.empty-state mensaje="Esta subdimensión no tiene preguntas registradas." :conBotonFiltros="false" />
        @else
            <div class="space-y-3">
                @foreach ($datosNivel3 as $index => $pregunta)
                    @php
                        $interp = interpretacion($pregunta['puntaje']);
                        $colorMap = [
                            1 => '#ef4444', // Falso — red
                            2 => '#f59e0b', // A veces — amber
                            3 => '#10b981', // Verdadero — green
                            0 => '#cbd5e1', // Prefiero no responder — gray
                        ];
                        $scoreColor =
                            $pregunta['puntaje'] >= 80
                                ? '#059669'
                                : ($pregunta['puntaje'] >= 51
                                    ? '#2563eb'
                                    : ($pregunta['puntaje'] >= 25
                                        ? '#d97706'
                                        : '#ef4444'));
                    @endphp

                    <div
                        class="bg-white border border-slate-100 rounded-xl p-5 shadow-sm
                                transition-shadow duration-200 hover:shadow-md">

                        {{-- Fila superior: número + texto + score + badge --}}
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <p class="text-sm font-medium text-slate-800 leading-relaxed">
                                {{ $index + 1 }}. {{ $pregunta['texto'] }}
                            </p>
                            <div class="flex-shrink-0 text-right">
                                <div class="text-2xl font-bold leading-none mb-1" style="color: {{ $scoreColor }}">
                                    {{ number_format($pregunta['puntaje'], 1) }}
                                </div>
                                <span
                                    class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full
                                             {{ $interp['bg'] }} {{ $interp['text'] }}">
                                    {{ $interp['label'] }}
                                </span>
                            </div>
                        </div>

                        {{-- Stacked bar fusionada --}}
                        <div class="flex h-2 rounded-full overflow-hidden mb-3">
                            @foreach ($pregunta['distribucion'] as $segmento)
                                <div style="width: {{ $segmento['porcentaje'] }}%; background: {{ $colorMap[$segmento['valor_numerico']] ?? '#e2e8f0' }}"
                                    title="{{ $segmento['opcion'] }}: {{ $segmento['porcentaje'] }}%"></div>
                            @endforeach
                        </div>

                        {{-- Leyenda: Opción: X% (N personas) --}}
                        <div class="flex flex-wrap gap-x-5 gap-y-1.5">
                            @foreach ($pregunta['distribucion'] as $segmento)
                                <span class="flex items-center gap-1.5 text-xs text-slate-500">
                                    <span class="w-2.5 h-2.5 rounded-sm flex-shrink-0"
                                        style="background: {{ $colorMap[$segmento['valor_numerico']] ?? '#e2e8f0' }}"></span>
                                    {{ $segmento['opcion'] }}:&nbsp;<span class="font-semibold text-slate-700">
                                        {{ $segmento['porcentaje'] }}%
                                    </span>
                                    <span class="text-slate-400">({{ $segmento['total'] }} personas)</span>
                                </span>
                            @endforeach
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Modal Exportar PDF --}}
    <template x-teleport="body">
        <div x-data="{
            abierto: false,
            alcance: 'dimensiones',
            limite: 25,
            exporting: false,
            capturarYExportar() {
                if ({{ $completadasFiltradas }} === 0) {
                    alert('No hay encuestas completadas con los filtros actuales.');
                    return;
                }
                this.exporting = true;
                let svgs = {};
                let radarEl = document.querySelector('#radar-chart svg');
                if (radarEl) svgs.radar = radarEl.outerHTML;
                $wire.prepararExportacion(svgs, this.alcance, this.limite);
                setTimeout(() => { this.exporting = false; }, 2500);
            }
        }" x-on:abrir-modal-pdf.window="abierto = true"
            x-on:keyup.escape.window="abierto = false" x-cloak x-show="abierto"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">

            <div x-show="abierto" x-transition.opacity class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"
                @click="abierto = false"></div>

            <div x-show="abierto" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden ring-1 ring-slate-900/5">

                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">Exportar Reporte a PDF</h3>
                    <button @click="abierto = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-slate-700">Alcance del reporte</label>

                        <label
                            class="flex items-center p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors"
                            :class="{ 'bg-blue-50/50 border-blue-200': alcance === 'dimensiones' }">
                            <input type="radio" x-model="alcance" value="dimensiones"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-600 border-slate-300">
                            <span class="ml-3 block text-sm font-medium text-slate-900">Solo Dimensiones</span>
                        </label>

                        <label
                            class="flex items-center p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors"
                            :class="{ 'bg-blue-50/50 border-blue-200': alcance === 'subdimensiones' }">
                            <input type="radio" x-model="alcance" value="subdimensiones"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-600 border-slate-300">
                            <span class="ml-3 block text-sm font-medium text-slate-900">Dimensiones +
                                Subdimensiones</span>
                        </label>

                        <label
                            class="flex items-center p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors"
                            :class="{ 'bg-blue-50/50 border-blue-200': alcance === 'respuestas_abiertas' }">
                            <input type="radio" x-model="alcance" value="respuestas_abiertas"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-600 border-slate-300">
                            <span class="ml-3 block text-sm font-medium text-slate-900">Solo Preguntas Abiertas</span>
                        </label>

                        <label
                            class="flex items-center p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors"
                            :class="{ 'bg-blue-50/50 border-blue-200': alcance === 'completo' }">
                            <input type="radio" x-model="alcance" value="completo"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-600 border-slate-300">
                            <span class="ml-3 block text-sm font-medium text-slate-900">Reporte Completo</span>
                        </label>
                    </div>

                    <div x-show="['respuestas_abiertas', 'completo'].includes(alcance)" x-collapse>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Máximo de respuestas por
                            pregunta</label>
                        <input type="number" x-model.number="limite" min="10" max="100"
                            class="w-full border border-slate-300 rounded-xl text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                        <p class="mt-1 text-xs text-slate-500">Valor recomendado: 25</p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button @click="abierto = false" type="button"
                        class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button @click="capturarYExportar()" type="button"
                        :disabled="exporting || {{ $completadasFiltradas }} === 0"
                        :class="{
                            'opacity-50 cursor-not-allowed': exporting || {{ $completadasFiltradas }} === 0,
                            'hover:bg-blue-700': !exporting && {{ $completadasFiltradas }} > 0
                        }"
                        class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl transition-colors flex items-center gap-2">
                        <svg x-show="exporting" class="animate-spin w-4 h-4 text-white" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                            <path d="M12 2a10 10 0 0 1 10 10" />
                        </svg>

                        <svg x-show="!exporting" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>

                        <span x-show="!exporting">Descargar PDF</span>
                        <span x-show="exporting">Generando PDF...</span>
                    </button>
                </div>

            </div>
    </template>

    @script
        <script>
            window.radarDatos = @json($datosNivel1);

            window.radarOptions = {
                chart: {
                    type: 'radar',
                    height: 480,
                    fontFamily: 'DM Sans, sans-serif',
                    toolbar: {
                        show: false
                    },
                    events: {
                        click: function(event, chartContext, config) {
                            if (config.dataPointIndex !== -1) {
                                const dimensionId = window.radarDatos[config.dataPointIndex].id;
                                $wire.irNivel2(dimensionId);
                            }
                        }
                    }
                },
                series: [{
                    name: 'Puntaje',
                    data: window.radarDatos.map(d => d.puntaje)
                }],
                xaxis: {
                    categories: window.radarDatos.map(d => d.nombre),
                    labels: {
                        style: {
                            colors: window.radarDatos.map(() => '#64748b'),
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    min: 0,
                    max: 100,
                    tickAmount: 4,
                    labels: {
                        formatter: val => val.toFixed(0)
                    }
                },
                fill: {
                    opacity: 0.2,
                    colors: ['#2563eb']
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['#2563eb']
                },
                markers: {
                    size: 4,
                    colors: ['#2563eb'],
                    strokeColor: '#fff',
                    strokeWidth: 2,
                    hover: {
                        size: 7,
                    }
                },
                colors: ['#2563eb'],
                tooltip: {
                    y: {
                        formatter: val => val.toFixed(1) + ' pts'
                    }
                }
            };

            // Notifica al chart que los datos cambiaron
            $wire.on('radar-datos-actualizados', ({
                datos
            }) => {
                window.radarDatos = datos;
                window.dispatchEvent(new CustomEvent('radar-update', {
                    detail: {
                        datos
                    }
                }));
            });

            // Preparación de exportación de PDF
            $wire.on('pdf-listo', ({
                alcance,
                limite
            }) => {
                const params = new URLSearchParams();
                params.append('alcance', alcance);
                params.append('limite', limite);

                // Agregar filtros activos
                const filtros = {
                    empresa_id: $wire.filtroEmpresaId,
                    sexo_id: $wire.filtroSexoId,
                    edad_id: $wire.filtroEdadId,
                    cargo_id: $wire.filtroCargoId,
                    antiguedad_id: $wire.filtroAntiguedadId,
                    lugar_trabajo_id: $wire.filtroLugarTrabajoId,
                    grado_academico_id: $wire.filtroGradoAcademicoId,
                };

                Object.entries(filtros).forEach(([key, value]) => {
                    if (value) params.append(key, value);
                });

                window.open(`/admin/reportes/pdf?${params.toString()}`, '_blank');
            });

            // ── Nivel 2: Barras horizontales ──────────────────────────────────
            const barrasPaleta = [
                '#2563eb', // blue-600
                '#6d28d9', // violet-700
                '#0891b2', // cyan-600
                '#1d4ed8', // blue-700
                '#7c3aed', // violet-600
                '#06b6d4', // cyan-500
            ];

            window.barrasNivel2Datos = [];

            window.barrasNivel2Options = {
                chart: {
                    type: 'bar',
                    height: 340,
                    fontFamily: 'DM Sans, sans-serif',
                    toolbar: {
                        show: false
                    },
                    events: {
                        click: function(event, chartContext, config) {
                            if (config.dataPointIndex !== -1) {
                                const subdimensionId = window.barrasNivel2Datos[config.dataPointIndex].id;
                                $wire.irNivel3(subdimensionId);
                            }
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 6,
                        distributed: true,
                        dataLabels: {
                            position: 'center'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: val => val.toFixed(1),
                    style: {
                        fontSize: '11px',
                        colors: ['#ffffff'],
                        fontWeight: '600'
                    },
                    dropShadow: {
                        enabled: false
                    }
                },
                legend: {
                    show: false
                },
                series: [{
                    name: 'Puntaje',
                    data: window.barrasNivel2Datos.map(d => d.puntaje)
                }],
                xaxis: {
                    categories: window.barrasNivel2Datos.map(d => d.nombre),
                    min: 0,
                    max: 100,
                    tickAmount: 5,
                    labels: {
                        formatter: val => Math.round(val),
                        style: {
                            colors: '#94a3b8',
                            fontSize: '11px'
                        }
                    }
                },
                yaxis: {
                    min: 0,
                    max: 100,
                    tickAmount: 5,
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontSize: '12px'
                        }
                    }
                },
                colors: barrasPaleta,
                states: {
                    hover: {
                        filter: {
                            type: 'darken',
                            value: 0.05
                        }
                    },
                    active: {
                        filter: {
                            type: 'darken',
                            value: 0.10
                        }
                    }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 4
                },
                tooltip: {
                    y: {
                        formatter: val => val.toFixed(1) + ' pts'
                    }
                }
            };

            $wire.on('barras-nivel2-actualizadas', ({
                datos
            }) => {
                window.barrasNivel2Datos = datos;
                window.barrasNivel2Options.series = [{
                    name: 'Puntaje',
                    data: datos.map(d => d.puntaje)
                }];
                window.barrasNivel2Options.xaxis = {
                    ...window.barrasNivel2Options.xaxis,
                    categories: datos.map(d => d.nombre)
                };
                window.barrasNivel2Options.yaxis = {
                    min: 0,
                    max: 100,
                    tickAmount: 5,
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontSize: '12px'
                        }
                    }
                };
                window.dispatchEvent(new CustomEvent('barras-nivel2-update', {
                    detail: {
                        datos
                    }
                }));
            });

            // ── Nivel 2: Donut distribución ───────────────────────────────────
            window.donutNivel2Datos = [];

            window.donutNivel2Options = {
                chart: {
                    type: 'donut',
                    height: 340,
                    fontFamily: 'DM Sans, sans-serif',
                    toolbar: {
                        show: false
                    }
                },
                series: window.donutNivel2Datos.map(d => d.total),
                labels: window.donutNivel2Datos.map(d => d.opcion),
                colors: [],
                legend: {
                    position: 'bottom',
                    fontSize: '12px',
                    fontFamily: 'DM Sans, sans-serif',
                    labels: {
                        colors: '#64748b'
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: (val) => val.toFixed(1) + '%',
                    style: {
                        fontSize: '12px',
                        fontWeight: '600'
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '62%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Respuestas',
                                    fontSize: '13px',
                                    color: '#64748b',
                                    formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                }
                            }
                        }
                    }
                },
                states: {
                    hover: {
                        filter: {
                            type: 'darken',
                            value: 0.05
                        }
                    },
                    active: {
                        filter: {
                            type: 'darken',
                            value: 0.10
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: val => val + ' respuestas'
                    }
                }
            };

            $wire.on('donut-nivel2-actualizado', ({
                datos
            }) => {
                window.donutNivel2Datos = datos;
                window.donutNivel2Options.series = datos.map(d => d.total);
                window.donutNivel2Options.labels = datos.map(d => d.opcion);
                window.dispatchEvent(new CustomEvent('donut-nivel2-update', {
                    detail: {
                        datos
                    }
                }));
            });

            // ── Nivel 2: CSS (Hover & Leyenda) ─────────────────────────────
            (function() {
                const style = document.createElement('style');
                style.textContent = `
                    #barras-nivel2-container .apexcharts-bar-area {
                        transition: transform 200ms cubic-bezier(0.4,0,0.2,1),
                                    filter 200ms cubic-bezier(0.4,0,0.2,1);
                        transform-box: fill-box;
                        transform-origin: left center;
                    }
                    #barras-nivel2-container .apexcharts-bar-area:hover {
                        transform: scaleX(1.04);
                        filter: brightness(0.93);
                    }
                    #donut-nivel2-container .apexcharts-pie-area {
                        transition: transform 200ms cubic-bezier(0.4,0,0.2,1),
                                    filter 200ms cubic-bezier(0.4,0,0.2,1);
                        transform-box: fill-box;
                        transform-origin: center;
                    }
                    #donut-nivel2-container .apexcharts-pie-area:hover {
                        transform: scale(1.05);
                        filter: brightness(0.93);
                    }
                    #donut-nivel2-container .apexcharts-legend {
                        display: flex;
                        flex-wrap: wrap;
                    }
                    #donut-nivel2-container .apexcharts-legend-series[rel="3"] { order: 1; }
                    #donut-nivel2-container .apexcharts-legend-series[rel="2"] { order: 2; }
                    #donut-nivel2-container .apexcharts-legend-series[rel="1"] { order: 3; }
                    #donut-nivel2-container .apexcharts-legend-series[rel="4"] { order: 4; }

                    /* Feedback estable para gráficos */
                    #radar-chart .apexcharts-radar-series path,
                    #radar-chart .apexcharts-marker {
                        cursor: pointer;
                    }

                    #barras-nivel2-container .apexcharts-bar-area {
                        cursor: pointer;
                    }
                `;
                document.head.appendChild(style);
            })();
        </script>
    @endscript
</div>
