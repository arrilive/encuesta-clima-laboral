<x-layouts.admin title="Dashboard" heading="Dashboard">

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Total tokens</p>
            <p class="text-3xl font-bold text-slate-900">{{ $kpis['total_tokens'] }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Completadas</p>
            <p class="text-3xl font-bold text-emerald-600">{{ $kpis['completadas'] }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">En progreso</p>
            <p class="text-3xl font-bold text-blue-600">{{ $kpis['en_progreso'] }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Disponibles</p>
            <p class="text-3xl font-bold text-slate-500">{{ $kpis['disponibles'] }}</p>
        </div>

    </div>

</x-layouts.admin>