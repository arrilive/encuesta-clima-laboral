<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Encuesta;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function encuestasCSV(Request $request): StreamedResponse
    {
        $user = auth()->user();

        $encuestas = Encuesta::with('empresa')
            ->when($user->role === 'admin_empresa', fn ($q) => $q->where('empresa_id', $user->empresa_id)
            )
            ->when($request->estado, fn ($q) => $q->where('estado', $request->estado)
            )
            ->when($request->buscar, fn ($q) => $q->where('token', 'like', '%'.$request->buscar.'%')
            )
            ->when($request->empresa, fn ($q) => $q->where('empresa_id', $request->empresa)
            )
            ->when($request->desde, fn ($q) => $q->whereDate('fecha_asignacion', '>=', $request->desde)
            )
            ->when($request->hasta, fn ($q) => $q->whereDate('fecha_asignacion', '<=', $request->hasta)
            )
            ->orderByDesc('fecha_asignacion')
            ->get();

        $filename = 'encuestas_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($encuestas, $user) {
            $handle = fopen('php://output', 'w');

            $headers = ['Token (primeros 16 chars)', 'Estado', 'Asignado', 'Completado'];
            if ($user->role === 'super_admin') {
                array_splice($headers, 1, 0, ['Empresa']);
            }
            fputcsv($handle, $headers);

            foreach ($encuestas as $encuesta) {
                $row = [
                    substr($encuesta->token, 0, 16),
                    $encuesta->estado,
                    $encuesta->fecha_asignacion?->format('d/m/Y H:i') ?? '',
                    $encuesta->fecha_completada?->format('d/m/Y H:i') ?? '',
                ];
                if ($user->role === 'super_admin') {
                    array_splice($row, 1, 0, [$encuesta->empresa->nombre]);
                }
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
