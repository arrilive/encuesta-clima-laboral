<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10 page-enter">

    {{-- Encabezado --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Cuéntanos sobre ti</h1>
        <p class="mt-1.5 text-sm text-slate-500 leading-relaxed">
            Estas preguntas nos ayudan a comprender mejor los resultados de la encuesta.
            Tus respuestas son <strong class="text-slate-700 font-medium">100% anónimas</strong> y se utilizarán
            únicamente con fines estadísticos.
        </p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">

            {{-- Edad --}}
            <div>
                <label for="edad_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Rango de edad
                </label>
                <select id="edad_id" wire:model.live="edad_id"
                    class="w-full rounded-xl border px-4 py-2.5 text-sm text-slate-800
                           shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none
                           transition-all duration-200
                           {{ $errors->has('edad_id') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}">
                    <option value="">— Selecciona —</option>
                    @foreach ($edades as $opcion)
                        <option value="{{ $opcion->id }}">{{ $opcion->opcion }}</option>
                    @endforeach
                </select>
                @error('edad_id')
                    <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Sexo --}}
            <div>
                <label for="sexo_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Sexo
                </label>
                <select id="sexo_id" wire:model.live="sexo_id"
                    class="w-full rounded-xl border px-4 py-2.5 text-sm text-slate-800
                           shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none
                           transition-all duration-200
                           {{ $errors->has('sexo_id') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}">
                    <option value="">— Selecciona —</option>
                    @foreach ($sexos as $opcion)
                        <option value="{{ $opcion->id }}">{{ $opcion->opcion }}</option>
                    @endforeach
                </select>
                @error('sexo_id')
                    <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Antigüedad --}}
            <div>
                <label for="antiguedad_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Antigüedad en la empresa
                </label>
                <select id="antiguedad_id" wire:model.live="antiguedad_id"
                    class="w-full rounded-xl border px-4 py-2.5 text-sm text-slate-800
                           shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none
                           transition-all duration-200
                           {{ $errors->has('antiguedad_id') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}">
                    <option value="">— Selecciona —</option>
                    @foreach ($antiguedades as $opcion)
                        <option value="{{ $opcion->id }}">{{ $opcion->opcion }}</option>
                    @endforeach
                </select>
                @error('antiguedad_id')
                    <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Lugar de trabajo --}}
            <div>
                <label for="lugar_trabajo_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Lugar de trabajo
                </label>
                <select id="lugar_trabajo_id" wire:model.live="lugar_trabajo_id"
                    class="w-full rounded-xl border px-4 py-2.5 text-sm text-slate-800
                           shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none
                           transition-all duration-200
                           {{ $errors->has('lugar_trabajo_id') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}">
                    <option value="">— Selecciona —</option>
                    @foreach ($lugaresTrabajo as $opcion)
                        <option value="{{ $opcion->id }}">{{ $opcion->opcion }}</option>
                    @endforeach
                </select>
                @error('lugar_trabajo_id')
                    <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Grado académico --}}
            <div>
                <label for="grado_academico_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Grado académico
                </label>
                <select id="grado_academico_id" wire:model.live="grado_academico_id"
                    class="w-full rounded-xl border px-4 py-2.5 text-sm text-slate-800
                           shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none
                           transition-all duration-200
                           {{ $errors->has('grado_academico_id') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}">
                    <option value="">— Selecciona —</option>
                    @foreach ($gradosAcademicos as $opcion)
                        <option value="{{ $opcion->id }}">{{ $opcion->opcion }}</option>
                    @endforeach
                </select>
                @error('grado_academico_id')
                    <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Cargo --}}
            <div>
                <label for="cargo_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Cargo o nivel jerárquico
                </label>
                <select id="cargo_id" wire:model.live="cargo_id"
                    class="w-full rounded-xl border px-4 py-2.5 text-sm text-slate-800
                           shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none
                           transition-all duration-200
                           {{ $errors->has('cargo_id') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}">
                    <option value="">— Selecciona —</option>
                    @foreach ($cargos as $opcion)
                        <option value="{{ $opcion->id }}">{{ $opcion->opcion }}</option>
                    @endforeach
                </select>
                @error('cargo_id')
                    <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Botón continuar --}}
        <div class="mt-6 pt-5 flex justify-end border-t border-slate-100">
            <button wire:click="continuar" wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700
                    px-6 py-2.5 text-sm font-semibold text-white shadow-sm
                    focus:outline-none focus:ring-4 focus:ring-blue-500/20
                    disabled:opacity-50 disabled:cursor-not-allowed
                    transition-all duration-200 hover:-translate-y-px
                    hover:shadow-[0_4px_12px_rgba(37,99,235,.25)]
                    active:translate-y-0">
                <svg wire:loading wire:target="continuar" class="animate-spin flex-shrink-0" width="14"
                    height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                    <path d="M12 2a10 10 0 0 1 10 10" />
                </svg>
                <span wire:loading.remove wire:target="continuar">Continuar</span>
                <span wire:loading wire:target="continuar">Guardando…</span>
            </button>
        </div>

    </div>

</div>
