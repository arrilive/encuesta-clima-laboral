<?php

namespace App\Livewire\Admin;

use App\Models\Encuesta;
use App\Models\PreguntaAbierta;
use App\Models\RespuestaAbierta;
use App\Services\ClimaScoringService;
use App\Traits\HasTenantScope;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use Livewire\WithPagination;

class RespuestasAbiertas extends Component
{
    use WithPagination, HasTenantScope;

    #[Reactive]
    public string $filtroEdadId = '';

    #[Reactive]
    public string $filtroSexoId = '';

    #[Reactive]
    public string $filtroCargoId = '';

    #[Reactive]
    public string $filtroLugarTrabajoId = '';

    #[Reactive]
    public string $filtroGradoAcademicoId = '';

    #[Reactive]
    public string $filtroAntiguedadId = '';

    #[Reactive]
    public string $filtroEmpresaId = '';

    public ?int $preguntaAbiertaActiva = null;

    public bool $mostrarRespuestasAbiertas = false;

    public function mount(): void
    {
        $primeraPregunta = PreguntaAbierta::orderBy('orden')->first();
        if ($primeraPregunta) {
            $this->preguntaAbiertaActiva = $primeraPregunta->id;
        }
    }

    protected function getEncuestasBaseQuery()
    {
        $user = auth()->user();
        $query = Encuesta::query()->where('estado', 'completado');

        $query->whereHas('lote', fn ($q) => $this->scopeByRole($q));

        if (! empty($this->filtroEmpresaId)) {
            $sucursalIds = $this->sucursalIdsDeEmpresa((int) $this->filtroEmpresaId);
            $query->whereHas('lote', function ($q) use ($sucursalIds) {
                $q->where('empresa_id', $this->filtroEmpresaId)
                  ->orWhereIn('sucursal_id', $sucursalIds);
            });
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

    public function getRespuestasAbiertasPaginadas()
    {
        if ($this->preguntaAbiertaActiva === null) {
            return collect();
        }

        return RespuestaAbierta::whereIn('encuesta_id', $this->getEncuestasBaseQuery()->select('id'))
            ->where('pregunta_abierta_id', $this->preguntaAbiertaActiva)
            ->whereNotNull('texto')
            ->where('texto', '!=', '')
            ->latest()
            ->paginate(20);
    }

    public function seleccionarPreguntaAbierta(int $id): void
    {
        $this->preguntaAbiertaActiva = $id;
        $this->resetPage();
    }

    public function toggleRespuestasAbiertas(): void
    {
        $this->mostrarRespuestasAbiertas = ! $this->mostrarRespuestasAbiertas;
        $this->resetPage();
    }

    public function render()
    {
        $totalRespondientes = $this->getEncuestasBaseQuery()->count();
        $bajoUmbral = $totalRespondientes > 0
                      && $totalRespondientes < ClimaScoringService::UMBRAL_RESPUESTAS_ABIERTAS;

        return view('livewire.admin.respuestas-abiertas', [
            'preguntasAbiertas' => PreguntaAbierta::orderBy('orden')->get(),
            'respuestasAbiertas' => ($this->mostrarRespuestasAbiertas && ! $bajoUmbral)
                ? $this->getRespuestasAbiertasPaginadas()
                : null,
            'bajoUmbral' => $bajoUmbral,
            'totalRespondientes' => $totalRespondientes,
            'umbralRespuestasAbiertas' => ClimaScoringService::UMBRAL_RESPUESTAS_ABIERTAS,
        ]);
    }
}
