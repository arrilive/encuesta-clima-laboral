<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Encuesta;
use App\Traits\HasTenantScope;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    use HasTenantScope;

    public function encuestasCSV(Request $request): StreamedResponse
    {
        $user = auth()->user();

        $encuestas = Encuesta::with('lote.empresa')
            ->whereHas('lote', fn ($q) => $this->scopeByRole($q))
            ->when($request->estado, fn ($q) => $q->where('estado', $request->estado))
            ->when($request->buscar, fn ($q) => $q->where('token', 'like', '%'.$request->buscar.'%'))
            ->when(in_array($user->role, [
                \App\Enums\Role::SUPER_ADMIN->value,
                \App\Enums\Role::ADMIN_CORPORATIVO->value,
            ]) && $request->empresa, fn ($q) => $q->whereHas('lote', fn ($q2) => $q2->where('empresa_id', $request->empresa)))
            ->when($request->desde, fn ($q) => $q->whereDate('fecha_asignacion', '>=', $request->desde))
            ->when($request->hasta, fn ($q) => $q->whereDate('fecha_asignacion', '<=', $request->hasta))
            ->orderByDesc('fecha_asignacion')
            ->get();

        $filename = 'encuestas_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($encuestas, $user) {
            $handle = fopen('php://output', 'w');

            $headers = ['Token (primeros 16 chars)', 'Estado', 'Asignado', 'Completado'];
            if (in_array($user->role, [
                \App\Enums\Role::SUPER_ADMIN->value,
                \App\Enums\Role::ADMIN_CORPORATIVO->value,
            ])) {
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
                if (in_array($user->role, [
                    \App\Enums\Role::SUPER_ADMIN->value,
                    \App\Enums\Role::ADMIN_CORPORATIVO->value,
                ])) {
                    array_splice($row, 1, 0, [$encuesta->lote?->empresa?->nombre ?? 'Sin Lote']);
                }
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
