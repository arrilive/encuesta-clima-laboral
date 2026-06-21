<?php

namespace App\Livewire\Admin;

use App\Models\Dimension;
use App\Models\Respuesta;
use App\Services\ClimaScoringService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class ComparativasDemograficas extends Component
{
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

    public string $campoComparativa = 'sexo';

    protected function getBaseQuery()
    {
        $user = auth()->user();
        $query = Respuesta::query()
            ->whereHas('encuesta', fn ($q) => $q->where('estado', 'completado'));

        if ($user->role === 'admin_empresa') {
            $query->whereHas('encuesta.lote', fn ($q) => $q->where('empresa_id', $user->empresa_id)
            );
        } elseif ($this->filtroEmpresaId) {
            $query->whereHas('encuesta.lote', fn ($q) => $q->where('empresa_id', $this->filtroEmpresaId)
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

    private function getDatosComparativas(): array
    {
        $mapaCampos = [
            'sexo' => ['tabla' => 'sexos',             'fk' => 'sexo_id'],
            'cargo' => ['tabla' => 'cargos',            'fk' => 'cargo_id'],
            'edad' => ['tabla' => 'edades',            'fk' => 'edad_id'],
            'antiguedad' => ['tabla' => 'antiguedades',      'fk' => 'antiguedad_id'],
            'lugar_trabajo' => ['tabla' => 'lugares_trabajo',   'fk' => 'lugar_trabajo_id'],
            'grado_academico' => ['tabla' => 'grados_academicos', 'fk' => 'grado_academico_id'],
        ];

        if (! array_key_exists($this->campoComparativa, $mapaCampos)) {
            return ['categorias' => [], 'series' => []];
        }

        $config = $mapaCampos[$this->campoComparativa];
        $fk = $config['fk'];
        $tabla = $config['tabla'];

        $scoringService = app(ClimaScoringService::class);
        $dimensiones = Dimension::orderBy('orden')->get();
        $labelsDemograficos = DB::table($tabla)->orderBy('orden')->get();
        $categorias = $dimensiones->pluck('nombre')->toArray();
        $seriesData = [];

        // 1. Usar el servicio centralizado para obtener los puntajes agrupados
        $resultadosPorGrupo = $scoringService->scoresPorDimensionAgrupado($this->getBaseQuery(), $fk);

        // 2. Mapear al formato esperado por el gráfico
        foreach ($labelsDemograficos as $labelObj) {
            $scoresGrupo = $resultadosPorGrupo->get($labelObj->id);

            if ($scoresGrupo) {
                $seriesData[] = [
                    'name' => $labelObj->opcion,
                    'data' => $scoresGrupo->map(fn ($s) => $s['puntaje'] ?? 0.0)->toArray(),
                ];
            }
        }

        return ['categorias' => $categorias, 'series' => $seriesData];
    }

    #[Computed]
    public function comparativas(): array
    {
        return $this->getDatosComparativas();
    }

    public function render()
    {
        return view('livewire.admin.comparativas-demograficas', [
            'comparativas' => $this->comparativas,
        ]);
    }
}
