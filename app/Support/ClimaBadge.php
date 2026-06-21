<?php

namespace App\Support;

class ClimaBadge
{
    public static function resolver(?float $puntaje): array
    {
        return match (true) {
            $puntaje >= 75 => [
                'label' => 'Excelente',
                'compact' => 'bg-emerald-50 text-emerald-600',
                'standard' => 'bg-emerald-100 text-emerald-700',
                'pdf_class' => 'badge-excelente',
                'color_hex' => '#10b981',
            ],
            $puntaje >= 60 => [
                'label' => 'Buen clima',
                'compact' => 'bg-blue-50 text-blue-600',
                'standard' => 'bg-blue-100 text-blue-700',
                'pdf_class' => 'badge-bueno',
                'color_hex' => '#3b82f6',
            ],
            $puntaje >= 45 => [
                'label' => 'En atención',
                'compact' => 'bg-amber-50 text-amber-600',
                'standard' => 'bg-amber-100 text-amber-700',
                'pdf_class' => 'badge-atencion',
                'color_hex' => '#f59e0b',
            ],
            default => [
                'label' => 'En riesgo',
                'compact' => 'bg-red-50 text-red-600',
                'standard' => 'bg-red-100 text-red-700',
                'pdf_class' => 'badge-riesgo',
                'color_hex' => '#ef4444',
            ],
        };
    }
}
