<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Encabezado --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Datos generales</h1>
        <p class="mt-1 text-sm text-gray-500">
            Estos datos se analizan de forma agregada y garantizan tu anonimato.
        </p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">

        {{-- Edad --}}
        <div>
            <label for="edad_id" class="block text-sm font-medium text-gray-700 mb-1">
                Rango de edad
            </label>
            <select id="edad_id" wire:model.live="edad_id"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">— Selecciona —</option>
                @foreach ($edades as $opcion)
                    <option value="{{ $opcion->id }}">{{ $opcion->opcion }}</option>
                @endforeach
            </select>
            @error('edad_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Sexo --}}
        <div>
            <label for="sexo_id" class="block text-sm font-medium text-gray-700 mb-1">
                Sexo
            </label>
            <select id="sexo_id" wire:model.live="sexo_id"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">— Selecciona —</option>
                @foreach ($sexos as $opcion)
                    <option value="{{ $opcion->id }}">{{ $opcion->opcion }}</option>
                @endforeach
            </select>
            @error('sexo_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Antigüedad --}}
        <div>
            <label for="antiguedad_id" class="block text-sm font-medium text-gray-700 mb-1">
                Antigüedad en la empresa
            </label>
            <select id="antiguedad_id" wire:model.live="antiguedad_id"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">— Selecciona —</option>
                @foreach ($antiguedades as $opcion)
                    <option value="{{ $opcion->id }}">{{ $opcion->opcion }}</option>
                @endforeach
            </select>
            @error('antiguedad_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Lugar de trabajo --}}
        <div>
            <label for="lugar_trabajo_id" class="block text-sm font-medium text-gray-700 mb-1">
                Lugar de trabajo
            </label>
            <select id="lugar_trabajo_id" wire:model.live="lugar_trabajo_id"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">— Selecciona —</option>
                @foreach ($lugaresTrabajo as $opcion)
                    <option value="{{ $opcion->id }}">{{ $opcion->opcion }}</option>
                @endforeach
            </select>
            @error('lugar_trabajo_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Grado académico --}}
        <div>
            <label for="grado_academico_id" class="block text-sm font-medium text-gray-700 mb-1">
                Grado académico
            </label>
            <select id="grado_academico_id" wire:model.live="grado_academico_id"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">— Selecciona —</option>
                @foreach ($gradosAcademicos as $opcion)
                    <option value="{{ $opcion->id }}">{{ $opcion->opcion }}</option>
                @endforeach
            </select>
            @error('grado_academico_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Cargo --}}
        <div>
            <label for="cargo_id" class="block text-sm font-medium text-gray-700 mb-1">
                Cargo o nivel jerárquico
            </label>
            <select id="cargo_id" wire:model.live="cargo_id"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">— Selecciona —</option>
                @foreach ($cargos as $opcion)
                    <option value="{{ $opcion->id }}">{{ $opcion->opcion }}</option>
                @endforeach
            </select>
            @error('cargo_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Pie: anonimato + botón --}}
        <div class="pt-4 flex items-center justify-between border-t border-gray-100">
            <p class="text-xs text-gray-400">
                Estos datos se analizan de forma agregada y nunca de manera individual.
            </p>
            <button wire:click="continuar" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition">
                <span wire:loading.remove wire:target="continuar">Continuar →</span>
                <span wire:loading wire:target="continuar">Guardando…</span>
            </button>
        </div>

    </div>
</div>
