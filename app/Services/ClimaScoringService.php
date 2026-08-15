<?php

namespace App\Services;

use App\Models\Dimension;
use App\Models\Respuesta;
use App\Models\Subdimension;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ClimaScoringService
{
    public const UMBRAL_REPORTES = 5;

    public const UMBRAL_RESPUESTAS_ABIERTAS = 10;

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
            ->selectRaw('preguntas.subdimension_id, AVG(CASE WHEN preguntas.invertida THEN (4 - opciones_respuesta.valor_numerico) ELSE opciones_respuesta.valor_numerico END) as promedio')
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
            ->selectRaw('preguntas.subdimension_id, AVG(CASE WHEN preguntas.invertida THEN (4 - opciones_respuesta.valor_numerico) ELSE opciones_respuesta.valor_numerico END) as promedio')
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
            ->selectRaw("preguntas.subdimension_id, datos_demograficos.{$fkDemografico} as grupo_id, AVG(CASE WHEN preguntas.invertida THEN (4 - opciones_respuesta.valor_numerico) ELSE opciones_respuesta.valor_numerico END) as promedio")
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

    /**
     * Calcula el promedio general de clima (0–100) para múltiples empresas
     * en una sola consulta SQL, evitando el patrón N+1 de calcularRanking().
     *
     * Usa joins explícitos en toda la cadena — sin whereHas anidados —
     * para mantener consistencia con scoresPorDimension() y evitar subqueries.
     *
     * @param  array<int>  $empresaIds
     * @return Collection<int, float> indexada por empresa_id
     */
    public function promediosGeneralesPorEmpresas(array $empresaIds): Collection
    {
        if (empty($empresaIds)) {
            return collect();
        }

        $hoy = \Carbon\Carbon::today()->toDateString();

        // Obtener todos los lotes de las empresas en una sola consulta
        $lotesDeEmpresas = \App\Models\Lote::query()
            ->leftJoin('sucursales', 'lotes.sucursal_id', '=', 'sucursales.id')
            ->where(function ($q) use ($empresaIds) {
                $q->whereIn('lotes.empresa_id', $empresaIds)
                    ->orWhereIn('sucursales.empresa_id', $empresaIds);
            })
            ->select('lotes.*', \Illuminate\Support\Facades\DB::raw('COALESCE(lotes.empresa_id, sucursales.empresa_id) as resolved_empresa_id'))
            ->get()
            ->groupBy('resolved_empresa_id');

        $lotesValidos = [];
        foreach ($empresaIds as $empresaId) {
            $lotes = $lotesDeEmpresas->get($empresaId, collect());

            // Limitación conocida y aceptada: al consolidar empresa + sucursales, cada una puede tener su "lote de estado actual"
            // con fecha_fin distinta entre sí (ej. Sucursal Norte cerrada en marzo, Sucursal Sur cerrada en junio).
            // El sistema tomará el lote más reciente dentro del conjunto combinado sin distinguir cuál sucursal aporta qué fecha.
            // Esto se resuelve formalmente en Issue M (comparativas históricas) con el concepto de "tanda/familia de lotes",
            // que aún no existe en el modelo de datos.

            // Regla 1: Buscar el lote con fecha_fin más reciente que ya pasó (fecha_fin < hoy)
            $cerrados = $lotes->filter(fn ($l) => $l->fecha_fin && $l->fecha_fin->toDateString() < $hoy)
                ->sortByDesc('fecha_fin');

            $loteEstadoActual = null;
            if ($cerrados->isNotEmpty()) {
                $loteEstadoActual = $cerrados->first();
            } else {
                // Regla 2: Si no existe ninguno -> buscar el lote con activo = true
                $activos = $lotes->filter(fn ($l) => $l->activo && (! $l->fecha_fin || $l->fecha_fin->toDateString() >= $hoy))
                    ->sortByDesc('fecha_inicio');
                if ($activos->isNotEmpty()) {
                    $loteEstadoActual = $activos->first();
                }
            }

            if ($loteEstadoActual) {
                // Se debe seguir respetando el umbral de anonimato existente (5 respuestas numéricas)
                $completadas = \App\Models\Encuesta::where('estado', 'completado')
                    ->where('lote_id', $loteEstadoActual->id)
                    ->count();

                if ($completadas >= self::UMBRAL_REPORTES) {
                    $lotesValidos[$empresaId] = $loteEstadoActual->id;
                }
            }
        }

        if (empty($lotesValidos)) {
            return collect(array_fill_keys($empresaIds, null));
        }

        $promediosSub = Respuesta::query()
            ->join('encuestas', 'respuestas.encuesta_id', '=', 'encuestas.id')
            ->join('lotes', 'encuestas.lote_id', '=', 'lotes.id')
            ->leftJoin('sucursales', 'lotes.sucursal_id', '=', 'sucursales.id')
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->join('preguntas', 'respuestas.pregunta_id', '=', 'preguntas.id')
            ->where('encuestas.estado', 'completado')
            ->whereIn('encuestas.lote_id', array_values($lotesValidos))
            ->where('opciones_respuesta.valor_numerico', '!=', 0)
            ->selectRaw('COALESCE(lotes.empresa_id, sucursales.empresa_id) as empresa_id, preguntas.subdimension_id, AVG(CASE WHEN preguntas.invertida THEN (4 - opciones_respuesta.valor_numerico) ELSE opciones_respuesta.valor_numerico END) as promedio')
            ->groupBy(\Illuminate\Support\Facades\DB::raw('COALESCE(lotes.empresa_id, sucursales.empresa_id)'), 'preguntas.subdimension_id')
            ->get()
            ->groupBy('empresa_id');

        $dimensiones = Dimension::with('subdimensiones')->orderBy('orden')->get();

        $resultados = collect();

        foreach ($empresaIds as $empresaId) {
            if (! isset($lotesValidos[$empresaId])) {
                $resultados->put($empresaId, null);

                continue;
            }

            $subsPorEmpresa = $promediosSub->get($empresaId, collect())->keyBy('subdimension_id');

            $scoresDimensiones = $dimensiones->map(function (Dimension $d) use ($subsPorEmpresa) {
                $puntajesSub = $d->subdimensiones->map(function ($sub) use ($subsPorEmpresa) {
                    if (isset($subsPorEmpresa[$sub->id])) {
                        $avg = $subsPorEmpresa[$sub->id]->promedio;

                        return (($avg - 1) / 2) * 100;
                    }

                    return null;
                })->filter(fn ($p) => $p !== null);

                return $puntajesSub->count() > 0 ? $puntajesSub->average() : null;
            })->filter(fn ($p) => $p !== null);

            $resultados->put(
                $empresaId,
                $scoresDimensiones->count() > 0 ? round($scoresDimensiones->average(), 1) : null
            );
        }

        return $resultados;
    }

    /**
     * Calcula los puntajes por dimensión, subdimensión y promedio general
     * agrupados por lote_id sobre el conjunto de respuestas representado por $baseQuery.
     *
     * @return Collection<int, array{
     *     lote_id: int,
     *     promedio_general: float,
     *     dimensiones: Collection<int, array{id: int, nombre: string, puntaje: float|null}>,
     *     subdimensiones: Collection<int, array{id: int, dimension_id: int, nombre: string, puntaje: float}>
     * }> Indexada por lote_id
     */
    public function scoresPorDimensionPorLotes(Builder $baseQuery): Collection
    {
        $dimensiones = Dimension::with('subdimensiones')->orderBy('orden')->get();

        $promedios = (clone $baseQuery)
            ->whereHas('opcionRespuesta', fn (Builder $q) => $q->where('valor_numerico', '!=', 0))
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->join('encuestas as enc_join', 'respuestas.encuesta_id', '=', 'enc_join.id')
            ->join('preguntas', 'respuestas.pregunta_id', '=', 'preguntas.id')
            ->selectRaw('preguntas.subdimension_id, enc_join.lote_id, AVG(CASE WHEN preguntas.invertida THEN (4 - opciones_respuesta.valor_numerico) ELSE opciones_respuesta.valor_numerico END) as promedio')
            ->groupBy('preguntas.subdimension_id', 'enc_join.lote_id')
            ->get();

        $resultadosPorLote = $promedios->groupBy('lote_id');

        return $resultadosPorLote->map(function ($filasLote, $loteId) use ($dimensiones) {
            $filasLoteSub = $filasLote->keyBy('subdimension_id');

            $allSubdimensionScores = collect();

            $dimensionesScores = $dimensiones->map(function (Dimension $d) use ($filasLoteSub, $allSubdimensionScores) {
                $puntajesSub = $d->subdimensiones->map(function ($sub) use ($filasLoteSub, $d, $allSubdimensionScores) {
                    if (isset($filasLoteSub[$sub->id])) {
                        $avg = $filasLoteSub[$sub->id]->promedio;
                        $puntajeSub = round((($avg - 1) / 2) * 100, 1);

                        $allSubdimensionScores->push([
                            'id' => $sub->id,
                            'dimension_id' => $d->id,
                            'nombre' => $sub->nombre,
                            'puntaje' => $puntajeSub,
                        ]);

                        return $puntajeSub;
                    } else {
                        $allSubdimensionScores->push([
                            'id' => $sub->id,
                            'dimension_id' => $d->id,
                            'nombre' => $sub->nombre,
                            'puntaje' => null,
                        ]);
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

            $dimensionesValidas = $dimensionesScores->whereNotNull('puntaje');
            $promedioGeneral = $dimensionesValidas->isNotEmpty()
                ? round($dimensionesValidas->avg('puntaje'), 1)
                : null;

            return [
                'lote_id' => (int) $loteId,
                'promedio_general' => $promedioGeneral,
                'dimensiones' => $dimensionesScores,
                'subdimensiones' => $allSubdimensionScores,
            ];
        });
    }
}
