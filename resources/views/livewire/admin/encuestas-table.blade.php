<div class="space-y-6 max-w-7xl">

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        @if(auth()->user()->role === 'super_admin')
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @endif

            {{-- Buscar por token --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Buscar token</label>
                <input
                    wire:model.live.debounce.400ms="buscar"
                    type="text"
                    placeholder="Escribe parte del token..."
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-900
                           placeholder-slate-400 bg-white focus:outline-none focus:border-blue-500
                           focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
                />
            </div>

            {{-- Filtro estado --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Estado</label>
                <select
                    wire:model.live="filtroEstado"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm
                           text-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10
                           focus:outline-none transition-all duration-200">
                    <option value="">Todos</option>
                    <option value="disponible">Disponible</option>
                    <option value="asignado">Asignado</option>
                    <option value="en_progreso">En progreso</option>
                    <option value="completado">Completado</option>
                </select>
            </div>

            {{-- Filtro empresa (solo super_admin) --}}
            @if(auth()->user()->role === 'super_admin')
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Empresa</label>
                <select
                    wire:model.live="filtroEmpresa"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm
                           text-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10
                           focus:outline-none transition-all duration-200">
                    <option value="">Todas</option>
                    @foreach(\App\Models\Empresa::orderBy('nombre')->get() as $empresa)
                        <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Fecha desde --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Asignado desde</label>
                <input
                    wire:model.live="filtroDesde"
                    type="date"
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-900
                           bg-white focus:outline-none focus:border-blue-500 focus:ring-4
                           focus:ring-blue-500/10 transition-all duration-200"
                />
            </div>

            {{-- Fecha hasta --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Asignado hasta</label>
                <input
                    wire:model.live="filtroHasta"
                    type="date"
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-900
                           bg-white focus:outline-none focus:border-blue-500 focus:ring-4
                           focus:ring-blue-500/10 transition-all duration-200"
                />
            </div>

        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Token</th>
                        @if(auth()->user()->role === 'super_admin')
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Empresa</th>
                        @endif
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Asignado</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Completado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($encuestas as $encuesta)
                        <tr class="hover:bg-slate-50 transition-colors duration-100">
                            <td class="px-6 py-4 font-mono text-xs text-slate-600">
                                {{ substr($encuesta->token, 0, 16) }}…
                            </td>
                            @if(auth()->user()->role === 'super_admin')
                                <td class="px-6 py-4 text-slate-700">
                                    {{ $encuesta->empresa->nombre }}
                                </td>
                            @endif
                            <td class="px-6 py-4">
                                @php
                                    $badge = match($encuesta->estado) {
                                        'disponible'  => 'bg-slate-100 text-slate-600',
                                        'asignado'    => 'bg-amber-50 text-amber-700',
                                        'en_progreso' => 'bg-blue-50 text-blue-700',
                                        'completado'  => 'bg-emerald-50 text-emerald-700',
                                        default       => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                    {{ ucfirst(str_replace('_', ' ', $encuesta->estado)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">
                                {{ $encuesta->fecha_asignacion?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">
                                {{ $encuesta->fecha_completada?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
                                No se encontraron encuestas con esos filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $encuestas->links() }}
        </div>
    </div>

</div>