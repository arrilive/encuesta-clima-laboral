<div class="space-y-6 max-w-7xl">

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5">

        {{-- Barra superior: acción --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <span class="text-base font-semibold text-slate-800 tracking-tight">Filtros</span>
                @if(auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value && !empty($selectedTokens) && count($selectedTokens) > 0)
                    <span class="inline-block text-xs font-medium px-2.5 py-0.5 rounded-full bg-red-50 text-red-700">
                        {{ count($selectedTokens) }} seleccionado(s)
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-3">
                @if(auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value && !empty($selectedTokens) && count($selectedTokens) > 0)
                    <button wire:click="confirmarEliminacion"
                            class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700
                                   text-white font-semibold text-sm px-4 py-2 rounded-xl transition-all duration-200
                                   hover:-translate-y-px hover:shadow-[0_4px_16px_rgba(220,38,38,.25)]
                                   active:translate-y-0 active:shadow-none">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                        </svg>
                        Eliminar tokens seleccionados
                    </button>
                @endif

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
            </div>
        </div>

        {{-- Grid de filtros --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

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
                        @if(auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value)
                            <th class="w-10 px-6 py-3 text-center">
                                <input
                                    type="checkbox"
                                    wire:model.live="selectAll"
                                    class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/10 focus:ring-2 cursor-pointer transition-colors"
                                    title="Seleccionar todos los tokens disponibles del filtro actual"
                                />
                            </th>
                        @endif
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Token</th>
                        @if(in_array(auth()->user()->role, [\App\Enums\Role::SUPER_ADMIN->value, \App\Enums\Role::ADMIN_CORPORATIVO->value]))
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Empresa / Sucursal</th>
                        @endif
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Asignado</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Completado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($encuestas as $encuesta)
                        <tr class="hover:bg-slate-50 transition-colors duration-100">
                            @if(auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value)
                                <td class="px-6 py-3.5 text-center">
                                    @if($encuesta->estado === 'disponible')
                                        <input
                                            type="checkbox"
                                            wire:model.live="selectedTokens"
                                            value="{{ $encuesta->id }}"
                                            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/10 focus:ring-2 cursor-pointer transition-colors"
                                        />
                                    @else
                                        <input
                                            type="checkbox"
                                            disabled
                                            class="w-4 h-4 rounded border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed"
                                            title="Solo se pueden eliminar tokens en estado disponible"
                                        />
                                    @endif
                                </td>
                            @endif
                            <td class="px-6 py-3.5 font-mono text-xs text-slate-600">
                                {{ substr($encuesta->token, 0, 16) }}…
                            </td>
                            @if(in_array(auth()->user()->role, [\App\Enums\Role::SUPER_ADMIN->value, \App\Enums\Role::ADMIN_CORPORATIVO->value]))
                                <td class="px-6 py-3.5 text-slate-700">
                                    {{ $encuesta->lote?->empresa?->nombre ?? 'Sin Lote' }}{{ $encuesta->lote?->sucursal ? ' · '.$encuesta->lote->sucursal->nombre : '' }}
                                </td>
                            @endif
                            <td class="px-6 py-3.5">
                                <span class="inline-block text-xs font-medium px-2 py-0.5 rounded-full {{ match($encuesta->estado) {
                                    'disponible'  => 'bg-slate-100 text-slate-700',
                                    'asignado'    => 'bg-amber-50 text-amber-700',
                                    'en_progreso' => 'bg-blue-50 text-blue-700',
                                    'completado'  => 'bg-emerald-50 text-emerald-700',
                                    default       => 'bg-slate-100 text-slate-700',
                                } }}">
                                    {{ match($encuesta->estado) {
                                        'disponible'  => 'Disponible',
                                        'asignado'    => 'Asignado',
                                        'en_progreso' => 'En progreso',
                                        'completado'  => 'Completado',
                                        default       => $encuesta->estado,
                                    } }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-slate-500 text-xs">
                                {{ $encuesta->fecha_asignacion?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="px-6 py-3.5 text-slate-500 text-xs">
                                {{ $encuesta->fecha_completada?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            @php
                                $cols = 4;
                                if (auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value) $cols++;
                                if (in_array(auth()->user()->role, [\App\Enums\Role::SUPER_ADMIN->value, \App\Enums\Role::ADMIN_CORPORATIVO->value])) $cols++;
                            @endphp
                            <td colspan="{{ $cols }}" class="px-6 py-12 text-center text-slate-400 text-sm">
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

    {{-- Modal: Confirmar Eliminar Tokens --}}
    <template x-teleport="body">
        <div x-data="{ abierto: @entangle('confirmandoEliminacion') }"
             x-show="abierto" x-cloak
             x-on:keyup.escape.window="abierto = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">

            <div x-show="abierto" x-transition.opacity
                 class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"
                 @click="abierto = false"></div>

            <div x-show="abierto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden ring-1 ring-slate-900/5">

                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">Eliminar tokens seleccionados</h3>
                    <button @click="abierto = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <p class="text-sm text-slate-600">
                        ¿Estás seguro de que deseas eliminar los <strong>{{ count($selectedTokens) }}</strong> tokens seleccionados? Esta acción es permanente y no se puede deshacer.
                    </p>

                    <div class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        <p class="text-xs text-amber-800">
                            Solo se eliminarán los tokens en estado <strong class="font-semibold">disponible</strong>. La Tasa de Participación se actualizará recalculando el total de tokens de los lotes afectados.
                        </p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button @click="abierto = false" type="button"
                            class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="eliminarTokensSeleccionados"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700
                                   text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all duration-200
                                   disabled:opacity-75 disabled:cursor-not-allowed">
                        <svg wire:loading wire:target="eliminarTokensSeleccionados" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                            <path d="M12 2a10 10 0 0 1 10 10"/>
                        </svg>
                        <span wire:loading.remove wire:target="eliminarTokensSeleccionados">Confirmar eliminación</span>
                        <span wire:loading wire:target="eliminarTokensSeleccionados">Eliminando…</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>
