@props(['puntaje', 'variant' => 'standard'])

@php
    $map = \App\Support\ClimaBadge::resolver($puntaje);
    $baseClasses = $variant === 'compact'
        ? 'px-2.5 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider'
        : 'px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider';
@endphp

<span class="{{ $baseClasses }} {{ $map[$variant] }}">{{ $map['label'] }}</span>

{{--
Tailwind classes safelist for ClimaBadge:
bg-emerald-50 text-emerald-600 bg-emerald-100 text-emerald-700
bg-blue-50 text-blue-600 bg-blue-100 text-blue-700
bg-amber-50 text-amber-600 bg-amber-100 text-amber-700
bg-red-50 text-red-600 bg-red-100 text-red-700
--}}
