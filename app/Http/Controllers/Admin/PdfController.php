<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Respuesta;
use App\Models\Subdimension;
use App\Services\ClimaScoringService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function reportePDF(Request $request)
    {
        set_time_limit(120);

        $alcance = $request->input('alcance', 'dimensiones');
        $limite = (int) $request->input('limite', 25);
        $user = auth()->user();

        $filtrosActivos = $this->getFiltrosActivos($request);
        $datosDimensiones = $this->getDatosDimensiones($request, $user);

        $datosSubdimensiones = [];
        if (in_array($alcance, ['subdimensiones', 'completo'])) {
            $datosSubdimensiones = $this->getDatosSubdimensiones($request, $user);
        }

        $respuestasAbiertas = [];
        if (in_array($alcance, ['respuestas_abiertas', 'completo'])) {
            $respuestasAbiertas = $this->getRespuestasAbiertas($request, $user, $limite);
        }

        $scoring = app(ClimaScoringService::class);
        $promedioGeneral = $scoring->promedioGeneral($this->getBaseQuery($request, $user));

        $completadas = $this->getBaseQuery($request, $user)->distinct('encuesta_id')->count('encuesta_id');
        $svgs = session('pdf_svgs', []);

        $pdf = Pdf::loadView('admin.pdf.reporte', compact(
            'alcance',
            'datosDimensiones',
            'datosSubdimensiones',
            'respuestasAbiertas',
            'svgs',
            'filtrosActivos',
            'promedioGeneral',
            'completadas'
        ));

        return $pdf->stream('reporte-clima.pdf');
    }

    private function getBaseQuery(Request $request, \App\Models\User $user)
    {
        $query = Respuesta::query()
            ->whereHas('encuesta', fn ($q) => $q->where('estado', 'completado'));

        if ($user->role === 'admin_empresa') {
            $query->whereHas('encuesta.lote', fn ($q) => $q->where('empresa_id', $user->empresa_id));
        } elseif ($request->filled('empresa_id')) {
            $query->whereHas('encuesta.lote', fn ($q) => $q->where('empresa_id', $request->empresa_id));
        }

        $filtros = [
            'edad_id' => 'edad_id',
            'sexo_id' => 'sexo_id',
            'cargo_id' => 'cargo_id',
            'lugar_trabajo_id' => 'lugar_trabajo_id',
            'grado_academico_id' => 'grado_academico_id',
            'antiguedad_id' => 'antiguedad_id',
        ];

        foreach ($filtros as $param => $column) {
            if ($request->filled($param)) {
                $query->whereHas('encuesta.datoDemografico', fn ($q) => $q->where($column, $request->$param));
            }
        }

        return $query;
    }

    private function getFiltrosActivos(Request $request): array
    {
        $filtros = [];
        $map = [
            'empresa_id' => ['label' => 'Empresa', 'model' => \App\Models\Empresa::class, 'attr' => 'nombre'],
            'edad_id' => ['label' => 'Edad', 'model' => \App\Models\Edad::class, 'attr' => 'opcion'],
            'sexo_id' => ['label' => 'Sexo', 'model' => \App\Models\Sexo::class, 'attr' => 'opcion'],
            'cargo_id' => ['label' => 'Cargo', 'model' => \App\Models\Cargo::class, 'attr' => 'opcion'],
            'lugar_trabajo_id' => ['label' => 'Lugar de Trabajo', 'model' => \App\Models\LugarTrabajo::class, 'attr' => 'opcion'],
            'grado_academico_id' => ['label' => 'Grado Académico', 'model' => \App\Models\GradoAcademico::class, 'attr' => 'opcion'],
            'antiguedad_id' => ['label' => 'Antigüedad', 'model' => \App\Models\Antiguedad::class, 'attr' => 'opcion'],
        ];

        foreach ($map as $key => $config) {
            if ($request->filled($key)) {
                $item = $config['model']::find($request->$key);
                if ($item) {
                    $filtros[$config['label']] = $item->{$config['attr']};
                }
            }
        }

        return $filtros;
    }

    private function getDatosDimensiones(Request $request, \App\Models\User $user): array
    {
        $scoring = app(ClimaScoringService::class);

        return $scoring->scoresPorDimension($this->getBaseQuery($request, $user))->toArray();
    }

    private function getDatosSubdimensiones(Request $request, \App\Models\User $user): array
    {
        $scoring = app(ClimaScoringService::class);
        $scores = $scoring->scoresPorSubdimension($this->getBaseQuery($request, $user));

        $datos = [];
        $subdimensiones = Subdimension::with('dimension')->orderBy('dimension_id')->orderBy('orden')->get();

        foreach ($subdimensiones as $s) {
            $score = $scores->firstWhere('id', $s->id);
            $datos[$s->dimension->nombre][] = [
                'nombre' => $s->nombre,
                'puntaje' => $score['puntaje'] ?? 0.0,
            ];
        }

        return $datos;
    }

    private function getRespuestasAbiertas(Request $request, \App\Models\User $user, int $limite): array
    {
        $respuestasAbiertas = [];
        $preguntasAbiertas = \App\Models\PreguntaAbierta::orderBy('orden')->get();

        $encuestasIds = \App\Models\Encuesta::where('estado', 'completado')
            ->when($user->role === 'admin_empresa', fn ($q) => $q->whereHas('lote', fn ($q2) => $q2->where('empresa_id', $user->empresa_id)))
            ->when($user->role === 'super_admin' && $request->filled('empresa_id'), fn ($q) => $q->whereHas('lote', fn ($q2) => $q2->where('empresa_id', $request->empresa_id)))
            ->when($request->filled('edad_id'), fn ($q) => $q->whereHas('datoDemografico', fn ($q2) => $q2->where('edad_id', $request->edad_id)))
            ->when($request->filled('sexo_id'), fn ($q) => $q->whereHas('datoDemografico', fn ($q2) => $q2->where('sexo_id', $request->sexo_id)))
            ->when($request->filled('cargo_id'), fn ($q) => $q->whereHas('datoDemografico', fn ($q2) => $q2->where('cargo_id', $request->cargo_id)))
            ->when($request->filled('lugar_trabajo_id'), fn ($q) => $q->whereHas('datoDemografico', fn ($q2) => $q2->where('lugar_trabajo_id', $request->lugar_trabajo_id)))
            ->when($request->filled('grado_academico_id'), fn ($q) => $q->whereHas('datoDemografico', fn ($q2) => $q2->where('grado_academico_id', $request->grado_academico_id)))
            ->when($request->filled('antiguedad_id'), fn ($q) => $q->whereHas('datoDemografico', fn ($q2) => $q2->where('antiguedad_id', $request->antiguedad_id)))
            ->pluck('id');

        foreach ($preguntasAbiertas as $p) {
            $respuestas = \App\Models\RespuestaAbierta::where('pregunta_abierta_id', $p->id)
                ->whereIn('encuesta_id', $encuestasIds)
                ->whereNotNull('texto')
                ->where('texto', '!=', '')
                ->orderBy('id', 'desc')
                ->take($limite)
                ->pluck('texto')
                ->toArray();

            $respuestasAbiertas[] = [
                'pregunta' => $p->texto,
                'respuestas' => $respuestas,
            ];
        }

        return $respuestasAbiertas;
    }
}
