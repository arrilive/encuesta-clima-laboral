<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Gestión de corporativos</h2>
            <p class="text-sm text-slate-600 mt-1">Crea, edita y administra los grupos corporativos del sistema.</p>
        </div>
        <button
            wire:click="abrirModalCrear"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                   text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all duration-200
                   hover:-translate-y-px hover:shadow-[0_4px_16px_rgba(37,99,235,.25)]
                   active:translate-y-0 active:shadow-none">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo corporativo
        </button>
    </div>

    {{-- Búsqueda --}}
    <div class="relative max-w-sm">
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
            wire:model.live.debounce.300ms="buscar"
            type="text"
            placeholder="Buscar corporativo…"
            class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-900
                   placeholder-slate-400 bg-white focus:outline-none focus:border-blue-500
                   focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
        />
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50">
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nombre</th>
                    <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Empresas</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Administrador</th>
                    <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($corporativos as $corporativo)
                    @php $adminCorp = $corporativo->users->where('role', 'admin_corporativo')->first(); @endphp
                    <tr class="hover:bg-slate-50 transition-colors duration-100">
                        <td class="px-6 py-3.5 text-slate-900 font-medium">{{ $corporativo->nombre }}</td>
                        <td class="px-6 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                {{ $corporativo->empresas_count }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-slate-500">{{ $adminCorp?->name ?? 'Sin asignar' }}</td>
                        <td class="px-6 py-3.5 text-center">
                            @if($corporativo->activa)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">Activo</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                {{-- Editar nombre --}}
                                <button wire:click="abrirEditar({{ $corporativo->id }})"
                                        title="Editar nombre"
                                        class="p-2 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors duration-150">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                        <path d="m15 5 4 4"/>
                                    </svg>
                                </button>

                                {{-- Toggle activa --}}
                                <button wire:click="toggleActiva({{ $corporativo->id }})"
                                        title="{{ $corporativo->activa ? 'Desactivar' : 'Activar' }}"
                                        class="group p-2 rounded-lg transition-colors duration-150
                                               {{ $corporativo->activa
                                                  ? 'text-emerald-500 hover:text-red-500 hover:bg-red-50'
                                                  : 'text-slate-400 hover:text-emerald-600 hover:bg-emerald-50' }}">
                                    @if($corporativo->activa)
                                        <svg class="w-4 h-4 group-hover:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        <svg class="w-4 h-4 hidden group-hover:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 group-hover:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/>
                                        </svg>
                                        <svg class="w-4 h-4 hidden group-hover:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                    @endif
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
                            No se encontraron corporativos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if($corporativos->hasPages())
        <div class="mt-4">
            {{ $corporativos->links() }}
        </div>
    @endif

    {{-- Modales --}}

    {{-- Modal: Crear --}}
    <template x-teleport="body">
        <div x-data="{ abierto: @entangle('modalCrear') }"
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
                    <h3 class="text-base font-semibold text-slate-900">Nuevo corporativo</h3>
                    <button @click="abierto = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nombre del corporativo <span class="text-red-400">*</span></label>
                        <input wire:model="nombre" type="text" placeholder="ej. Grupo México"
                               class="w-full px-4 py-2.5 border rounded-xl text-sm text-slate-900
                                      placeholder-slate-400 focus:outline-none focus:border-blue-500
                                      focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                      {{ $errors->has('nombre') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                        @error('nombre')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button @click="abierto = false" type="button"
                            class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="crear"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                                   text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all duration-200
                                   disabled:opacity-75 disabled:cursor-not-allowed">
                        <svg wire:loading wire:target="crear" class="animate-spin w-4 h-4" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                            <path d="M12 2a10 10 0 0 1 10 10"/>
                        </svg>
                        <span wire:loading.remove wire:target="crear">Crear corporativo</span>
                        <span wire:loading wire:target="crear">Creando…</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal: Editar --}}
    <template x-teleport="body">
        <div x-data="{ abierto: @entangle('modalEditar') }"
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
                    <h3 class="text-base font-semibold text-slate-900">Editar corporativo</h3>
                    <button @click="abierto = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nombre del corporativo <span class="text-red-400">*</span></label>
                        <input wire:model="nombre" type="text"
                               class="w-full px-4 py-2.5 border rounded-xl text-sm text-slate-900
                                      focus:outline-none focus:border-blue-500
                                      focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                      {{ $errors->has('nombre') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                        @error('nombre')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button @click="abierto = false" type="button"
                            class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="editar"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                                   text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all duration-200
                                   disabled:opacity-75 disabled:cursor-not-allowed">
                        <svg wire:loading wire:target="editar" class="animate-spin w-4 h-4" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                            <path d="M12 2a10 10 0 0 1 10 10"/>
                        </svg>
                        <span wire:loading.remove wire:target="editar">Guardar</span>
                        <span wire:loading wire:target="editar">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>
