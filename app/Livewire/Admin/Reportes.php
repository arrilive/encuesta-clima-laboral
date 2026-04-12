<?php

namespace App\Livewire\Admin;

use App\Models\Dimension;
use App\Models\Empresa;
use App\Models\Respuesta;
use App\Models\Subdimension;
use App\Services\ClimaScoringService;
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

    public ?string $hashDatosNivel1 = null;

    public array $pdfSvgs = [];

    public $edades;

    public $sexos;

    public $cargos;

    public $lugares;

    public $grados;

    public $antiguedades;

    public function mount(): void
    {
        $this->edades = \App\Models\Edad::orderBy('orden')->get();
        $this->sexos = \App\Models\Sexo::orderBy('orden')->get();
        $this->cargos = \App\Models\Cargo::orderBy('orden')->get();
        $this->lugares = \App\Models\LugarTrabajo::orderBy('orden')->get();
        $this->grados = \App\Models\GradoAcademico::orderBy('orden')->get();
        $this->antiguedades = \App\Models\Antiguedad::orderBy('orden')->get();
    }

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

    public function prepararExportacion(array $svgs, string $alcance, int $limite = 25): void
    {
        session(['pdf_svgs' => $svgs]);
        $this->dispatch('pdf-listo', alcance: $alcance, limite: $limite);
    }

    protected function getBaseQuery()
    {
        $user = auth()->user();
        $query = Respuesta::query()
            ->whereHas('encuesta', fn ($q) => $q->where('estado', 'completado'));

        if ($user->role === 'admin_empresa') {
            $query->whereHas('encuesta', fn ($q) => $q->where('empresa_id', $user->empresa_id)
            );
        } elseif ($this->filtroEmpresaId) {
            $query->whereHas('encuesta', fn ($q) => $q->where('empresa_id', $this->filtroEmpresaId)
            );
        }

        if ($this->filtroEdadId) {
            $query->whereHas('encuesta.datoDemografico', fn ($q) => $q->where('edad_id', $this->filtroEdadId)
            );
        }
        if ($this->filtroSexoId) {
            $query->whereHas('encuesta.datoDemografico', fn ($q) => $q->where('sexo_id', $this->filtroSexoId)
            );
        }
        if ($this->filtroCargoId) {
            $query->whereHas('encuesta.datoDemografico', fn ($q) => $q->where('cargo_id', $this->filtroCargoId)
            );
        }
        if ($this->filtroLugarTrabajoId) {
            $query->whereHas('encuesta.datoDemografico', fn ($q) => $q->where('lugar_trabajo_id', $this->filtroLugarTrabajoId)
            );
        }
        if ($this->filtroGradoAcademicoId) {
            $query->whereHas('encuesta.datoDemografico', fn ($q) => $q->where('grado_academico_id', $this->filtroGradoAcademicoId)
            );
        }
        if ($this->filtroAntiguedadId) {
            $query->whereHas('encuesta.datoDemografico', fn ($q) => $q->where('antiguedad_id', $this->filtroAntiguedadId)
            );
        }

        return $query;
    }

    protected function getEncuestasBaseQuery(bool $soloSinFiltrosDemograficos = false)
    {
        $user = auth()->user();
        $query = \App\Models\Encuesta::where('estado', 'completado');

        if ($user->role === 'admin_empresa') {
            $query->where('empresa_id', $user->empresa_id);
        } elseif ($this->filtroEmpresaId) {
            $query->where('empresa_id', $this->filtroEmpresaId);
        }

        if ($soloSinFiltrosDemograficos) {
            return $query;
        }

        if ($this->filtroEdadId) {
            $query->whereHas('datoDemografico', fn ($q) => $q->where('edad_id', $this->filtroEdadId));
        }
        if ($this->filtroSexoId) {
            $query->whereHas('datoDemografico', fn ($q) => $q->where('sexo_id', $this->filtroSexoId));
        }
        if ($this->filtroCargoId) {
            $query->whereHas('datoDemografico', fn ($q) => $q->where('cargo_id', $this->filtroCargoId));
        }
        if ($this->filtroLugarTrabajoId) {
            $query->whereHas('datoDemografico', fn ($q) => $q->where('lugar_trabajo_id', $this->filtroLugarTrabajoId));
        }
        if ($this->filtroGradoAcademicoId) {
            $query->whereHas('datoDemografico', fn ($q) => $q->where('grado_academico_id', $this->filtroGradoAcademicoId));
        }
        if ($this->filtroAntiguedadId) {
            $query->whereHas('datoDemografico', fn ($q) => $q->where('antiguedad_id', $this->filtroAntiguedadId));
        }

        return $query;
    }

    // ── NIVEL 1: DIMENSIONES ──────────────────────────────────────────────

    public function getDatosNivel1(): array
    {
        $scoringService = app(ClimaScoringService::class);
        $baseQuery = $this->getBaseQuery();

        return $scoringService->scoresPorDimension($baseQuery)->toArray();
    }

    // ── NIVEL 2: SUBDIMENSIONES ───────────────────────────────────────────

    public function getDatosNivel2(): array
    {
        $scoringService = app(ClimaScoringService::class);
        $baseQuery = $this->getBaseQuery();
        $allScores = $scoringService->scoresPorSubdimension($baseQuery);

        // Filtrar solo las de la dimensión activa
        $subIds = Subdimension::where('dimension_id', $this->dimensionActivaId)
            ->pluck('id')
            ->toArray();

        return $allScores->whereIn('id', $subIds)->values()->toArray();
    }

    public function getDistribucionAgregadaNivel2(): array
    {
        return (clone $this->getBaseQuery())
            ->whereHas('pregunta.subdimension', fn ($q) => $q->where('dimension_id', $this->dimensionActivaId)
            )
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->selectRaw('opciones_respuesta.id, opciones_respuesta.opcion as nombre, opciones_respuesta.orden, COUNT(*) as total')
            ->groupBy('opciones_respuesta.id', 'opciones_respuesta.opcion', 'opciones_respuesta.orden')
            ->orderBy('opciones_respuesta.orden')
            ->get()
            ->map(fn ($row) => [
                'opcion' => $row->nombre,
                'total' => (int) $row->total,
            ])
            ->toArray();
    }

    // ── NIVEL 3: PREGUNTAS INDIVIDUALES ──────────────────────────────────

    public function getDatosNivel3(): array
    {
        if (! $this->subdimensionActivaId) {
            return [];
        }

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
                    ->whereHas('opcionRespuesta', fn ($q) => $q->where('valor_numerico', '!=', 0))
                    ->join('opciones_respuesta as or2', 'respuestas.opcion_respuesta_id', '=', 'or2.id')
                    ->avg('or2.valor_numerico');

                return [
                    'id' => $pregunta->id,
                    'texto' => $pregunta->texto,
                    'puntaje' => $puntaje !== null ? round((($puntaje - 1) / 2) * 100, 1) : 0.0,
                    'total' => $totalRespuestas,
                    'distribucion' => $distribucion->map(fn ($op) => [
                        'opcion' => $op->opcion,
                        'valor_numerico' => $op->valor_numerico,
                        'total' => $op->total,
                        'porcentaje' => $totalRespuestas > 0
                                            ? round($op->total / $totalRespuestas * 100)
                                            : 0,
                    ])->toArray(),
                ];
            })->toArray();
    }

    private function despacharEventos(array $datosNivel1, array $datosNivel2, array $distribucionAgregada): void
    {
        $nuevoHashRadar = md5(json_encode($datosNivel1));
        if ($this->hashDatosNivel1 !== $nuevoHashRadar) {
            $this->dispatch('radar-datos-actualizados', datos: $datosNivel1);
            $this->hashDatosNivel1 = $nuevoHashRadar;
        }

        if ($this->nivel === 2) {
            $this->dispatch('barras-nivel2-actualizadas', datos: $datosNivel2);
            $this->dispatch('donut-nivel2-actualizado', datos: $distribucionAgregada);
        }
    }

    public function render()
    {
        $user = auth()->user();

        $datosNivel1 = $this->getDatosNivel1();
        $datosNivel2 = $this->nivel === 2 ? $this->getDatosNivel2() : [];
        $distribucionAgregada = $this->nivel === 2 ? $this->getDistribucionAgregadaNivel2() : [];
        $datosNivel3 = $this->nivel === 3 ? $this->getDatosNivel3() : [];
        $this->despacharEventos($datosNivel1, $datosNivel2, $distribucionAgregada);

        $completadasFiltradas = $this->getEncuestasBaseQuery()->count();

        $totalTokens = \App\Models\Encuesta::query()
            ->when($user->role === 'admin_empresa', fn ($q) => $q->where('empresa_id', $user->empresa_id))
            ->when($user->role === 'super_admin' && $this->filtroEmpresaId, fn ($q) => $q->where('empresa_id', $this->filtroEmpresaId))
            ->count();

        $scoringService = app(ClimaScoringService::class);
        $promedioGeneral = $scoringService->promedioGeneral($this->getBaseQuery());

        return view('livewire.admin.reportes', [
            'promedioGeneral' => $promedioGeneral,
            'completadasFiltradas' => $completadasFiltradas,
            'totalTokens' => $totalTokens,
            'sinDatos' => $completadasFiltradas === 0,
            'empresas' => $user->role === 'super_admin' ? Empresa::orderBy('nombre')->get() : collect(),
            'dimensionActiva' => $this->dimensionActivaId ? Dimension::find($this->dimensionActivaId) : null,
            'subdimensionActiva' => $this->subdimensionActivaId ? Subdimension::find($this->subdimensionActivaId) : null,
            'datosNivel1' => $datosNivel1,
            'datosNivel2' => $datosNivel2,
            'distribucionAgregada' => $distribucionAgregada,
            'datosNivel3' => $datosNivel3,
        ]);
    }
}
