<x-layouts.encuesta>
    <div class="max-w-md mx-auto mt-16 px-4 text-center">

        <h1 class="text-2xl font-semibold text-gray-800 mb-8">
            Tu código de acceso
        </h1>

        <div class="bg-white rounded-2xl shadow-md px-8 py-10 mb-8">
            <p class="text-7xl font-bold tracking-widest text-indigo-600 select-all">
                {{ $encuesta->token }}
            </p>
        </div>

        <p class="text-gray-500 text-sm mb-10">
            Guarda este código. Te permitirá continuar desde cualquier dispositivo.
        </p>

        <a href="{{ route('encuesta.demograficos', $encuesta->token) }}"
            class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-8 py-3 rounded-xl transition-colors duration-200">
            Continuar →
        </a>

    </div>
</x-layouts.encuesta>
