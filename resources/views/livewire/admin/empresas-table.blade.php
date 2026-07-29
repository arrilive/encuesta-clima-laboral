<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Gestión de empresas</h2>
            <p class="text-sm text-slate-600 mt-1">Crea, edita y administra las empresas del sistema.</p>
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
            Nueva empresa
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
            placeholder="Buscar empresa…"
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
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Corporativo</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Administrador</th>
                    <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Completadas</th>
                    <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Disponibles</th>
                    <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($empresas as $empresa)
                    @php $admin = $empresa->users->where('role', 'admin_empresa')->first(); @endphp
                    <tr class="hover:bg-slate-50 transition-colors duration-100">
                        <td class="px-6 py-3.5 text-slate-900 font-medium">{{ $empresa->nombre }}</td>
                        <td class="px-6 py-3.5 text-slate-500">{{ $empresa->corporativo?->nombre ?? '—' }}</td>
                        <td class="px-6 py-3.5 text-slate-500">{{ $admin?->name ?? 'Sin asignar' }}</td>
                        <td class="px-6 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                {{ $empresa->completadas }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                {{ $empresa->disponibles }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-center">
                            @if($empresa->activa)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">Activa</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Inactiva</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                {{-- Sucursales --}}
                                <button wire:click="abrirModalSucursales({{ $empresa->id }})"
                                        title="Gestionar sucursales"
                                        class="p-2 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors duration-150">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                                    </svg>
                                </button>

                                {{-- Editar --}}
                                <button wire:click="abrirEditarEmpresa({{ $empresa->id }})"
                                        title="Editar empresa"
                                        class="p-2 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors duration-150">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                        <path d="m15 5 4 4"/>
                                    </svg>
                                </button>

                                {{-- Llave maestra --}}
                                <button wire:click="abrirLlaveMaestra({{ $empresa->id }})"
                                        title="Cambiar llave maestra"
                                        class="p-2 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors duration-150">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m21 2-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                                    </svg>
                                </button>

                                {{-- Toggle activa --}}
                                <button wire:click="toggleActiva({{ $empresa->id }})"
                                        title="{{ $empresa->activa ? 'Desactivar' : 'Activar' }}"
                                        class="group p-2 rounded-lg transition-colors duration-150
                                               {{ $empresa->activa
                                                  ? 'text-emerald-500 hover:text-red-500 hover:bg-red-50'
                                                  : 'text-slate-400 hover:text-emerald-600 hover:bg-emerald-50' }}">
                                    @if($empresa->activa)
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
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm">
                            No se encontraron empresas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if($empresas->hasPages())
        <div class="mt-4">
            {{ $empresas->links() }}
        </div>
    @endif

    {{-- Modales --}}

    {{-- Modal: Crear empresa --}}
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
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-visible ring-1 ring-slate-900/5">

                {{-- Header --}}
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">Nueva empresa</h3>
                    <button @click="abierto = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                {{-- Content --}}
                <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                    {{-- Nombre empresa --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nombre de la empresa <span class="text-red-400">*</span></label>
                        <input wire:model="nombre" type="text" placeholder="ej. Coca-Cola México"
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

                    {{-- Corporativo (opcional) --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Corporativo (Opcional)</label>
                        <x-admin.combobox-entidad
                            wire-model="corporativoId"
                            placeholder="Buscar corporativo..."
                            :has-error="$errors->has('corporativoId')"
                            :disabled="false">
                            <option value="">Ninguno</option>
                            @foreach($corporativos as $corp)
                                <option value="{{ $corp->id }}">{{ $corp->nombre }}</option>
                            @endforeach
                        </x-admin.combobox-entidad>
                        @error('corporativoId')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Administrador (opcional) --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Administrador (Opcional)</label>
                        <x-admin.combobox-entidad
                            wire-model="adminId"
                            placeholder="Sin asignar"
                            :has-error="$errors->has('adminId')"
                            :disabled="false">
                            <option value="">Sin asignar</option>
                            @foreach($adminsEmpresaDisponibles as $adm)
                                <option value="{{ $adm->id }}">{{ $adm->name }} ({{ $adm->email }})</option>
                            @endforeach
                        </x-admin.combobox-entidad>
                        @error('adminId')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Llave maestra --}}
                    <div x-data="{ mostrar: false }">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Llave maestra de encuestas <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input wire:model="llaveMaestra"
                                   :type="mostrar ? 'text' : 'password'"
                                   placeholder="Mínimo 8 caracteres"
                                   class="w-full px-4 py-2.5 pr-10 border rounded-xl text-sm text-slate-900
                                          placeholder-slate-400 focus:outline-none focus:border-blue-500
                                          focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                          {{ $errors->has('llaveMaestra') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                            <button type="button" @click="mostrar = !mostrar"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                <svg x-show="!mostrar" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg x-show="mostrar" x-cloak class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                    <line x1="2" x2="22" y1="2" y2="22"/>
                                </svg>
                            </button>
                        </div>
                        @error('llaveMaestra')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="text-xs text-slate-400 mt-1.5">La contraseña que los empleados usarán para acceder a la encuesta.</p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-2xl">
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
                        <span wire:loading.remove wire:target="crear">Crear empresa</span>
                        <span wire:loading wire:target="crear">Creando…</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal: Editar empresa (anteriormente Editar nombre) --}}
    <template x-teleport="body">
        <div x-data="{ abierto: @entangle('modalEditarEmpresa') }"
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
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-visible ring-1 ring-slate-900/5">

                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">Editar empresa</h3>
                    <button @click="abierto = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Nombre --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nombre de la empresa <span class="text-red-400">*</span></label>
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

                    {{-- Corporativo --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Corporativo (Opcional)</label>
                        <x-admin.combobox-entidad
                            wire-model="corporativoId"
                            placeholder="Buscar corporativo..."
                            :has-error="$errors->has('corporativoId')"
                            :disabled="false">
                            <option value="">Ninguno</option>
                            @foreach($corporativos as $corp)
                                <option value="{{ $corp->id }}">{{ $corp->nombre }}</option>
                            @endforeach
                        </x-admin.combobox-entidad>
                        @error('corporativoId')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Administrador --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Administrador (Opcional)</label>
                        <x-admin.combobox-entidad
                            wire-model="adminId"
                            placeholder="Sin asignar"
                            :has-error="$errors->has('adminId')"
                            :disabled="false">
                            <option value="">Sin asignar</option>
                            @foreach($adminsEmpresaDisponibles as $adm)
                                <option value="{{ $adm->id }}">{{ $adm->name }} ({{ $adm->email }})</option>
                            @endforeach
                        </x-admin.combobox-entidad>
                        @error('adminId')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-2xl">
                    <button @click="abierto = false" type="button"
                            class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="editarEmpresa"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                                   text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all duration-200
                                   disabled:opacity-75 disabled:cursor-not-allowed">
                        <svg wire:loading wire:target="editarEmpresa" class="animate-spin w-4 h-4" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                            <path d="M12 2a10 10 0 0 1 10 10"/>
                        </svg>
                        <span wire:loading.remove wire:target="editarEmpresa">Guardar</span>
                        <span wire:loading wire:target="editarEmpresa">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal: Cambiar llave maestra --}}
    <template x-teleport="body">
        <div x-data="{ abierto: @entangle('modalLlaveMaestra') }"
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
                    <h3 class="text-base font-semibold text-slate-900">Cambiar llave maestra</h3>
                    <button @click="abierto = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <p class="text-sm text-slate-500">La llave maestra es la contraseña que los empleados usan para acceder a la encuesta.</p>

                    <div x-data="{ mostrar: false }">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nueva llave maestra <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input wire:model="llaveMaestra"
                                   :type="mostrar ? 'text' : 'password'"
                                   placeholder="Mínimo 8 caracteres"
                                   class="w-full px-4 py-2.5 pr-10 border rounded-xl text-sm text-slate-900
                                          placeholder-slate-400 focus:outline-none focus:border-blue-500
                                          focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                          {{ $errors->has('llaveMaestra') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                            <button type="button" @click="mostrar = !mostrar"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                <svg x-show="!mostrar" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg x-show="mostrar" x-cloak class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                    <line x1="2" x2="22" y1="2" y2="22"/>
                                </svg>
                            </button>
                        </div>
                        @error('llaveMaestra')
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
                    <button wire:click="cambiarLlave"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                                   text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all duration-200
                                   disabled:opacity-75 disabled:cursor-not-allowed">
                        <svg wire:loading wire:target="cambiarLlave" class="animate-spin w-4 h-4" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                            <path d="M12 2a10 10 0 0 1 10 10"/>
                        </svg>
                        <span wire:loading.remove wire:target="cambiarLlave">Cambiar llave</span>
                        <span wire:loading wire:target="cambiarLlave">Cambiando…</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal: Contraseña generada --}}
    <template x-teleport="body">
        <div x-data="{
                 abierto: @entangle('modalPasswordGenerada'),
                 copiado: false,
                 get password() { return $wire.passwordGenerada ?? '' }
             }"
             x-show="abierto" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">

            <div x-show="abierto" x-transition.opacity
                 class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

            <div x-show="abierto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden ring-1 ring-slate-900/5">

                <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                    <div class="flex items-center justify-center w-8 h-8 bg-emerald-100 rounded-lg">
                        <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900">Contraseña generada</h3>
                </div>

                <div class="p-6 space-y-5">
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
                        <pre class="text-sm font-mono text-slate-900 select-all" x-text="password"></pre>
                        <button @click="navigator.clipboard.writeText(password); copiado = true; setTimeout(() => copiado = false, 2000)"
                                class="ml-3 p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors duration-150">
                            <svg x-show="!copiado" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                            </svg>
                            <svg x-show="copiado" x-cloak class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        <p class="text-sm text-amber-800">Esta contraseña no se puede recuperar. Cópiala y guárdala antes de cerrar esta ventana.</p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button wire:click="cerrarPasswordGenerada"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                                   text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all duration-200">
                        Entendido, cerrar
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- ════════════════════════════════════════════════════════════════════════
         MODALES DE SUCURSALES (CRUD)
         ════════════════════════════════════════════════════════════════════════ --}}

    {{-- Modal: Listar Sucursales --}}
    <template x-teleport="body">
        <div x-data="{ abierto: @entangle('modalSucursales') }"
             x-show="abierto" x-cloak
             x-on:keyup.escape.window="abierto = false"
             class="fixed inset-0 z-40 flex items-center justify-center p-4 sm:p-0">

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
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-3xl overflow-hidden ring-1 ring-slate-900/5">

                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">
                            Sucursales de {{ $empresaSeleccionada?->nombre ?? '' }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Administra las sedes físicas de esta empresa.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button wire:click="abrirCrearSucursal"
                                class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700
                                       text-white font-semibold text-xs px-4 py-2 rounded-xl transition-all duration-200">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Nueva sucursal
                        </button>
                        <button @click="abierto = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6 max-h-[50vh] overflow-y-auto">
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50">
                                    <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nombre</th>
                                    <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Administrador</th>
                                    <th class="text-center px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                    <th class="text-right px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($sucursales as $suc)
                                    @php $sucAdmin = $suc->users->where('role', 'admin_sucursal')->first(); @endphp
                                    <tr class="hover:bg-slate-50 transition-colors duration-100">
                                        <td class="px-5 py-3 text-slate-900 font-medium">{{ $suc->nombre }}</td>
                                        <td class="px-5 py-3 text-slate-500">{{ $sucAdmin?->name ?? 'Sin asignar' }}</td>
                                        <td class="px-5 py-3 text-center">
                                            @if($suc->activa)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">Activa</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Inactiva</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center justify-end gap-1">
                                                {{-- Editar nombre --}}
                                                <button wire:click="abrirEditarSucursal({{ $suc->id }})"
                                                        title="Editar nombre"
                                                        class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                                    </svg>
                                                </button>

                                                {{-- Cambiar llave maestra --}}
                                                <button wire:click="abrirLlaveSucursal({{ $suc->id }})"
                                                        title="Cambiar llave de sucursal"
                                                        class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="m21 2-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                                                    </svg>
                                                </button>

                                                {{-- Toggle activa --}}
                                                <button wire:click="toggleActivaSucursal({{ $suc->id }})"
                                                        title="{{ $suc->activa ? 'Desactivar' : 'Activar' }}"
                                                        class="group p-1.5 rounded-lg transition-colors
                                                               {{ $suc->activa
                                                                  ? 'text-emerald-500 hover:text-red-500 hover:bg-red-50'
                                                                  : 'text-slate-400 hover:text-emerald-600 hover:bg-emerald-50' }}">
                                                    @if($suc->activa)
                                                        <svg class="w-3.5 h-3.5 group-hover:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12"/>
                                                        </svg>
                                                        <svg class="w-3.5 h-3.5 hidden group-hover:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-3.5 h-3.5 group-hover:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/>
                                                        </svg>
                                                        <svg class="w-3.5 h-3.5 hidden group-hover:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12"/>
                                                        </svg>
                                                    @endif
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-8 text-center text-slate-400 text-xs">
                                            No hay sucursales registradas para esta empresa.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button @click="abierto = false" type="button"
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal: Crear Sucursal --}}
    <template x-teleport="body">
        <div x-data="{ abierto: @entangle('modalCrearSucursal') }"
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
                    <h3 class="text-base font-semibold text-slate-900">Nueva sucursal</h3>
                    <button @click="abierto = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Nombre --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nombre de la sucursal <span class="text-red-400">*</span></label>
                        <input wire:model="sucursalNombre" type="text" placeholder="ej. Sucursal Centro"
                               class="w-full px-4 py-2.5 border rounded-xl text-sm text-slate-900
                                      focus:outline-none focus:border-blue-500
                                      focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                      {{ $errors->has('sucursalNombre') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                        @error('sucursalNombre')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Llave --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Llave maestra de sucursal <span class="text-red-400">*</span></label>
                        <input wire:model="sucursalLlave" type="password" placeholder="Mínimo 8 caracteres"
                               class="w-full px-4 py-2.5 border rounded-xl text-sm text-slate-900
                                      focus:outline-none focus:border-blue-500
                                      focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                      {{ $errors->has('sucursalLlave') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                        @error('sucursalLlave')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Administrador de Sucursal --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Administrador (Opcional)</label>
                        <x-admin.combobox-entidad
                            wire-model="sucursalAdminId"
                            placeholder="Sin asignar"
                            :has-error="$errors->has('sucursalAdminId')"
                            :disabled="false">
                            <option value="">Sin asignar</option>
                            @foreach($adminsSucursalDisponibles as $sadm)
                                <option value="{{ $sadm->id }}">{{ $sadm->name }} ({{ $sadm->email }})</option>
                            @endforeach
                        </x-admin.combobox-entidad>
                        @error('sucursalAdminId')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button @click="abierto = false" type="button"
                            class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="crearSucursal"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                                   text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all duration-200">
                        <span wire:loading.remove wire:target="crearSucursal">Crear sucursal</span>
                        <span wire:loading wire:target="crearSucursal">Creando…</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal: Editar Sucursal --}}
    <template x-teleport="body">
        <div x-data="{ abierto: @entangle('modalEditarSucursal') }"
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
                    <h3 class="text-base font-semibold text-slate-900">Editar sucursal</h3>
                    <button @click="abierto = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nombre de la sucursal <span class="text-red-400">*</span></label>
                        <input wire:model="sucursalNombre" type="text"
                               class="w-full px-4 py-2.5 border rounded-xl text-sm text-slate-900
                                      focus:outline-none focus:border-blue-500
                                      focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                      {{ $errors->has('sucursalNombre') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                        @error('sucursalNombre')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Administrador de Sucursal --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Administrador (Opcional)</label>
                        <x-admin.combobox-entidad
                            wire-model="sucursalAdminId"
                            placeholder="Sin asignar"
                            :has-error="$errors->has('sucursalAdminId')"
                            :disabled="false">
                            <option value="">Sin asignar</option>
                            @foreach($adminsSucursalDisponibles as $sadm)
                                <option value="{{ $sadm->id }}">{{ $sadm->name }} ({{ $sadm->email }})</option>
                            @endforeach
                        </x-admin.combobox-entidad>
                        @error('sucursalAdminId')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button @click="abierto = false" type="button"
                            class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="editarSucursal"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                                   text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all duration-200">
                        <span wire:loading.remove wire:target="editarSucursal">Guardar</span>
                        <span wire:loading wire:target="editarSucursal">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal: Cambiar llave de Sucursal --}}
    <template x-teleport="body">
        <div x-data="{ abierto: @entangle('modalLlaveSucursal') }"
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
                    <h3 class="text-base font-semibold text-slate-900">Cambiar llave de sucursal</h3>
                    <button @click="abierto = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <p class="text-sm text-slate-500">Ingrese la nueva llave maestra propia de esta sucursal.</p>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nueva llave maestra <span class="text-red-400">*</span></label>
                        <input wire:model="sucursalLlave" type="password" placeholder="Mínimo 8 caracteres"
                               class="w-full px-4 py-2.5 border rounded-xl text-sm text-slate-900
                                      focus:outline-none focus:border-blue-500
                                      focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                      {{ $errors->has('sucursalLlave') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                        @error('sucursalLlave')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button @click="abierto = false" type="button"
                            class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="cambiarLlaveSucursal"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                                   text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all duration-200">
                        <span wire:loading.remove wire:target="cambiarLlaveSucursal">Cambiar llave</span>
                        <span wire:loading wire:target="cambiarLlaveSucursal">Cambiando…</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>
