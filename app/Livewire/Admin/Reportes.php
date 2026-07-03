<?php

namespace App\Livewire\Admin;

use App\Models\Corporativo;
use App\Models\Dimension;
use App\Models\Empresa;
use App\Models\Lote;
use App\Models\Respuesta;
use App\Models\Subdimension;
use App\Models\Sucursal;
use App\Services\ClimaScoringService;
use App\Traits\HasTenantScope;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin', ['heading' => 'Reportes'])]
class Reportes extends Component
{
    use HasTenantScope;

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

    public string $filtroCorporativoId = '';

    public string $filtroSucursalId = '';

    public string $filtroLoteId = '';

    public ?string $hashDatosNivel1 = null;

    public array $pdfSvgs = [];

    public \Illuminate\Support\Collection $edades;

    public \Illuminate\Support\Collection $sexos;

    public \Illuminate\Support\Collection $cargos;

    public \Illuminate\Support\Collection $lugares;

    public \Illuminate\Support\Collection $grados;

    public \Illuminate\Support\Collection $antiguedades;

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
        $this->filtroCorporativoId = '';
        $this->filtroEmpresaId = '';
        $this->filtroSucursalId = '';
        $this->filtroLoteId = '';
        $this->irNivel1();
    }

    public function updatedFiltroCorporativoId(): void
    {
        $this->filtroEmpresaId = '';
        $this->filtroSucursalId = '';
        $this->filtroLoteId = '';
    }

    public function updatedFiltroEmpresaId(): void
    {
        $this->filtroSucursalId = '';
        $this->filtroLoteId = '';
    }

    public function updatedFiltroSucursalId(): void
    {
        $this->filtroLoteId = '';
    }

    public function getCorporativosProperty()
    {
        $user = auth()->user();
        if ($user->role !== \App\Enums\Role::SUPER_ADMIN->value) {
            return collect();
        }

        return Corporativo::where('activa', true)->orderBy('nombre')->get();
    }

    public function getSucursalesProperty()
    {
        $user = auth()->user();

        // admin_sucursal: no necesita selector, su scope está fijo en HasTenantScope
        if ($user->role === \App\Enums\Role::ADMIN_SUCURSAL->value) {
            return collect();
        }

        // Sin empresa seleccionada para roles que la requieren
        if (in_array($user->role, [
            \App\Enums\Role::SUPER_ADMIN->value,
            \App\Enums\Role::ADMIN_CORPORATIVO->value,
        ]) && ! $this->filtroEmpresaId) {
            return collect();
        }

        $empresaId = $this->filtroEmpresaId ?: $user->empresa_id;

        return Sucursal::where('empresa_id', $empresaId)
            ->where('activa', true)
            ->orderBy('nombre')
            ->get();
    }

    public function getLotesProperty()
    {
        $user = auth()->user();

        if (in_array($user->role, [
            \App\Enums\Role::SUPER_ADMIN->value,
            \App\Enums\Role::ADMIN_CORPORATIVO->value,
        ]) && ! $this->filtroEmpresaId) {
            return collect();
        }

        $query = Lote::with('sucursal');
        $query = $this->scopeByRole($query);

        if ($this->filtroEmpresaId) {
            $query->where('empresa_id', $this->filtroEmpresaId);
        }

        if ($this->filtroSucursalId) {
            $query->where('sucursal_id', $this->filtroSucursalId);
        }

        return $query->orderByDesc('fecha_inicio')->get();
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

        $query->whereHas('encuesta.lote', fn ($q) => $this->scopeByRole($q));

        if ($user->role === \App\Enums\Role::SUPER_ADMIN->value && $this->filtroCorporativoId) {
            $query->whereHas('encuesta.lote.empresa', fn ($q) => $q->where('corporativo_id', $this->filtroCorporativoId));
        }

        if (in_array($user->role, [
            \App\Enums\Role::SUPER_ADMIN->value,
            \App\Enums\Role::ADMIN_CORPORATIVO->value,
        ]) && $this->filtroEmpresaId) {
            $sucursalIds = $this->sucursalIdsDeEmpresa((int) $this->filtroEmpresaId);
            $query->whereHas('encuesta.lote', function ($q) use ($sucursalIds) {
                $q->where('empresa_id', $this->filtroEmpresaId)
                  ->orWhereIn('sucursal_id', $sucursalIds);
            });
        }

        if ($this->filtroSucursalId) {
            $query->whereHas('encuesta.lote', fn ($q) => $q->where('lotes.sucursal_id', $this->filtroSucursalId));
        }

        if ($this->filtroLoteId) {
            $query->whereHas('encuesta.lote', fn ($q) => $q->where('lotes.id', $this->filtroLoteId));
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

        $query->whereHas('lote', fn ($q) => $this->scopeByRole($q));

        if ($user->role === \App\Enums\Role::SUPER_ADMIN->value && $this->filtroCorporativoId) {
            $query->whereHas('lote.empresa', fn ($q) => $q->where('corporativo_id', $this->filtroCorporativoId));
        }

        if (in_array($user->role, [
            \App\Enums\Role::SUPER_ADMIN->value,
            \App\Enums\Role::ADMIN_CORPORATIVO->value,
        ]) && $this->filtroEmpresaId) {
            $sucursalIds = $this->sucursalIdsDeEmpresa((int) $this->filtroEmpresaId);
            $query->whereHas('lote', function ($q) use ($sucursalIds) {
                $q->where('empresa_id', $this->filtroEmpresaId)
                  ->orWhereIn('sucursal_id', $sucursalIds);
            });
        }

        if ($this->filtroSucursalId) {
            $query->where(function ($q) {
                $q->whereHas('lote', fn ($q2) => $q2->where('sucursal_id', $this->filtroSucursalId));
            });
        }

        if ($this->filtroLoteId) {
            $query->where('lote_id', $this->filtroLoteId);
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

        $preguntas = \App\Models\Pregunta::where('subdimension_id', $this->subdimensionActivaId)
            ->orderBy('orden')
            ->get();

        $preguntasIds = $preguntas->pluck('id')->toArray();

        $respuestasRaw = $this->getBaseQuery()
            ->whereIn('respuestas.pregunta_id', $preguntasIds)
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->selectRaw('respuestas.pregunta_id, opciones_respuesta.opcion, opciones_respuesta.valor_numerico, opciones_respuesta.orden, COUNT(*) as total')
            ->groupBy('respuestas.pregunta_id', 'opciones_respuesta.opcion', 'opciones_respuesta.valor_numerico', 'opciones_respuesta.orden')
            ->orderBy('opciones_respuesta.orden')
            ->get();

        $grouped = $respuestasRaw->groupBy('pregunta_id');

        return $preguntas->map(function ($pregunta) use ($grouped) {
            $respuestasPregunta = $grouped->get($pregunta->id, collect());
            $totalRespuestas = $respuestasPregunta->sum('total');

            // Calcular promedio excluyendo valor_numerico = 0
            $respuestasValidas = $respuestasPregunta->filter(fn ($op) => $op->valor_numerico != 0);
            $sumaValores = $respuestasValidas->sum(fn ($op) => $op->valor_numerico * $op->total);
            $cuentaValores = $respuestasValidas->sum('total');
            $promedio = $cuentaValores > 0 ? ($sumaValores / $cuentaValores) : null;

            return [
                'id' => $pregunta->id,
                'texto' => $pregunta->texto,
                'puntaje' => $promedio !== null ? round((($promedio - 1) / 2) * 100, 1) : 0.0,
                'total' => $totalRespuestas,
                'distribucion' => $respuestasPregunta->map(fn ($op) => [
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
            ->whereHas('lote', fn ($q) => $this->scopeByRole($q))
            ->when(in_array($user->role, [
                \App\Enums\Role::SUPER_ADMIN->value,
                \App\Enums\Role::ADMIN_CORPORATIVO->value,
            ]) && $this->filtroEmpresaId, fn ($q) => $q->whereHas('lote', fn ($q2) => $q2->where('empresa_id', $this->filtroEmpresaId)))
            ->when($this->filtroLoteId, fn ($q) => $q->where('lote_id', $this->filtroLoteId))
            ->when($this->filtroCorporativoId && $user->role === \App\Enums\Role::SUPER_ADMIN->value,
                fn ($q) => $q->whereHas('lote.empresa', fn ($q2) => $q2->where('corporativo_id', $this->filtroCorporativoId)))
            ->when($this->filtroSucursalId, fn ($q) => $q->whereHas('lote', fn ($q2) => $q2->where('sucursal_id', $this->filtroSucursalId)))
            ->count();

        $scoringService = app(ClimaScoringService::class);
        $promedioGeneral = $scoringService->promedioGeneral($this->getBaseQuery());

        return view('livewire.admin.reportes', [
            'promedioGeneral' => $promedioGeneral,
            'completadasFiltradas' => $completadasFiltradas,
            'totalTokens' => $totalTokens,
            'sinDatos' => $completadasFiltradas === 0,
            'bajoUmbral' => $completadasFiltradas > 0
                            && $completadasFiltradas < ClimaScoringService::UMBRAL_REPORTES,
            'totalRespondientes' => $completadasFiltradas,
            'umbralReportes' => ClimaScoringService::UMBRAL_REPORTES,
            'empresas' => in_array($user->role, [
                \App\Enums\Role::SUPER_ADMIN->value,
                \App\Enums\Role::ADMIN_CORPORATIVO->value,
            ])
                ? (match ($user->role) {
                    \App\Enums\Role::SUPER_ADMIN->value => $this->filtroCorporativoId
                        ? Empresa::where('corporativo_id', $this->filtroCorporativoId)->orderBy('nombre')->get()
                        : Empresa::orderBy('nombre')->get(),
                    \App\Enums\Role::ADMIN_CORPORATIVO->value => Empresa::where('corporativo_id', $user->corporativo_id)->orderBy('nombre')->get(),
                    default => collect(),
                })
                : collect(),
            'corporativos' => $this->corporativos,
            'sucursales' => $this->sucursales,
            'lotes' => $this->lotes,
            'dimensionActiva' => $this->dimensionActivaId ? Dimension::find($this->dimensionActivaId) : null,
            'subdimensionActiva' => $this->subdimensionActivaId ? Subdimension::find($this->subdimensionActivaId) : null,
            'datosNivel1' => $datosNivel1,
            'datosNivel2' => $datosNivel2,
            'distribucionAgregada' => $distribucionAgregada,
            'datosNivel3' => $datosNivel3,
        ]);
    }
}
