<nav class="flex items-center justify-between" aria-label="Pagination">

    {{-- Texto "Mostrando X de Y" --}}
    <p class="text-xs text-slate-400">
        @if ($paginator->total() > 0)
            Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
        @else
            Mostrando 0 resultados
        @endif
    </p>

    @if ($paginator->hasPages())
        {{-- Botones --}}
        <div class="flex items-center gap-1">

            {{-- Anterior --}}
            @if ($paginator->onFirstPage())
                <span
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-300 cursor-not-allowed">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="w-4 h-4">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500
                          hover:bg-slate-100 hover:text-slate-900 transition-colors duration-150">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                </a>
            @endif

            {{-- Números de página --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs text-slate-400">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg
                                         bg-blue-600 text-white text-xs font-semibold">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg
                                      text-xs text-slate-600 hover:bg-slate-100 hover:text-slate-900
                                      transition-colors duration-150">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Siguiente --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500
                          hover:bg-slate-100 hover:text-slate-900 transition-colors duration-150">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </a>
            @else
                <span
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-300 cursor-not-allowed">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </span>
            @endif

        </div>
    @endif
</nav>
