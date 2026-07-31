<aside class="flex flex-col w-64 h-screen max-h-screen bg-white border-r border-slate-200 flex-shrink-0 fixed inset-y-0 left-0 z-40 transition-transform duration-200 md:translate-x-0 md:static md:sticky md:top-0" :class="sidebarAbierto ? 'translate-x-0' : '-translate-x-full'" x-on:keyup.escape.window="sidebarAbierto = false">

    {{-- Header: usuario y rol --}}
    <div class="px-6 py-5 border-b border-slate-200 relative">
        <button @click="sidebarAbierto = false" aria-label="Cerrar menú de navegación" class="md:hidden absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
        <p class="font-semibold text-slate-900 text-sm truncate">{{ auth()->user()->name }}</p>
        <p class="text-xs text-slate-400 mt-0.5 truncate">{{ auth()->user()->email }}</p>
        <span class="inline-block mt-2 text-xs font-medium px-2 py-0.5 rounded-full {{ match(auth()->user()->role) {
            'super_admin'       => 'bg-slate-800 text-white',
            'admin_corporativo' => 'bg-indigo-50 text-indigo-700',
            'admin_empresa'     => 'bg-blue-50 text-blue-700',
            'admin_sucursal'    => 'bg-violet-50 text-violet-700',
            default             => 'bg-blue-50 text-blue-700',
        } }}">
            {{ match(auth()->user()->role) {
                'super_admin'       => 'Super Admin',
                'admin_corporativo' => 'Admin Corporativo',
                'admin_empresa'     => 'Admin Empresa',
                'admin_sucursal'    => 'Admin Sucursal',
                default             => 'Admin',
            } }}
        </span>
    </div>

    {{-- Navegación --}}
    <nav aria-label="Navegación principal" class="flex-1 px-3 py-4 space-y-0.5">

        <x-admin.sidebar-item
            route="admin.dashboard"
            label="Dashboard"
            icon='<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>'
        />

        <x-admin.sidebar-item
            route="admin.encuestas"
            label="Encuestas"
            icon='<path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/>'
        />

        <x-admin.sidebar-item
            route="admin.tokens"
            label="Tokens"
            icon='<circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/>'
        />

        <x-admin.sidebar-item
            route="admin.reportes"
            label="Reportes"
            icon='<path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/>'
        />

        <x-admin.sidebar-item
            route="admin.tendencias"
            label="Tendencias"
            icon='<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>'
        />

        @if(auth()->user()->role === 'super_admin')
            <x-admin.sidebar-item
                route="admin.corporativos"
                label="Corporativos"
                icon='<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />'
            />
            <x-admin.sidebar-item
                route="admin.empresas"
                label="Empresas"
                icon='<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />'
            />
            <x-admin.sidebar-item
                route="admin.administradores"
                label="Administradores"
                icon='<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'
            />
        @endif

    </nav>

   {{-- Logout --}}
    <div class="px-3 py-4 border-t border-slate-200">
        <livewire:admin.logout-button />
    </div>

</aside>
