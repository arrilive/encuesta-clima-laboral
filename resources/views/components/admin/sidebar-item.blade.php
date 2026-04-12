@props(['route', 'label', 'icon'])

@php $active = request()->routeIs($route); @endphp

<a href="{{ route($route) }}"
   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150
          {{ $active
             ? 'bg-blue-50 text-blue-700'
             : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">

    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
         class="w-4 h-4 flex-shrink-0 {{ $active ? 'text-blue-600' : 'text-slate-400' }}">
        {!! $icon !!}
    </svg>

    {{ $label }}
</a>
