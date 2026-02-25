<x-layouts.encuesta>
    <div class="max-w-md mx-auto mt-16 px-4">

        <h1 class="text-2xl font-semibold text-gray-800 mb-8">
            Bienvenido
        </h1>

        <form method="POST" action="{{ route('encuesta.acceso') }}" class="space-y-6">
            @csrf

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Código de acceso
                </label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                @error('password')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-8 py-3 rounded-xl transition-colors duration-200">
                Continuar →
            </button>
        </form>

    </div>
</x-layouts.encuesta>
