<nav class="flex-1 px-3 py-4 space-y-0.5">

    <x-admin.sidebar-item
        route="admin.dashboard"
        label="Dashboard"
        icon='<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>'
    />

    {{-- Issues futuros — rutas aún no definidas
    <x-admin.sidebar-item route="admin.encuestas" label="Encuestas" ... />
    <x-admin.sidebar-item route="admin.tokens" label="Tokens" ... />
    @if(auth()->user()->role === 'super_admin')
        <x-admin.sidebar-item route="admin.empresas" label="Empresas" ... />
    @endif
    --}}

</nav>