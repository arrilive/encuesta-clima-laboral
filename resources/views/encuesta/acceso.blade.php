<x-layouts.encuesta>
    <div class="flex items-center justify-center px-4 py-14 min-h-[calc(100vh-5rem)]">
        <div class="w-full max-w-sm page-enter">


            {{-- Título --}}
            <h1 class="text-[1.7rem] font-bold text-slate-900 text-center tracking-tight leading-tight mb-2">
                ¡Bienvenido!
            </h1>

            {{-- Subtítulo --}}
            <p class="text-sm text-slate-500 text-center leading-relaxed mb-8">
                Genera tu código personal para iniciar la encuesta.
                Este código te permitirá retomarla si se interrumpe o si usas otro dispositivo.
            </p>

            {{-- Mensaje de sesión (error genérico) --}}
            @if (session('error'))
                <div class="mb-4 text-sm text-red-500 text-center">{{ session('error') }}</div>
            @endif

            {{-- Opción principal: Generar código --}}
            <form method="POST" action="{{ route('encuesta.generar') }}" x-data="{ loading: false }"
                x-on:submit="loading = true">
                @csrf
                <button type="submit" :disabled="loading"
                    class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                           text-white font-semibold text-sm px-6 py-3.5 rounded-xl
                           transition-all duration-200 hover:-translate-y-px
                           hover:shadow-[0_4px_16px_rgba(37,99,235,.25)]
                           active:translate-y-0 active:shadow-none
                           disabled:opacity-75 disabled:cursor-not-allowed">
                    <svg x-show="loading" class="animate-spin flex-shrink-0" width="14" height="14"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round">
                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                        <path d="M12 2a10 10 0 0 1 10 10" />
                    </svg>
                    <span x-text="loading ? 'Generando…' : 'Generar mi código'"></span>
                </button>
                @error('generar')
                    <div class="flex items-start sm:items-center mt-4 p-4 text-sm text-amber-800 rounded-lg bg-amber-50 border border-amber-200"
                        role="alert">
                        <svg class="w-4 h-4 mr-2 shrink-0 mt-0.5 sm:mt-0" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <p><span class="font-medium mr-1">¡Atención!</span> {{ $message }}</p>
                    </div>
                @enderror
            </form>

            {{-- Separador --}}
            <div class="flex items-center gap-3 my-5">
                <div class="flex-1 h-px bg-slate-200"></div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">o bien</span>
                <div class="flex-1 h-px bg-slate-200"></div>
            </div>

            {{-- Sección secundaria: Retomar con código existente --}}
            <p class="text-xs text-slate-400 text-center mb-3 leading-relaxed">
                Si ya iniciaste la encuesta antes, usa tu código para retomar donde te quedaste.
            </p>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                <form method="POST" action="{{ route('encuesta.reanudar') }}" class="space-y-2.5">
                    @csrf
                    <input type="text" name="token" placeholder="Pega tu código aquí…"
                        class="w-full px-3 py-2.5 border border-slate-300 rounded-lg font-mono text-xs
                               text-slate-800 placeholder-slate-400 bg-white
                               focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10
                               transition-all duration-200
                               @error('token') border-red-400 bg-red-50 @enderror">
                    @error('token')
                        <p class="flex items-center gap-1.5 text-xs text-red-500">
                            {{ $message }}
                        </p>
                    @enderror
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-1.5 bg-white border border-slate-300
                               hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700
                               text-slate-600 font-medium text-xs px-4 py-2.5 rounded-lg
                               transition-all duration-200 active:bg-blue-100">
                        Retomar mi encuesta
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-layouts.encuesta>
