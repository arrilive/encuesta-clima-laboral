<div class="space-y-6 max-w-7xl">

    {{-- Formulario --}}
    @if(auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value)
    <div
        x-data="{
            modo: @entangle('modo'),
            mostrarConfirmacion: @entangle('mostrarConfirmacion'),
            fechaFinLote: '{{ $loteSeleccionado?->fecha_fin ? $loteSeleccionado->fecha_fin->toDateString() : '' }}',
            get diasParaExpirar() {
                if (!this.fechaFinLote) return null;
                let hoy = new Date();
                hoy.setHours(0,0,0,0);
                let exp = new Date(this.fechaFinLote + 'T00:00:00');
                let diff = exp.getTime() - hoy.getTime();
                return Math.ceil(diff / (1000 * 60 * 60 * 24));
            },
            get expiraPronto() {
                let d = this.diasParaExpirar;
                return d !== null && d < 7;
            }
        }"
        x-effect="fechaFinLote = '{{ $loteSeleccionado?->fecha_fin ? $loteSeleccionado->fecha_fin->toDateString() : '' }}'"
        class="bg-white rounded-2xl border border-slate-200 p-6 max-w-4xl"
    >
        <h2 class="text-sm font-semibold text-slate-900 mb-5 transition-all duration-300" x-text="modo === 'a' ? 'Generar nuevo lote de tokens' : 'Agregar tokens a un lote existente'"></h2>

        @if($generado)
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl mb-5">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <p class="text-sm text-emerald-700 font-medium">
                    {{ $totalGenerado }} {{ $totalGenerado === 1 ? 'token generado' : 'tokens generados' }} correctamente.
                </p>
            </div>
        @endif

        {{-- Tabs de Selección de Modo --}}
        <div class="flex border-b border-slate-200 mb-6">
            <button
                type="button"
                @click="modo = 'a'"
                :class="modo === 'a' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm mr-8 transition-colors duration-150 focus:outline-none">
                Crear nuevo lote
            </button>
            <button
                type="button"
                @click="modo = 'b'"
                :class="modo === 'b' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm transition-colors duration-150 focus:outline-none">
                Agregar a un lote existente
            </button>
        </div>

        {{-- Modo A --}}
        <div 
            x-show="modo === 'a'" 
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6"
        >
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            {{-- Empresa (solo super_admin) --}}
            @if(auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value)
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Empresa <span class="text-red-400">*</span></label>
                    <x-admin.combobox-entidad
                        wire-model="empresaId"
                        placeholder="Buscar empresa..."
                        :has-error="$errors->has('empresaId')"
                        :disabled="false">
                        <option value="">Selecciona una empresa</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" @disabled(!$empresa->activa)>
                                {{ $empresa->nombre }}{{ !$empresa->activa ? ' (Inactiva)' : '' }}
                            </option>
                        @endforeach
                    </x-admin.combobox-entidad>
                    @error('empresaId')
                        <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Sucursal (opcional, aparece al seleccionar empresa) --}}
                @if($empresaId)
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Sucursal
                        <span class="text-slate-400 font-normal ml-1">(opcional)</span>
                    </label>
                    <x-admin.combobox-entidad
                        wire-model="sucursalId"
                        placeholder="Buscar sucursal..."
                        :has-error="$errors->has('sucursalId')"
                        :disabled="false">
                        <option value="">General (toda la empresa)</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                        @endforeach
                    </x-admin.combobox-entidad>
                    @error('sucursalId')
                        <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
                @endif
            @endif

            {{-- Cantidad --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Cantidad <span class="text-red-400">*</span></label>
                <input
                    wire:model="tokensTotal"
                    type="number"
                    min="1"
                    max="500"
                    inputmode="numeric"
                    class="w-full px-4 py-2.5 border rounded-xl text-sm text-slate-900
                           focus:outline-none focus:border-blue-500 focus:ring-4
                           focus:ring-blue-500/10 transition-all duration-200
                           {{ $errors->has('tokensTotal') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}"
                />
                @error('tokensTotal')
                    <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ $message }}
                    </p>
                @enderror
                <p class="text-xs text-slate-400 mt-1">Mínimo 1, máximo 500</p>
            </div>

            {{-- Nombre del lote (opcional) --}}
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nombre del lote
                    <span class="text-slate-400 font-normal ml-1">(opcional)</span>
                </label>
                <input
                    wire:model="nombre"
                    type="text"
                    placeholder="ej. Campaña Marzo 2026"
                    maxlength="75"
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-900
                           placeholder-slate-400 bg-white focus:outline-none focus:border-blue-500
                           focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
                />
            </div>

            {{-- Fecha de Inicio --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Fecha de inicio <span class="text-red-400">*</span></label>
                <input
                    wire:model="fechaInicio"
                    type="date"
                    class="w-full px-4 py-2.5 border rounded-xl text-sm text-slate-900
                           focus:outline-none focus:border-blue-500 focus:ring-4
                           focus:ring-blue-500/10 transition-all duration-200
                           {{ $errors->has('fechaInicio') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}"
                />
                @error('fechaInicio')
                    <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Fecha de Fin --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Fecha de finalización <span class="text-red-400">*</span></label>
                <input
                    wire:model="fechaFin"
                    type="date"
                    class="w-full px-4 py-2.5 border rounded-xl text-sm text-slate-900
                           focus:outline-none focus:border-blue-500 focus:ring-4
                           focus:ring-blue-500/10 transition-all duration-200
                           {{ $errors->has('fechaFin') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}"
                />
                @error('fechaFin')
                    <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

        </div>

        <div class="mt-6">
            <button
                wire:click="generar"
                wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                       text-white font-semibold text-sm px-6 py-3 rounded-xl transition-all duration-200
                       hover:-translate-y-px hover:shadow-[0_4px_16px_rgba(37,99,235,.25)]
                       active:translate-y-0 active:shadow-none disabled:opacity-75 disabled:cursor-not-allowed">
                <svg wire:loading wire:target="generar" class="animate-spin w-4 h-4" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                    <path d="M12 2a10 10 0 0 1 10 10"/>
                </svg>
                <svg wire:loading.remove wire:target="generar" class="w-4 h-4" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="8" cy="8" r="6"/>
                    <path d="M18.09 10.37A6 6 0 1 1 10.34 18"/>
                    <path d="M7 6h1v4"/>
                    <path d="m16.71 13.88.7.71-2.82 2.82"/>
                </svg>
                <span wire:loading.remove wire:target="generar">Generar tokens</span>
                <span wire:loading wire:target="generar">Generando…</span>
            </button>
        </div>
        </div>

        {{-- Modo B --}}
        <div 
            x-show="modo === 'b'" 
            x-cloak 
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6"
        >
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Empresa Selector --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Empresa <span class="text-red-400">*</span></label>
                    <x-admin.combobox-entidad
                        wire-model="empresaIdModoB"
                        placeholder="Buscar empresa..."
                        :has-error="$errors->has('empresaIdModoB')"
                        :disabled="false">
                        <option value="">Selecciona una empresa</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" @disabled(!$empresa->activa)>
                                {{ $empresa->nombre }}{{ !$empresa->activa ? ' (Inactiva)' : '' }}
                            </option>
                        @endforeach
                    </x-admin.combobox-entidad>
                    @error('empresaIdModoB')
                        <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Lote Selector --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Lote de destino <span class="text-red-400">*</span></label>
                    <x-admin.combobox-entidad
                        wire-model="loteId"
                        placeholder="Buscar lote..."
                        :has-error="$errors->has('loteId')"
                        :disabled="empty($empresaIdModoB)">
                        <option value="">Selecciona un lote vigente</option>
                        @foreach($lotesVigentes as $l)
                            <option value="{{ $l->id }}">
                                {{ $l->nombre ?? 'Lote #'.$l->id }}
                                ({{ $l->sucursal ? $l->sucursal->nombre : 'General' }})
                                — vence {{ $l->fecha_fin->format('d/m/Y') }}
                            </option>
                        @endforeach
                    </x-admin.combobox-entidad>
                    @error('loteId')
                        <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Bloque Informativo del Lote Seleccionado --}}
                @if($loteSeleccionado)
                    <div class="sm:col-span-2 p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                        <div class="flex justify-between items-center text-xs text-slate-600">
                            <span>Fecha de finalización actual del lote:</span>
                            <span class="font-semibold text-slate-800">
                                {{ $loteSeleccionado->fecha_fin ? $loteSeleccionado->fecha_fin->format('d/m/Y') : 'Sin registrar' }}
                            </span>
                        </div>

                        {{-- Alerta ámbar en Alpine.js --}}
                        <div x-show="expiraPronto" x-cloak class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-xs">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            <div>
                                <span class="font-semibold">Atención:</span> El lote seleccionado expira en <span class="font-bold" x-text="diasParaExpirar"></span> días. Te recomendamos extender la vigencia del lote.
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Extender vigencia (opcional) --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Extender vigencia
                        <span class="text-slate-400 font-normal ml-1">(opcional)</span>
                    </label>
                    <input
                        wire:model="nuevaFechaFin"
                        type="date"
                        class="w-full px-4 py-2.5 border rounded-xl text-sm text-slate-900
                               focus:outline-none focus:border-blue-500 focus:ring-4
                               focus:ring-blue-500/10 transition-all duration-200
                               {{ $errors->has('nuevaFechaFin') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}"
                    />
                    @error('nuevaFechaFin')
                        <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Cantidad Modo B --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Cantidad <span class="text-red-400">*</span></label>
                    <input
                        wire:model="cantidadModoB"
                        type="number"
                        min="1"
                        max="500"
                        inputmode="numeric"
                        class="w-full px-4 py-2.5 border rounded-xl text-sm text-slate-900
                               focus:outline-none focus:border-blue-500 focus:ring-4
                               focus:ring-blue-500/10 transition-all duration-200
                               {{ $errors->has('cantidadModoB') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}"
                    />
                    @error('cantidadModoB')
                        <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="text-xs text-slate-400 mt-1">Mínimo 1, máximo 500</p>
                </div>
            </div>

            <div class="mt-6">
                <button
                    wire:click="prepararInyeccion"
                    class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                           text-white font-semibold text-sm px-6 py-3 rounded-xl transition-all duration-200
                           hover:-translate-y-px hover:shadow-[0_4px_16px_rgba(37,99,235,.25)]
                           active:translate-y-0 active:shadow-none">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="8" cy="8" r="6"/>
                        <path d="M18.09 10.37A6 6 0 1 1 10.34 18"/>
                        <path d="M7 6h1v4"/>
                        <path d="m16.71 13.88.7.71-2.82 2.82"/>
                    </svg>
                    <span>Proceder a agregar</span>
                </button>
            </div>
        </div>

        {{-- Modal de Conf        {{-- Modal de Confirmación --}}
        <template x-teleport="body">
            <div
                x-show="mostrarConfirmacion"
                x-on:keyup.escape.window="mostrarConfirmacion = false"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0"
            >
                <!-- Backdrop blur -->
                <div
                    x-show="mostrarConfirmacion"
                    x-transition.opacity
                    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"
                    @click="mostrarConfirmacion = false"
                ></div>
    
                <!-- Modal panel -->
                <div
                    x-show="mostrarConfirmacion"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-2xl bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6 border border-slate-200"
                >
                    <div>
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-50">
                            <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 1 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.852l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-base font-semibold leading-6 text-slate-900">
                                Confirmar inyección de tokens
                            </h3>
                            
                            @if($loteSeleccionado)
                                <div class="mt-4 text-sm text-slate-600 text-left bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                                    <div><span class="font-semibold text-slate-700">Empresa:</span> {{ $loteSeleccionado->empresa->nombre }}</div>
                                    <div>
                                        <span class="font-semibold text-slate-700">Sucursal:</span>
                                        @if($loteSeleccionado->sucursal)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-violet-50 text-violet-700 ml-1">{{ $loteSeleccionado->sucursal->nombre }}</span>
                                        @else
                                            <span class="text-slate-400 italic">General (toda la empresa)</span>
                                        @endif
                                    </div>
                                    <div><span class="font-semibold text-slate-700">Lote destino:</span> {{ $loteSeleccionado->nombre ?? 'Lote sin nombre' }}</div>
                                    
                                    <div class="border-t border-slate-200 my-2 pt-2 flex justify-between">
                                        <span>Tokens actuales:</span>
                                        <span class="font-semibold text-slate-800">{{ $loteSeleccionado->tokens_total }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Tokens a agregar:</span>
                                        <span class="font-semibold text-blue-600">+{{ $cantidadModoB }}</span>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-200 pb-2 mb-2">
                                        <span>Total final:</span>
                                        <span class="font-semibold text-slate-900">{{ (int)$loteSeleccionado->tokens_total + (int)$cantidadModoB }}</span>
                                    </div>
    
                                    <div class="flex justify-between">
                                        <span>Fecha de finalización:</span>
                                        <span class="font-semibold text-slate-800">
                                            @if($nuevaFechaFin)
                                                {{ \Carbon\Carbon::parse($nuevaFechaFin)->format('d/m/Y') }} (Nueva)
                                            @else
                                                {{ $loteSeleccionado->fecha_fin ? $loteSeleccionado->fecha_fin->format('d/m/Y') : 'Sin registrar' }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
    
                                {{-- Alerta roja de irreversibilidad --}}
                                <div class="mt-4 flex items-start gap-2.5 p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-xs text-left">
                                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                    </svg>
                                    <span>
                                        <span class="font-semibold">Acción irreversible:</span> Una vez generados, estos tokens se agregarán al lote y estarán disponibles para responder encuestas.
                                    </span>
                                </div>
    
                                {{-- Si el lote expira en menos de 7 días y no se extendió: reiterar advertencia ámbar --}}
                                <div x-show="expiraPronto && !'{{ $nuevaFechaFin }}'" x-cloak class="mt-2 flex items-start gap-2.5 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-xs text-left">
                                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                    </svg>
                                    <span>
                                        <span class="font-semibold">Advertencia de expiración:</span> Este lote expirará pronto (en <span x-text="diasParaExpirar"></span> días) y no has extendido su vigencia.
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                        <button
                            type="button"
                            wire:click="inyectar"
                            wire:loading.attr="disabled"
                            wire:target="inyectar"
                            class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 sm:col-start-2 disabled:opacity-70 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="inyectar">Confirmar y Generar</span>
                            <span wire:loading wire:target="inyectar">Generando...</span>
                        </button>
                        <button
                            type="button"
                            @click="mostrarConfirmacion = false"
                            class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:col-start-1 sm:mt-0"
                        >
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
    @endif

    {{-- Historial de lotes --}}
    <div class="space-y-6 pt-4">
        <h2 class="text-lg font-semibold text-slate-900">Historial de lotes</h2>

        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Fecha</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Vigencia</th>
                            @if(auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value)
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Empresa</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Sucursal</th>
                            @endif
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Nombre del lote</th>
                            <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Cantidad</th>
                            <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Estado</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Generado por</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse($lotes as $lote)
                        <tr class="hover:bg-slate-50 transition-colors duration-100">
                            <td class="px-6 py-3 text-slate-500 text-xs whitespace-nowrap">
                                {{ $lote->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-3 text-slate-600 text-xs whitespace-nowrap">
                                @if($lote->fecha_inicio && $lote->fecha_fin)
                                    {{ \Carbon\Carbon::parse($lote->fecha_inicio)->format('d M Y') }} → {{ \Carbon\Carbon::parse($lote->fecha_fin)->format('d M Y') }}
                                @else
                                    <span class="text-slate-400 text-xs italic">Sin fecha registrada</span>
                                @endif
                            </td>
                            @if(auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value)
                                <td class="px-6 py-3 text-slate-700 whitespace-nowrap">
                                    {{ $lote->empresa->nombre }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    @if($lote->sucursal)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-violet-50 text-violet-700">
                                            {{ $lote->sucursal->nombre }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs">—</span>
                                    @endif
                                </td>
                            @endif
                            <td class="px-6 py-3 text-slate-700">
                                {{ $lote->nombre ?? '—' }}
                            </td>
                            <td class="px-6 py-3 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 whitespace-nowrap">
                                    {{ $lote->tokens_total }} {{ $lote->tokens_total == 1 ? 'token' : 'tokens' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center whitespace-nowrap">
                                @if(!$lote->activo)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Inactivo</span>
                                @elseif($lote->fecha_fin && $lote->fecha_fin->lt(today()))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">Expirado</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">Activo</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-slate-500 text-xs whitespace-nowrap">
                                {{ $lote->user?->name ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Aún no se han generado tokens.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
</div>
