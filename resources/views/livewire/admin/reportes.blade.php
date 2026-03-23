<div class="space-y-6">
    {{-- SECCIÓN 1 — Panel de filtros --}}
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <h2 class="text-slate-900 font-semibold">Filtros</h2>
            <button wire:click="limpiarFiltros" wire:loading.attr="disabled"
                class="text-blue-600 hover:text-blue-700 text-sm font-semibold flex items-center gap-2
                       transition-all duration-200 hover:-translate-y-px active:translate-y-0
                       disabled:opacity-50 disabled:cursor-not-allowed">

                <svg wire:loading wire:target="limpiarFiltros" class="animate-spin w-4 h-4" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                    <path d="M12 2a10 10 0 0 1 10 10" />
                </svg>

                <svg wire:loading.remove wire:target="limpiarFiltros" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>

                <span wire:loading.remove wire:target="limpiarFiltros">Limpiar filtros</span>
                <span wire:loading wire:target="limpiarFiltros">Limpiando...</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 @if(auth()->user()->role === 'super_admin') lg:grid-cols-4 @endif gap-3">
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
            <span class="text-slate-400 font-medium uppercase tracking-wider">Bloques</span>
        @else
            <button wire:click="irNivel1"
                class="text-blue-600 hover:text-blue-700 font-medium uppercase tracking-wider transition-colors">Bloques</button>
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
        @php
            $completadasFiltradas = \App\Models\Encuesta::where('estado', 'completado')
                ->when(
                    auth()->user()->role === 'admin_empresa',
                    fn($q) => $q->where('empresa_id', auth()->user()->empresa_id),
                )
                ->when(
                    $filtroEdadId,
                    fn($q) => $q->whereHas('datoDemografico', fn($q2) => $q2->where('edad_id', $filtroEdadId)),
                )
                ->when(
                    $filtroSexoId,
                    fn($q) => $q->whereHas('datoDemografico', fn($q2) => $q2->where('sexo_id', $filtroSexoId)),
                )
                ->when(
                    $filtroCargoId,
                    fn($q) => $q->whereHas('datoDemografico', fn($q2) => $q2->where('cargo_id', $filtroCargoId)),
                )
                ->when(
                    $filtroLugarTrabajoId,
                    fn($q) => $q->whereHas(
                        'datoDemografico',
                        fn($q2) => $q2->where('lugar_trabajo_id', $filtroLugarTrabajoId),
                    ),
                )
                ->when(
                    $filtroGradoAcademicoId,
                    fn($q) => $q->whereHas(
                        'datoDemografico',
                        fn($q2) => $q2->where('grado_academico_id', $filtroGradoAcademicoId),
                    ),
                )
                ->when(
                    $filtroAntiguedadId,
                    fn($q) => $q->whereHas(
                        'datoDemografico',
                        fn($q2) => $q2->where('antiguedad_id', $filtroAntiguedadId),
                    ),
                )
                ->count();

            $completadasTotal = \App\Models\Encuesta::where('estado', 'completado')
                ->when(
                    auth()->user()->role === 'admin_empresa',
                    fn($q) => $q->where('empresa_id', auth()->user()->empresa_id),
                )
                ->count();

            $sinDatos = $completadasFiltradas === 0;
        @endphp

        <div class="space-y-6">
            {{-- 3a. KPIs --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Promedio General --}}
                <div class="bg-white rounded-2xl shadow-sm p-4">
                    <p class="text-slate-500 text-sm mb-1">Promedio General</p>
                    <div class="flex items-end justify-between">
                        @php
                            $promedioGral =
                                count($datosNivel1) > 0
                                    ? array_sum(array_column($datosNivel1, 'puntaje')) / count($datosNivel1)
                                    : 0;
                            $colorBadge =
                                $promedioGral >= 2.5
                                    ? 'bg-emerald-50 text-emerald-600'
                                    : ($promedioGral >= 2.0
                                        ? 'bg-blue-50 text-blue-600'
                                        : ($promedioGral >= 1.5
                                            ? 'bg-amber-50 text-amber-600'
                                            : 'bg-red-50 text-red-600'));
                        @endphp
                        <h3 class="text-2xl font-bold text-slate-900">{{ number_format($promedioGral, 2) }}</h3>
                        <span
                            class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $colorBadge }}">
                            {{ $promedioGral >= 2.5 ? 'Excelente' : ($promedioGral >= 2.0 ? 'Bueno' : ($promedioGral >= 1.5 ? 'Regular' : 'Crítico')) }}
                        </span>
                    </div>
                </div>

                {{-- Encuestas Completadas --}}
                <div class="bg-white rounded-2xl shadow-sm p-4">
                    <p class="text-slate-500 text-sm mb-1">Completadas</p>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-slate-900">{{ $completadasFiltradas }}</h3>
                            <p class="text-slate-400 text-[10px]">de {{ $completadasTotal }} totales</p>
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
                    <h3 class="text-lg font-bold text-slate-900 truncate" title="{{ $maxDim['nombre'] ?? 'N/A' }}">
                        {{ $maxDim['nombre'] ?? 'N/A' }}
                    </h3>
                    <p class="text-emerald-600 text-sm font-bold">{{ number_format($maxDim['puntaje'] ?? 0, 2) }} pts
                    </p>
                </div>

                {{-- Dimensión más baja --}}
                <div class="bg-white rounded-2xl shadow-sm p-4">
                    <p class="text-slate-500 text-sm mb-1">Más bajo</p>
                    @php
                        $minDim = count($datosNivel1) > 0 ? collect($datosNivel1)->sortBy('puntaje')->first() : null;
                    @endphp
                    <h3 class="text-lg font-bold text-slate-900 truncate" title="{{ $minDim['nombre'] ?? 'N/A' }}">
                        {{ $minDim['nombre'] ?? 'N/A' }}
                    </h3>
                    <p class="text-red-500 text-sm font-bold">{{ number_format($minDim['puntaje'] ?? 0, 2) }} pts</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[55fr_45fr] gap-6 items-start">
                {{-- 3b. Radar chart --}}
                <div class="bg-white rounded-2xl shadow-sm p-3">
                    <h2 class="text-slate-900 font-semibold mb-4">Mapa de clima laboral</h2>
                    <div class="flex-1 min-h-[480px]" x-data="{ chart: null }" x-init="if (chart) { chart.destroy(); }
                    chart = new ApexCharts($el.querySelector('#radar-chart'), JSON.parse(JSON.stringify(window.radarOptions)));
                    chart.render();"
                        x-on:radar-update.window="
                                if (chart) { chart.destroy(); }
                                window.radarOptions.series = [{ name: 'Puntaje', data: $event.detail.datos.map(d => d.puntaje) }];
                                window.radarOptions.xaxis = { categories: $event.detail.datos.map(d => d.nombre), labels: { style: { colors: $event.detail.datos.map(() => '#64748b'), fontSize: '12px' } } };
                                chart = new ApexCharts($el.querySelector('#radar-chart'), JSON.parse(JSON.stringify(window.radarOptions)));
                                chart.render();
                            ">
                        <div x-ignore>
                            <div id="radar-chart" style="height: 480px"></div>
                        </div>
                    </div>
                </div>

                {{-- 3c. Ranking --}}
                <div class="bg-white rounded-2xl shadow-sm p-4 flex flex-col">
                    <h2 class="text-slate-900 font-semibold mb-4">Ranking de Bloques</h2>
                    <div>
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="text-slate-400 border-b border-slate-100">
                                    <th class="pb-3 font-medium px-2">#</th>
                                    <th class="pb-3 font-medium">Bloque</th>
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
                                                class="font-bold text-slate-700">{{ number_format($item['puntaje'], 2) }}</span>
                                        </td>
                                        <td class="py-3">
                                            @php
                                                $badge =
                                                    $item['puntaje'] >= 2.5
                                                        ? 'bg-emerald-50 text-emerald-600'
                                                        : ($item['puntaje'] >= 2.0
                                                            ? 'bg-blue-50 text-blue-600'
                                                            : ($item['puntaje'] >= 1.5
                                                                ? 'bg-amber-50 text-amber-600'
                                                                : 'bg-red-50 text-red-600'));
                                                $label =
                                                    $item['puntaje'] >= 2.5
                                                        ? 'Excelente'
                                                        : ($item['puntaje'] >= 2.0
                                                            ? 'Buen clima'
                                                            : ($item['puntaje'] >= 1.5
                                                                ? 'Regular'
                                                                : 'Deficiente'));
                                            @endphp
                                            <span
                                                class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $badge }}">
                                                {{ $label }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-right">
                                            <button wire:click="irNivel2({{ $item['id'] }})"
                                                class="text-blue-600 hover:text-blue-700 inline-flex items-center group-hover:translate-x-1 transition-transform p-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
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
        </div>
    @endif

    {{-- Nivel 2: issue #47 --}}
    {{-- Nivel 3: issue #48 --}}

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
                        dataPointSelection: function(event, chartContext, config) {
                            const idx = config.dataPointIndex;
                            const dimensionId = window.radarDatos[idx].id;
                            $wire.irNivel2(dimensionId);
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
                    min: 1,
                    max: 3,
                    tickAmount: 4,
                    labels: {
                        formatter: val => val.toFixed(1)
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
                    strokeWidth: 2
                },
                colors: ['#2563eb'],
                tooltip: {
                    y: {
                        formatter: val => val.toFixed(2) + ' pts'
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
        </script>
    @endscript
</div>
