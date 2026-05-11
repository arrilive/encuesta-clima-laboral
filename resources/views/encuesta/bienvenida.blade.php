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

            {{-- TODO #129: Modal Alpine.js con flujo OTP --}}
            {{-- El botón "Comenzar" abrirá el modal de ingreso de número de teléfono --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6 text-center">
                <p class="text-sm text-slate-500">
                    El acceso mediante verificación de número estará disponible en breve.
                </p>
            </div>

        </div>
    </div>
</x-layouts.encuesta>
