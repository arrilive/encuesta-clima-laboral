<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Encuesta;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $base = Encuesta::when(
            $user->role === 'admin_empresa',
            fn($q) => $q->where('empresa_id', $user->empresa_id)
        );

        $kpis = [
            'total_tokens' => (clone $base)->count(),
            'completadas'  => (clone $base)->where('estado', 'completado')->count(),
            'en_progreso'  => (clone $base)->where('estado', 'en_progreso')->count(),
            'disponibles'  => (clone $base)->where('estado', 'disponible')->count(),
        ];

        return view('admin.dashboard', compact('kpis'));
    }
}