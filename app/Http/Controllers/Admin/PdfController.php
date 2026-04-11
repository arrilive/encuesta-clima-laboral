<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dimension;
use App\Models\Respuesta;
use App\Models\Subdimension;
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

        $promedioGeneral = count($datosDimensiones) > 0
            ? array_sum(array_column($datosDimensiones, 'puntaje')) / count($datosDimensiones)
            : 0;

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

    private function getBaseQuery(Request $request, $user)
    {
        $query = Respuesta::query()
            ->whereHas('encuesta', fn ($q) => $q->where('estado', 'completado'));

        if ($user->role === 'admin_empresa') {
            $query->whereHas('encuesta', fn ($q) => $q->where('empresa_id', $user->empresa_id));
        } elseif ($request->filled('empresa_id')) {
            $query->whereHas('encuesta', fn ($q) => $q->where('empresa_id', $request->empresa_id));
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

    private function getDatosDimensiones(Request $request, $user): array
    {
        $datos = [];
        $dimensiones = Dimension::orderBy('orden')->get();
        foreach ($dimensiones as $d) {
            $avg = $this->getBaseQuery($request, $user)
                ->whereHas('pregunta.subdimension', fn ($q) => $q->where('dimension_id', $d->id))
                ->whereHas('opcionRespuesta', fn ($q) => $q->where('valor_numerico', '!=', 0))
                ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
                ->avg('opciones_respuesta.valor_numerico');

            $datos[] = [
                'id' => $d->id,
                'nombre' => $d->nombre,
                'puntaje' => $avg !== null ? round((($avg - 1) / 2) * 100, 1) : 0.0,
            ];
        }

        return $datos;
    }

    private function getDatosSubdimensiones(Request $request, $user): array
    {
        $datos = [];
        $subdimensiones = Subdimension::with('dimension')->orderBy('dimension_id')->orderBy('orden')->get();
        foreach ($subdimensiones as $s) {
            $avg = $this->getBaseQuery($request, $user)
                ->whereHas('pregunta', fn ($q) => $q->where('subdimension_id', $s->id))
                ->whereHas('opcionRespuesta', fn ($q) => $q->where('valor_numerico', '!=', 0))
                ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
                ->avg('opciones_respuesta.valor_numerico');

            $datos[$s->dimension->nombre][] = [
                'nombre' => $s->nombre,
                'puntaje' => $avg !== null ? round((($avg - 1) / 2) * 100, 1) : 0.0,
            ];
        }

        return $datos;
    }

    private function getRespuestasAbiertas(Request $request, $user, int $limite): array
    {
        $respuestasAbiertas = [];
        $preguntasAbiertas = \App\Models\PreguntaAbierta::orderBy('orden')->get();

        $encuestasIds = \App\Models\Encuesta::where('estado', 'completado')
            ->when($user->role === 'admin_empresa', fn ($q) => $q->where('empresa_id', $user->empresa_id))
            ->when($user->role === 'super_admin' && $request->filled('empresa_id'), fn ($q) => $q->where('empresa_id', $request->empresa_id))
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
