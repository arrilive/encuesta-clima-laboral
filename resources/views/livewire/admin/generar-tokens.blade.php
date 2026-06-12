<div class="space-y-6 max-w-7xl">

    {{-- Formulario --}}
    @if(auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value)
    <div class="bg-white rounded-2xl border border-slate-200 p-6 max-w-4xl">
        <h2 class="text-sm font-semibold text-slate-900 mb-5">Generar nuevos tokens</h2>

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

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            {{-- Empresa (solo super_admin) --}}
            @if(auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value)
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Empresa <span class="text-red-400">*</span></label>
                    <select
                        wire:model="empresaId"
                        class="w-full rounded-xl border px-4 py-2.5 text-sm
                               text-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10
                               focus:outline-none transition-all duration-200
                               {{ $errors->has('empresaId') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}">
                        <option value="">Selecciona una empresa</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" @disabled(!$empresa->activa)>
                                {{ $empresa->nombre }}{{ !$empresa->activa ? ' (Inactiva)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('empresaId')
                        <p class="flex items-center gap-1.5 mt-1.5 text-xs text-red-500">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
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
                    maxlength="100"
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
    @endif

    {{-- Historial de lotes --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Fecha</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Vigencia</th>
                        @if(auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value)
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Empresa</th>
                        @endif
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Nombre del lote</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Cantidad</th>
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
                            @endif
                            <td class="px-6 py-3 text-slate-700">
                                {{ $lote->nombre ?? '—' }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 whitespace-nowrap">
                                    {{ $lote->tokens_total }} {{ $lote->tokens_total == 1 ? 'token' : 'tokens' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-slate-500 text-xs whitespace-nowrap">
                                {{ $lote->user?->name ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Aún no se han generado tokens.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
