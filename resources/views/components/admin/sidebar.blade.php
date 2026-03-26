<aside class="flex flex-col w-64 h-screen max-h-screen bg-white border-r border-slate-200 flex-shrink-0 sticky top-0">

    {{-- Header: usuario y rol --}}
    <div class="px-6 py-5 border-b border-slate-200">
        <p class="font-semibold text-slate-900 text-sm truncate">{{ auth()->user()->name }}</p>
        <p class="text-xs text-slate-400 mt-0.5 truncate">{{ auth()->user()->email }}</p>
        <span class="inline-block mt-2 text-xs font-medium bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">
            {{ auth()->user()->role === 'super_admin' ? 'Super Admin' : 'Admin Empresa' }}
        </span>
    </div>

    {{-- Navegación --}}
    <nav class="flex-1 px-3 py-4 space-y-0.5">

        <x-admin.sidebar-item
            route="admin.dashboard"
            label="Dashboard"
            icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>'
        />

        <x-admin.sidebar-item
            route="admin.encuestas"
            label="Encuestas"
            icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>'
        />

        <x-admin.sidebar-item
            route="admin.tokens"
            label="Tokens"
            icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>'
        />

        <x-admin.sidebar-item
            route="admin.reportes"
            label="Reportes"
            icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>'
        />

        {{-- Issues futuros — rutas aún no definidas
        @if(auth()->user()->role === 'super_admin')
            <x-admin.sidebar-item route="admin.empresas" label="Empresas" ... />
        @endif
        --}}

    </nav>

   {{-- Logout --}}
    <div class="px-3 py-4 border-t border-slate-200">
        <livewire:admin.logout-button />
    </div>

</aside>
