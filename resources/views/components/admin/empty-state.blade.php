@props([
    'titulo' => 'Sin resultados',
    'mensaje' => 'No hay datos para los filtros seleccionados.',
    'conBotonFiltros' => true
])

<div class="bg-white rounded-2xl shadow-sm p-12 flex flex-col items-center justify-center text-center gap-4">
    <div class="p-4 bg-slate-50 rounded-2xl">
        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
        </svg>
    </div>
    <div>
        <h3 class="text-slate-900 font-semibold mb-1">{{ $titulo }}</h3>
        <p class="text-slate-500 text-sm">{{ $mensaje }}</p>
    </div>
    @if ($conBotonFiltros)
        <button wire:click="limpiarFiltros" class="mt-2 text-blue-600 hover:text-blue-700 text-sm font-semibold transition-colors">
            Limpiar filtros
        </button>
    @endif
</div>
