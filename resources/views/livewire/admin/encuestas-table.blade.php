<div class="space-y-6 max-w-7xl">

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5">

        {{-- Barra superior: acción --}}
        <div class="flex items-center justify-between mb-4">
            <span class="text-slate-900 font-semibold">Filtros</span>
            <a href="{{ route('admin.encuestas.exportar', array_filter([
                'estado'      => $filtroEstado,
                'buscar'      => $buscar,
                'corporativo' => $filtroCorporativo,
                'empresa'     => $filtroEmpresa,
                'sucursal'    => $filtroSucursal,
                'lote'        => $filtroLote,
                'desde'       => $filtroDesde,
                'hasta'       => $filtroHasta,
            ])) }}"
               x-data="{ exporting: false }"
               x-on:click="exporting = true; setTimeout(() => exporting = false, 2500)"
               x-bind:class="{ 'opacity-50 cursor-not-allowed': exporting }"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700
                      bg-white border border-slate-300 rounded-lg hover:bg-slate-50
                      transition-all duration-200 hover:-translate-y-px active:translate-y-0 whitespace-nowrap">
                      
                <svg x-show="exporting" style="display: none;" class="animate-spin w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                    <path d="M12 2a10 10 0 0 1 10 10"/>
                </svg>

                <svg x-show="!exporting" class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>

                <span x-show="!exporting">Exportar CSV</span>
                <span x-show="exporting" style="display: none;">Exportando...</span>
            </a>
        </div>

        {{-- Grid de filtros --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

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

            {{-- Corporativo (Solo super_admin) --}}
            @if(auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value)
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Corporativo</label>
                <x-admin.combobox-entidad
                    wire-model="filtroCorporativo"
                    placeholder="Buscar corporativo..."
                    :has-error="$errors->has('filtroCorporativo')"
                    :disabled="false">
                    <option value="">Todos</option>
                    @foreach($corporativos as $corp)
                        <option value="{{ $corp->id }}">{{ $corp->nombre }}</option>
                    @endforeach
                </x-admin.combobox-entidad>
            </div>
            @endif

            {{-- Empresa (super_admin, admin_corporativo) --}}
            @if(in_array(auth()->user()->role, [\App\Enums\Role::SUPER_ADMIN->value, \App\Enums\Role::ADMIN_CORPORATIVO->value]))
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Empresa</label>
                <x-admin.combobox-entidad
                    wire-model="filtroEmpresa"
                    placeholder="Buscar empresa..."
                    :has-error="$errors->has('filtroEmpresa')"
                    :disabled="false">
                    <option value="">Todas</option>
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                    @endforeach
                </x-admin.combobox-entidad>
            </div>
            @endif

            {{-- Sucursal (super_admin, admin_corporativo, admin_empresa) --}}
            @if(auth()->user()->role !== \App\Enums\Role::ADMIN_SUCURSAL->value)
            @php
                $sucursalDeshabilitada = in_array(auth()->user()->role, [
                    \App\Enums\Role::SUPER_ADMIN->value,
                    \App\Enums\Role::ADMIN_CORPORATIVO->value,
                ]) && !$filtroEmpresa;
            @endphp
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Sucursal</label>
                <x-admin.combobox-entidad
                    wire-model="filtroSucursal"
                    placeholder="Buscar sucursal..."
                    :has-error="$errors->has('filtroSucursal')"
                    :disabled="$sucursalDeshabilitada">
                    <option value="">Todas</option>
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                    @endforeach
                </x-admin.combobox-entidad>
            </div>
            @endif

            {{-- Lote (Todos) --}}
            @php
                $loteDeshabilitado = in_array(auth()->user()->role, [
                    \App\Enums\Role::SUPER_ADMIN->value,
                    \App\Enums\Role::ADMIN_CORPORATIVO->value,
                ]) && !$filtroEmpresa;
            @endphp
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Lote / Período</label>
                <x-admin.combobox-entidad
                    wire-model="filtroLote"
                    placeholder="Buscar período..."
                    :has-error="$errors->has('filtroLote')"
                    :disabled="$loteDeshabilitado">
                    <option value="">Todos</option>
                    @foreach($lotes as $lote)
                        <option value="{{ $lote->id }}">
                            {{ $lote->nombre ?? 'Lote #'.$lote->id }} ({{ $lote->sucursal ? $lote->sucursal->nombre : 'General' }})
                        </option>
                    @endforeach
                </x-admin.combobox-entidad>
            </div>

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
                        <th class="text-left px-6 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Token</th>
                        @if(in_array(auth()->user()->role, [\App\Enums\Role::SUPER_ADMIN->value, \App\Enums\Role::ADMIN_CORPORATIVO->value]))
                            <th class="text-left px-6 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Empresa</th>
                        @endif
                        <th class="text-left px-6 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                        <th class="text-left px-6 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Asignado</th>
                        <th class="text-left px-6 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Completado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($encuestas as $encuesta)
                        <tr class="hover:bg-slate-50 transition-colors duration-100">
                            <td class="px-6 py-3 font-mono text-xs text-slate-600">
                                {{ substr($encuesta->token, 0, 16) }}…
                            </td>
                            @if(in_array(auth()->user()->role, [\App\Enums\Role::SUPER_ADMIN->value, \App\Enums\Role::ADMIN_CORPORATIVO->value]))
                                <td class="px-6 py-3 text-slate-700">
                                    {{ $encuesta->lote?->empresa?->nombre ?? 'Sin Lote' }}
                                </td>
                            @endif
                            <td class="px-6 py-3">
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
                            <td class="px-6 py-3 text-slate-500 text-xs">
                                {{ $encuesta->fecha_asignacion?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="px-6 py-3 text-slate-500 text-xs">
                                {{ $encuesta->fecha_completada?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ in_array(auth()->user()->role, [\App\Enums\Role::SUPER_ADMIN->value, \App\Enums\Role::ADMIN_CORPORATIVO->value]) ? 5 : 4 }}" class="px-6 py-12 text-center text-slate-400 text-sm">
                                No se encontraron encuestas con esos filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($encuestas->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $encuestas->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>

</div>
