<div id="pregunta-{{ $pregunta->id }}"
    class="bg-white border rounded-2xl p-6 transition-colors duration-300 {{ $mostrarError ? 'border-red-400 bg-red-50/30' : 'border-slate-200' }}">

    {{-- Error de pregunta saltada --}}
    @if ($mostrarError)
        <div class="flex items-center gap-2 text-red-600 text-xs font-semibold mb-3">
            <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            Favor de seleccionar una opción.
        </div>
    @endif

    {{-- Texto de la pregunta --}}
    <p class="text-sm font-semibold {{ $mostrarError ? 'text-red-900' : 'text-slate-700' }} leading-relaxed mb-4">
        {{ $pregunta->texto }}
    </p>

    {{-- Opciones --}}
    <div class="space-y-2">
        @foreach ($opciones as $opcion)
            <button wire:click="seleccionar({{ $opcion->id }})" wire:loading.attr="disabled"
                wire:target="seleccionar({{ $opcion->id }})"
                class="w-full flex items-center gap-3 text-left px-4 py-3 rounded-xl border text-sm
                       transition-all duration-200
                       {{ $opcionSeleccionada === $opcion->id
                           ? 'border-blue-500 bg-blue-50 text-blue-800 font-medium'
                           : ($mostrarError
                               ? 'border-red-200 hover:border-red-300 hover:bg-red-50 text-slate-700'
                               : 'border-slate-200 hover:border-blue-300 hover:bg-slate-50 text-slate-700') }}">
                <span class="flex-shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center
                             {{ $opcionSeleccionada === $opcion->id ? 'border-blue-600 bg-blue-600' : 'border-slate-300' }}">
                    @if ($opcionSeleccionada === $opcion->id)
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5" />
                        </svg>
                    @endif
                </span>
                <span>{{ $opcion->opcion }}</span>
            </button>
        @endforeach
    </div>

</div>
