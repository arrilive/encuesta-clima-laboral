<?php

namespace App\Livewire\Admin;

use App\Models\Dimension;
use App\Models\Empresa;
use App\Models\Respuesta;
use App\Models\Subdimension;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin', ['heading' => 'Reportes'])]
class Reportes extends Component
{
    // Navegación drill-down
    public int $nivel = 1;
    public ?int $dimensionActivaId = null;
    public ?int $subdimensionActivaId = null;

    // Filtros demográficos
    public string $filtroEdadId = '';
    public string $filtroSexoId = '';
    public string $filtroCargoId = '';
    public string $filtroLugarTrabajoId = '';
    public string $filtroGradoAcademicoId = '';
    public string $filtroAntiguedadId = '';

    // Filtro empresa (solo super_admin)
    public string $filtroEmpresaId = '';

    // Comparativas demográficas
    public string $campoComparativa = 'sexo';

    public ?string $hashDatosNivel1 = null;
    public ?string $hashComparativas = null;

    public function updated(string $property): void
    {
        //
    }

    public function irNivel1(): void
    {
        $this->nivel = 1;
        $this->dimensionActivaId = null;
        $this->subdimensionActivaId = null;
    }

    public function irNivel2(int $dimensionId): void
    {
        $this->nivel = 2;
        $this->dimensionActivaId = $dimensionId;
        $this->subdimensionActivaId = null;
    }

    public function irNivel3(int $subdimensionId): void
    {
        $this->nivel = 3;
        $this->subdimensionActivaId = $subdimensionId;
    }

    public function limpiarFiltros(): void
    {
        $this->filtroEdadId = '';
        $this->filtroSexoId = '';
        $this->filtroCargoId = '';
        $this->filtroLugarTrabajoId = '';
        $this->filtroGradoAcademicoId = '';
        $this->filtroAntiguedadId = '';
        $this->filtroEmpresaId = '';
        $this->irNivel1();
    }

    protected function getBaseQuery()
    {
        $user = auth()->user();
        $query = Respuesta::query()
            ->whereHas('encuesta', fn($q) => $q->where('estado', 'completado'));

        if ($user->role === 'admin_empresa') {
            $query->whereHas('encuesta', fn($q) =>
                $q->where('empresa_id', $user->empresa_id)
            );
        } elseif ($this->filtroEmpresaId) {
            $query->whereHas('encuesta', fn($q) =>
                $q->where('empresa_id', $this->filtroEmpresaId)
            );
        }

        if ($this->filtroEdadId) {
            $query->whereHas('encuesta.datoDemografico', fn($q) =>
                $q->where('edad_id', $this->filtroEdadId)
            );
        }
        if ($this->filtroSexoId) {
            $query->whereHas('encuesta.datoDemografico', fn($q) =>
                $q->where('sexo_id', $this->filtroSexoId)
            );
        }
        if ($this->filtroCargoId) {
            $query->whereHas('encuesta.datoDemografico', fn($q) =>
                $q->where('cargo_id', $this->filtroCargoId)
            );
        }
        if ($this->filtroLugarTrabajoId) {
            $query->whereHas('encuesta.datoDemografico', fn($q) =>
                $q->where('lugar_trabajo_id', $this->filtroLugarTrabajoId)
            );
        }
        if ($this->filtroGradoAcademicoId) {
            $query->whereHas('encuesta.datoDemografico', fn($q) =>
                $q->where('grado_academico_id', $this->filtroGradoAcademicoId)
            );
        }
        if ($this->filtroAntiguedadId) {
            $query->whereHas('encuesta.datoDemografico', fn($q) =>
                $q->where('antiguedad_id', $this->filtroAntiguedadId)
            );
        }

        return $query;
    }

    // ── NIVEL 1: DIMENSIONES ──────────────────────────────────────────────

    public function getDatosNivel1(): array
    {
        return Dimension::orderBy('orden')->get()->map(fn($d) => [
            'id'      => $d->id,
            'nombre'  => $d->nombre,
            'puntaje' => $this->calcularPuntajeDimension($d->id),
        ])->toArray();
    }

    protected function calcularPuntajeDimension(int $dimensionId): float
    {
        $result = (clone $this->getBaseQuery())
            ->whereHas('pregunta.subdimension', fn($q) =>
                $q->where('dimension_id', $dimensionId)
            )
            ->whereHas('opcionRespuesta', fn($q) =>
                $q->where('valor_numerico', '!=', 0)
            )
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->avg('opciones_respuesta.valor_numerico');

        if ($result === null) return 0.0;
        return round((($result - 1) / 2) * 100, 1);
    }

