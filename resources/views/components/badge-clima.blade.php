@props(['puntaje', 'variant' => 'standard'])

@php
    $map = match(true) {
        $puntaje >= 80 => ['label' => 'Excelente',  'compact' => 'bg-emerald-50 text-emerald-600', 'standard' => 'bg-emerald-100 text-emerald-700'],
        $puntaje >= 51 => ['label' => 'Buen clima', 'compact' => 'bg-blue-50 text-blue-600',       'standard' => 'bg-blue-100 text-blue-700'],
        $puntaje >= 25 => ['label' => 'Regular',    'compact' => 'bg-amber-50 text-amber-600',     'standard' => 'bg-amber-100 text-amber-700'],
        default        => ['label' => 'Deficiente', 'compact' => 'bg-red-50 text-red-600',         'standard' => 'bg-red-100 text-red-700'],
    };
    $baseClasses = $variant === 'compact'
        ? 'px-2.5 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider'
        : 'px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider';
@endphp

<span class="{{ $baseClasses }} {{ $map[$variant] }}">{{ $map['label'] }}</span>
