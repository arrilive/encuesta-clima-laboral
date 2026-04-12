<?php

namespace App\Services;

use App\Models\Dimension;
use App\Models\Subdimension;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ClimaScoringService
{
    /**
     * Calcula el puntaje (0–100) para cada dimensión sobre el conjunto
     * de respuestas representado por $baseQuery.
     *
     * El consumidor construye y scope-a la query base (por rol, por filtros
     * demográficos, etc.). El servicio no la modifica: clona internamente
     * antes de agregar constraints propios.
     *
     * @return Collection<array{id: int, nombre: string, puntaje: float}>
     */
    public function scoresPorDimension(Builder $baseQuery): Collection
    {
        $dimensiones = Dimension::with('subdimensiones')->orderBy('orden')->get();

        $promediosSub = (clone $baseQuery)
            ->whereHas('opcionRespuesta', fn (Builder $q) => $q->where('valor_numerico', '!=', 0))
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->join('preguntas', 'respuestas.pregunta_id', '=', 'preguntas.id')
            ->selectRaw('preguntas.subdimension_id, AVG(opciones_respuesta.valor_numerico) as promedio')
            ->groupBy('preguntas.subdimension_id')
            ->get()
            ->keyBy('subdimension_id');

        return $dimensiones->map(function (Dimension $d) use ($promediosSub) {
            $puntajesSub = $d->subdimensiones->map(function ($sub) use ($promediosSub) {
                if (isset($promediosSub[$sub->id])) {
                    $avg = $promediosSub[$sub->id]->promedio;

                    return (($avg - 1) / 2) * 100;
                }

                return null;
            })->filter(fn ($p) => $p !== null);

            $puntajeDimension = $puntajesSub->count() > 0
                ? round($puntajesSub->average(), 1)
                : null;

            return [
                'id' => $d->id,
                'nombre' => $d->nombre,
                'puntaje' => $puntajeDimension,
            ];
        })->values();
    }

    /**
     * Calcula el puntaje (0–100) para cada subdimensión sobre el conjunto
     * de respuestas representado por $baseQuery.
     *
     * @return Collection<array{id: int, nombre: string, puntaje: float}>
     */
    public function scoresPorSubdimension(Builder $baseQuery): Collection
    {
        $subdimensiones = Subdimension::orderBy('orden')->get();

        $promedios = (clone $baseQuery)
            ->whereHas('opcionRespuesta', fn (Builder $q) => $q->where('valor_numerico', '!=', 0)
            )
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->join('preguntas', 'respuestas.pregunta_id', '=', 'preguntas.id')
            ->selectRaw('preguntas.subdimension_id, AVG(opciones_respuesta.valor_numerico) as promedio')
            ->groupBy('preguntas.subdimension_id')
            ->get()
            ->keyBy('subdimension_id');

        return $subdimensiones->map(fn (Subdimension $s) => [
            'id' => $s->id,
            'nombre' => $s->nombre,
            'puntaje' => isset($promedios[$s->id])
                ? round((($promedios[$s->id]->promedio - 1) / 2) * 100, 1)
                : 0.0,
        ])->values();
    }

    /**
     * Calcula el promedio general de clima (0–100) sobre todas las
     * dimensiones juntas, excluyendo respuestas con valor_numerico = 0.
     *
     * Retorna 0.0 cuando no hay respuestas con valor numérico válido.
     */
    public function promedioGeneral(Builder $baseQuery): float
    {
        $scores = $this->scoresPorDimension($baseQuery);
        $dimensionesValidas = $scores->whereNotNull('puntaje');

        if ($dimensionesValidas->isEmpty()) {
            return 0.0;
        }

        return round($dimensionesValidas->avg('puntaje'), 1);
    }

    /**
     * Calcula los puntajes por dimensión agrupados por un campo demográfico.
     * Útil para gráficos comparativos manteniendo la consistencia matemática.
     *
     * @return Collection<int, Collection<int, array{id: int, nombre: string, puntaje: float|null}>>
     */
    public function scoresPorDimensionAgrupado(Builder $baseQuery, string $fkDemografico): Collection
    {
        $dimensiones = Dimension::with('subdimensiones')->orderBy('orden')->get();

        $promedios = (clone $baseQuery)
            ->whereHas('opcionRespuesta', fn (Builder $q) => $q->where('valor_numerico', '!=', 0))
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->join('encuestas as enc_join', 'respuestas.encuesta_id', '=', 'enc_join.id')
            ->join('datos_demograficos', 'enc_join.id', '=', 'datos_demograficos.encuesta_id')
            ->join('preguntas', 'respuestas.pregunta_id', '=', 'preguntas.id')
            ->selectRaw("preguntas.subdimension_id, datos_demograficos.{$fkDemografico} as grupo_id, AVG(opciones_respuesta.valor_numerico) as promedio")
            ->groupBy('preguntas.subdimension_id', "datos_demograficos.{$fkDemografico}")
            ->get();

        $resultadosPorGrupo = $promedios->groupBy('grupo_id');

        return $resultadosPorGrupo->map(function ($filasGrupo) use ($dimensiones) {
            $filasGrupoSub = $filasGrupo->keyBy('subdimension_id');

            return $dimensiones->map(function (Dimension $d) use ($filasGrupoSub) {
                $puntajesSub = $d->subdimensiones->map(function ($sub) use ($filasGrupoSub) {
                    if (isset($filasGrupoSub[$sub->id])) {
                        $avg = $filasGrupoSub[$sub->id]->promedio;

                        return (($avg - 1) / 2) * 100;
                    }

                    return null;
                })->filter(fn ($p) => $p !== null);

                $puntajeDimension = $puntajesSub->count() > 0
                    ? round($puntajesSub->average(), 1)
                    : null;

                return [
                    'id' => $d->id,
                    'nombre' => $d->nombre,
                    'puntaje' => $puntajeDimension,
                ];
            });
        });
    }
}
