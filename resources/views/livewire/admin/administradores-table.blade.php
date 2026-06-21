<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Gestión de administradores</h2>
            <p class="text-sm text-slate-400 mt-0.5">Crea, edita y administra los usuarios con roles administrativos en el sistema.</p>
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
            Nuevo administrador
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
            placeholder="Buscar administrador…"
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
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Email</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Rol</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Entidad Asignada</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                    <tr class="hover:bg-slate-50 transition-colors duration-100">
                        <td class="px-6 py-3.5 text-slate-900 font-medium">{{ $user->name }}</td>
                        <td class="px-6 py-3.5 text-slate-500">{{ $user->email }}</td>
                        <td class="px-6 py-3.5">
                            <span class="inline-block text-xs font-medium bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">
                                {{ match($user->role) {
                                    'admin_corporativo' => 'Admin Corporativo',
                                    'admin_empresa'     => 'Admin Empresa',
                                    'admin_sucursal'    => 'Admin Sucursal',
                                    default             => $user->role,
                                } }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-slate-500">
                            @if($user->role === 'admin_corporativo')
                                <span class="text-slate-600 font-medium">{{ $user->corporativo?->nombre ?? '—' }}</span> <span class="text-xs text-slate-400">(Corporativo)</span>
                            @elseif($user->role === 'admin_empresa')
                                <span class="text-slate-600 font-medium">{{ $user->empresa?->nombre ?? '—' }}</span> <span class="text-xs text-slate-400">(Empresa)</span>
                            @elseif($user->role === 'admin_sucursal')
                                <span class="text-slate-600 font-medium">{{ $user->sucursal?->nombre ?? '—' }}</span> <span class="text-xs text-slate-400">({{ $user->sucursal?->empresa?->nombre ?? '—' }})</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                {{-- Editar --}}
                                <button wire:click="abrirEditar({{ $user->id }})"
                                        title="Editar administrador"
                                        class="p-2 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors duration-150">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                        <path d="m15 5 4 4"/>
                                    </svg>
                                </button>

                                {{-- Regenerar contraseña --}}
                                <button wire:click="regenerarPassword({{ $user->id }})"
                                        wire:confirm="¿Estás seguro de que deseas regenerar la contraseña de este administrador? La contraseña anterior dejará de funcionar inmediatamente."
                                        title="Regenerar contraseña"
                                        class="p-2 rounded-lg text-slate-400 hover:text-violet-600 hover:bg-violet-50 transition-colors duration-150">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                </button>

                                {{-- Eliminar --}}
                                <button wire:click="abrirEliminar({{ $user->id }})"
                                        title="Eliminar administrador"
                                        class="p-2 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors duration-150">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
                            No se encontraron administradores.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if($users->hasPages())
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif

    {{-- Modales --}}

    {{-- Modal: Crear --}}
    <template x-teleport="body">
        <div x-data="{ abierto: @entangle('modalCrear'), rol: @entangle('rol') }"
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
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden ring-1 ring-slate-900/5">

                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">Nuevo administrador</h3>
                    <button @click="abierto = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                    {{-- Nombre --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nombre <span class="text-red-400">*</span></label>
                        <input wire:model="nombre" type="text" placeholder="ej. Juan Pérez"
                               class="w-full px-4 py-2.5 border rounded-xl text-sm text-slate-900
                                      focus:outline-none focus:border-blue-500
                                      focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                      {{ $errors->has('nombre') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                        @error('nombre')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Correo electrónico <span class="text-red-400">*</span></label>
                        <input wire:model="email" type="email" placeholder="ej. juan@empresa.com"
                               class="w-full px-4 py-2.5 border rounded-xl text-sm text-slate-900
                                      focus:outline-none focus:border-blue-500
                                      focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                      {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                        @error('email')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Rol --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Rol de administrador <span class="text-red-400">*</span></label>
                        <select wire:model.live="rol"
                                class="w-full px-4 py-2.5 border border-slate-300 bg-white rounded-xl text-sm text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200">
                            <option value="">Selecciona un rol</option>
                            <option value="admin_corporativo">Admin Corporativo</option>
                            <option value="admin_empresa">Admin Empresa</option>
                            <option value="admin_sucursal">Admin Sucursal</option>
                        </select>
                        @error('rol')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Selectores dinámicos basados en el Rol --}}
                    @if($rol === 'admin_corporativo')
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Corporativo Asociado <span class="text-red-400">*</span></label>
                            <x-admin.combobox-entidad
                                wire-model="corporativoId"
                                placeholder="Buscar corporativo..."
                                :has-error="$errors->has('corporativoId')"
                                :disabled="false">
                                <option value="">Selecciona un corporativo</option>
                                @foreach($corporativos as $corp)
                                    <option value="{{ $corp->id }}">{{ $corp->nombre }}</option>
                                @endforeach
                            </x-admin.combobox-entidad>
                            @error('corporativoId')
                                <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @elseif($rol === 'admin_empresa')
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Empresa Asociada <span class="text-red-400">*</span></label>
                            <x-admin.combobox-entidad
                                wire-model="empresaId"
                                placeholder="Buscar empresa..."
                                :has-error="$errors->has('empresaId')"
                                :disabled="false">
                                <option value="">Selecciona una empresa</option>
                                @foreach($empresas as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                                @endforeach
                            </x-admin.combobox-entidad>
                            @error('empresaId')
                                <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @elseif($rol === 'admin_sucursal')
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Sucursal Asociada <span class="text-red-400">*</span></label>
                            <x-admin.combobox-entidad
                                wire-model="sucursalId"
                                placeholder="Buscar sucursal..."
                                :has-error="$errors->has('sucursalId')"
                                :disabled="false">
                                <option value="">Selecciona una sucursal</option>
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->id }}">{{ $suc->nombre }} ({{ $suc->empresa->nombre }})</option>
                                @endforeach
                            </x-admin.combobox-entidad>
                            @error('sucursalId')
                                <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
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
                        <span wire:loading.remove wire:target="crear">Crear administrador</span>
                        <span wire:loading wire:target="crear">Creando…</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal: Editar --}}
    <template x-teleport="body">
        <div x-data="{ abierto: @entangle('modalEditar'), rol: @entangle('rol') }"
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
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden ring-1 ring-slate-900/5">

                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">Editar administrador</h3>
                    <button @click="abierto = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                    {{-- Nombre --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nombre <span class="text-red-400">*</span></label>
                        <input wire:model="nombre" type="text"
                               class="w-full px-4 py-2.5 border rounded-xl text-sm text-slate-900
                                      focus:outline-none focus:border-blue-500
                                      focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                      {{ $errors->has('nombre') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                        @error('nombre')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Correo electrónico <span class="text-red-400">*</span></label>
                        <input wire:model="email" type="email"
                               class="w-full px-4 py-2.5 border rounded-xl text-sm text-slate-900
                                      focus:outline-none focus:border-blue-500
                                      focus:ring-4 focus:ring-blue-500/10 transition-all duration-200
                                      {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}" />
                        @error('email')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Rol --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Rol de administrador <span class="text-red-400">*</span></label>
                        <select wire:model.live="rol"
                                class="w-full px-4 py-2.5 border border-slate-300 bg-white rounded-xl text-sm text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200">
                            <option value="">Selecciona un rol</option>
                            <option value="admin_corporativo">Admin Corporativo</option>
                            <option value="admin_empresa">Admin Empresa</option>
                            <option value="admin_sucursal">Admin Sucursal</option>
                        </select>
                        @error('rol')
                            <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Selectores dinámicos basados en el Rol --}}
                    @if($rol === 'admin_corporativo')
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Corporativo Asociado <span class="text-red-400">*</span></label>
                            <x-admin.combobox-entidad
                                wire-model="corporativoId"
                                placeholder="Buscar corporativo..."
                                :has-error="$errors->has('corporativoId')"
                                :disabled="false">
                                <option value="">Selecciona un corporativo</option>
                                @foreach($corporativos as $corp)
                                    <option value="{{ $corp->id }}">{{ $corp->nombre }}</option>
                                @endforeach
                            </x-admin.combobox-entidad>
                            @error('corporativoId')
                                <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @elseif($rol === 'admin_empresa')
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Empresa Asociada <span class="text-red-400">*</span></label>
                            <x-admin.combobox-entidad
                                wire-model="empresaId"
                                placeholder="Buscar empresa..."
                                :has-error="$errors->has('empresaId')"
                                :disabled="false">
                                <option value="">Selecciona una empresa</option>
                                @foreach($empresas as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                                @endforeach
                            </x-admin.combobox-entidad>
                            @error('empresaId')
                                <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @elseif($rol === 'admin_sucursal')
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Sucursal Asociada <span class="text-red-400">*</span></label>
                            <x-admin.combobox-entidad
                                wire-model="sucursalId"
                                placeholder="Buscar sucursal..."
                                :has-error="$errors->has('sucursalId')"
                                :disabled="false">
                                <option value="">Selecciona una sucursal</option>
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->id }}">{{ $suc->nombre }} ({{ $suc->empresa->nombre }})</option>
                                @endforeach
                            </x-admin.combobox-entidad>
                            @error('sucursalId')
                                <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
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

    {{-- Modal: Confirmar Eliminar --}}
    <template x-teleport="body">
        <div x-data="{ abierto: @entangle('modalEliminar') }"
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
                    <h3 class="text-base font-semibold text-slate-900">Eliminar administrador</h3>
                    <button @click="abierto = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <p class="text-sm text-slate-600">¿Estás seguro de que deseas eliminar este administrador? Esta acción no se puede deshacer.</p>

                    @if($errorEliminar)
                        <div class="flex items-start gap-2.5 p-3.5 bg-red-50 border border-red-200 rounded-xl">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <p class="text-xs text-red-700">{{ $errorEliminar }}</p>
                        </div>
                    @endif
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button @click="abierto = false" type="button"
                            class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="eliminar"
                            class="inline-flex items-center bg-red-600 hover:bg-red-700
                                   text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all duration-200">
                        Eliminar
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

</div>
