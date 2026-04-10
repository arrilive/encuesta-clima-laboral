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
        $dimensiones = Dimension::orderBy('orden')->get();

        $promedios = (clone $baseQuery)
            ->whereHas('opcionRespuesta', fn(Builder $q) =>
                $q->where('valor_numerico', '!=', 0)
            )
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->join('preguntas', 'respuestas.pregunta_id', '=', 'preguntas.id')
            ->join('subdimensiones', 'preguntas.subdimension_id', '=', 'subdimensiones.id')
            ->selectRaw('subdimensiones.dimension_id, AVG(opciones_respuesta.valor_numerico) as promedio')
            ->groupBy('subdimensiones.dimension_id')
            ->get()
            ->keyBy('dimension_id');

        return $dimensiones->map(fn(Dimension $d) => [
            'id'      => $d->id,
            'nombre'  => $d->nombre,
            'puntaje' => isset($promedios[$d->id])
                ? round((($promedios[$d->id]->promedio - 1) / 2) * 100, 1)
                : 0.0,
        ])->values();
    }

    /**
     * Calcula el puntaje (0–100) para cada subdimensión sobre el conjunto
     * de respuestas representado por $baseQuery.
     *
     * @return Collection<array{id: int, nombre: string, puntaje: float}>
     */
    public function scoresPorSubdimension(Builder $baseQuery): Collection
    {
        return Subdimension::orderBy('orden')->get()->map(fn(Subdimension $s) => [
            'id'      => $s->id,
            'nombre'  => $s->nombre,
            'puntaje' => $this->calcularPuntajeSubdimension($baseQuery, $s->id),
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
        $result = (clone $baseQuery)
            ->whereHas('opcionRespuesta', fn(Builder $q) => $q->where('valor_numerico', '!=', 0))
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->avg('opciones_respuesta.valor_numerico');

        if ($result === null) {
            return 0.0;
        }

        return round((($result - 1) / 2) * 100, 1);
    }

    // -------------------------------------------------------------------------
    // Implementación interna
    // -------------------------------------------------------------------------

    /**
     * Puntaje normalizado (0–100) para una dimensión específica.
     *
     * Privado: es un detalle de implementación de scoresPorDimension.
     * El consumidor siempre quiere la colección completa; nunca necesita
     * este cálculo de forma aislada.
     */
    private function calcularPuntajeDimension(Builder $baseQuery, int $dimensionId): float
    {
        $result = (clone $baseQuery)
            ->whereHas('pregunta.subdimension', fn(Builder $q) => $q->where('dimension_id', $dimensionId))
            ->whereHas('opcionRespuesta', fn(Builder $q) => $q->where('valor_numerico', '!=', 0))
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->avg('opciones_respuesta.valor_numerico');

        if ($result === null) {
            return 0.0;
        }

        return round((($result - 1) / 2) * 100, 1);
    }

    private function calcularPuntajeSubdimension(Builder $baseQuery, int $subdimensionId): float
    {
        $result = (clone $baseQuery)
            ->whereHas('pregunta', fn(Builder $q) => $q->where('subdimension_id', $subdimensionId))
            ->whereHas('opcionRespuesta', fn(Builder $q) => $q->where('valor_numerico', '!=', 0))
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->avg('opciones_respuesta.valor_numerico');

        if ($result === null) {
            return 0.0;
        }

        return round((($result - 1) / 2) * 100, 1);
    }
}
