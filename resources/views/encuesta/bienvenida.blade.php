<x-layouts.encuesta>
    <div class="flex items-center justify-center px-4 py-14 min-h-[calc(100vh-5rem)]">
        <div class="w-full max-w-sm page-enter">


            {{-- Título --}}
            <h1 class="text-[1.75rem] font-bold text-slate-900 text-center tracking-tight leading-tight mb-3">
                Hola, queremos escucharte
            </h1>

            {{-- Subtítulo --}}
            <p class="text-sm text-slate-500 text-center leading-relaxed mb-8">
                Tu opinión es el primer paso para construir un mejor lugar de trabajo.
                Esta encuesta es <strong class="text-slate-700 font-semibold">completamente anónima</strong>,
                nadie podrá saber quién dijo qué.
            </p>

            {{-- Card con formulario --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <form method="POST" action="{{ route('encuesta.acceso') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Contraseña de acceso
                        </label>
                        <input type="password" id="password" name="password" placeholder="La recibiste en tu correo"
                            required
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900
                                   placeholder-slate-400 bg-white
                                   focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10
                                   transition-all duration-200">
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                               text-white font-semibold text-sm px-6 py-3 rounded-xl
                               transition-all duration-200 hover:-translate-y-px
                               hover:shadow-[0_4px_16px_rgba(37,99,235,.25)]
                               active:translate-y-0 active:shadow-none">
                        Ingresar
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.encuesta>