    private function getDatosComparativas(): array
    {
        $mapaCampos = [
            'sexo' => ['tabla' => 'sexos', 'fk' => 'sexo_id'],
            'cargo' => ['tabla' => 'cargos', 'fk' => 'cargo_id'],
            'edad' => ['tabla' => 'edades', 'fk' => 'edad_id'],
            'antiguedad' => ['tabla' => 'antiguedades', 'fk' => 'antiguedad_id'],
            'lugar_trabajo' => ['tabla' => 'lugares_trabajo', 'fk' => 'lugar_trabajo_id'],
            'grado_academico' => ['tabla' => 'grados_academicos', 'fk' => 'grado_academico_id'],
        ];

        if (!array_key_exists($this->campoComparativa, $mapaCampos)) {
            return ['categorias' => [], 'series' => []];
        }

        $config = $mapaCampos[$this->campoComparativa];
        $tablaDemografica = $config['tabla'];
        $fk = $config['fk'];

        $dimensiones = Dimension::orderBy('orden')->get();
        $categorias = $dimensiones->pluck('nombre')->toArray();

        $labelsDemograficos = \Illuminate\Support\Facades\DB::table($tablaDemografica)->orderBy('orden')->get();

        $seriesData = [];

        foreach ($labelsDemograficos as $labelObj) {
            $puntajes = [];
            $hasData = false;

            foreach ($dimensiones as $dimension) {
                $result = (clone $this->getBaseQuery())
                    ->whereHas('pregunta.subdimension', fn($q) => $q->where('dimension_id', $dimension->id))
                    ->whereHas('encuesta.datoDemografico', fn($q) => $q->where($fk, $labelObj->id))
                    ->whereHas('opcionRespuesta', fn($q) => $q->where('valor_numerico', '!=', 0))
                    ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
                    ->avg('opciones_respuesta.valor_numerico');

                if ($result !== null) {
                    $puntajes[] = round((($result - 1) / 2) * 100, 1);
                    $hasData = true;
                } else {
                    $puntajes[] = 0.0;
                }
            }

            if ($hasData) {
                $seriesData[] = [
                    'name' => $labelObj->opcion,
                    'data' => $puntajes,
                ];
            }
        }

        return [
            'categorias' => $categorias,
            'series' => $seriesData,
        ];
    }

    public function getComparativasProperty(): array
    {
        return $this->getDatosComparativas();
    }

    // ── NIVEL 2: SUBDIMENSIONES ───────────────────────────────────────────

    public function getDatosNivel2(): array
    {
        return Subdimension::where('dimension_id', $this->dimensionActivaId)
            ->orderBy('orden')
            ->get()
            ->map(fn($s) => [
                'id'      => $s->id,
                'nombre'  => $s->nombre,
                'puntaje' => $this->calcularPuntajeSubdimension($s->id),
            ])->toArray();
    }

    public function getDistribucionAgregadaNivel2(): array
    {
        return (clone $this->getBaseQuery())
            ->whereHas('pregunta.subdimension', fn($q) =>
                $q->where('dimension_id', $this->dimensionActivaId)
            )
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->selectRaw('opciones_respuesta.id, opciones_respuesta.opcion as nombre, opciones_respuesta.orden, COUNT(*) as total')
            ->groupBy('opciones_respuesta.id', 'opciones_respuesta.opcion', 'opciones_respuesta.orden')
            ->orderBy('opciones_respuesta.orden')
            ->get()
            ->map(fn($row) => [
                'opcion' => $row->nombre,
                'total'  => (int) $row->total,
            ])
            ->toArray();
    }

    protected function calcularPuntajeSubdimension(int $subdimensionId): float
    {
        $result = (clone $this->getBaseQuery())
            ->whereHas('pregunta', fn($q) =>
                $q->where('subdimension_id', $subdimensionId)
            )
            ->whereHas('opcionRespuesta', fn($q) =>
                $q->where('valor_numerico', '!=', 0)
            )
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->avg('opciones_respuesta.valor_numerico');

        if ($result === null) return 0.0;
        return round((($result - 1) / 2) * 100, 1);
    }

    // ── NIVEL 3: PREGUNTAS INDIVIDUALES ──────────────────────────────────

    public function getDatosNivel3(): array
    {
        if (!$this->subdimensionActivaId) return [];

        return \App\Models\Pregunta::where('subdimension_id', $this->subdimensionActivaId)
            ->orderBy('orden')
            ->get()
            ->map(function ($pregunta) {
                $baseQuery = clone $this->getBaseQuery();

                // Distribución: todas las opciones incluyendo "No responde" (valor_numerico = 0)
                $distribucion = (clone $baseQuery)
                    ->where('pregunta_id', $pregunta->id)
                    ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
                    ->selectRaw('opciones_respuesta.id, opciones_respuesta.opcion, opciones_respuesta.valor_numerico, COUNT(*) as total')
                    ->groupBy('opciones_respuesta.id', 'opciones_respuesta.opcion', 'opciones_respuesta.valor_numerico')
                    ->orderBy('opciones_respuesta.orden')
                    ->get();

                $totalRespuestas = $distribucion->sum('total');

                // Puntaje: excluye valor_numerico = 0
                $puntaje = (clone $baseQuery)
                    ->where('pregunta_id', $pregunta->id)
                    ->whereHas('opcionRespuesta', fn($q) => $q->where('valor_numerico', '!=', 0))
                    ->join('opciones_respuesta as or2', 'respuestas.opcion_respuesta_id', '=', 'or2.id')
                    ->avg('or2.valor_numerico');

                return [
                    'id'      => $pregunta->id,
                    'texto'   => $pregunta->texto,
                    'puntaje' => $puntaje !== null ? round((($puntaje - 1) / 2) * 100, 1) : 0.0,
                    'total'   => $totalRespuestas,
                    'distribucion' => $distribucion->map(fn($op) => [
                        'opcion'         => $op->opcion,
                        'valor_numerico' => $op->valor_numerico,
                        'total'          => $op->total,
                        'porcentaje'     => $totalRespuestas > 0
                                            ? round($op->total / $totalRespuestas * 100)
                                            : 0,
                    ])->toArray(),
                ];
            })->toArray();
    }

    public function render()
    {
        $user = auth()->user();

        $datosNivel1          = $this->nivel === 1 ? $this->getDatosNivel1() : [];
        $datosNivel2          = $this->nivel === 2 ? $this->getDatosNivel2() : [];
        $distribucionAgregada = $this->nivel === 2 ? $this->getDistribucionAgregadaNivel2() : [];
        $datosNivel3          = $this->nivel === 3 ? $this->getDatosNivel3() : [];

        if ($this->nivel === 1) {
            $nuevoHashRadar = md5(json_encode($datosNivel1));
            if ($this->hashDatosNivel1 !== $nuevoHashRadar) {
                $this->dispatch('radar-datos-actualizados', datos: $datosNivel1);
                $this->hashDatosNivel1 = $nuevoHashRadar;
            }

            $nuevoHashComparativas = md5(json_encode($this->comparativas));
            if ($this->hashComparativas !== $nuevoHashComparativas) {
                $this->dispatch('comparativas-actualizadas', comparativas: $this->comparativas);
                $this->hashComparativas = $nuevoHashComparativas;
            }
        } elseif ($this->nivel === 2) {
            $this->dispatch('barras-nivel2-actualizadas', datos: $datosNivel2);
            $this->dispatch('donut-nivel2-actualizado', datos: $distribucionAgregada);
        }

        return view('livewire.admin.reportes', [
            'edades'               => \App\Models\Edad::orderBy('orden')->get(),
            'sexos'                => \App\Models\Sexo::orderBy('orden')->get(),
            'cargos'               => \App\Models\Cargo::orderBy('orden')->get(),
            'lugares'              => \App\Models\LugarTrabajo::orderBy('orden')->get(),
            'grados'               => \App\Models\GradoAcademico::orderBy('orden')->get(),
            'antiguedades'         => \App\Models\Antiguedad::orderBy('orden')->get(),
            'empresas'             => $user->role === 'super_admin' ? Empresa::orderBy('nombre')->get() : collect(),
            'dimensionActiva'      => $this->dimensionActivaId ? Dimension::find($this->dimensionActivaId) : null,
            'subdimensionActiva'   => $this->subdimensionActivaId ? Subdimension::find($this->subdimensionActivaId) : null,
            'datosNivel1'          => $datosNivel1,
            'datosNivel2'          => $datosNivel2,
            'distribucionAgregada' => $distribucionAgregada,
            'datosNivel3'          => $datosNivel3,
        ]);
    }
}
