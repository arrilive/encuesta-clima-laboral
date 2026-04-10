<?php

namespace App\Livewire\Admin;

use App\Models\Dimension;
use App\Models\Respuesta;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class ComparativasDemograficas extends Component
{
    #[Reactive] public string $filtroEdadId = '';
    #[Reactive] public string $filtroSexoId = '';
    #[Reactive] public string $filtroCargoId = '';
    #[Reactive] public string $filtroLugarTrabajoId = '';
    #[Reactive] public string $filtroGradoAcademicoId = '';
    #[Reactive] public string $filtroAntiguedadId = '';
    #[Reactive] public string $filtroEmpresaId = '';

    public string $campoComparativa = 'sexo';

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

    private function getDatosComparativas(): array
    {
        $mapaCampos = [
            'sexo'            => ['tabla' => 'sexos',             'fk' => 'sexo_id'],
            'cargo'           => ['tabla' => 'cargos',            'fk' => 'cargo_id'],
            'edad'            => ['tabla' => 'edades',            'fk' => 'edad_id'],
            'antiguedad'      => ['tabla' => 'antiguedades',      'fk' => 'antiguedad_id'],
            'lugar_trabajo'   => ['tabla' => 'lugares_trabajo',   'fk' => 'lugar_trabajo_id'],
            'grado_academico' => ['tabla' => 'grados_academicos', 'fk' => 'grado_academico_id'],
        ];

        if (!array_key_exists($this->campoComparativa, $mapaCampos)) {
            return ['categorias' => [], 'series' => []];
        }

        $config = $mapaCampos[$this->campoComparativa];
        $fk     = $config['fk'];
        $tabla  = $config['tabla'];

        // 1 query agrupada en lugar del doble loop
        $resultados = (clone $this->getBaseQuery())
            ->whereHas('opcionRespuesta', fn($q) =>
                $q->where('valor_numerico', '!=', 0)
            )
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->join('encuestas as enc_join', 'respuestas.encuesta_id', '=', 'enc_join.id')
            ->join('datos_demograficos', 'enc_join.id', '=', 'datos_demograficos.encuesta_id')
            ->join('preguntas', 'respuestas.pregunta_id', '=', 'preguntas.id')
            ->join('subdimensiones', 'preguntas.subdimension_id', '=', 'subdimensiones.id')
            ->selectRaw("subdimensiones.dimension_id, datos_demograficos.{$fk} as grupo_id, AVG(opciones_respuesta.valor_numerico) as promedio")
            ->groupBy('subdimensiones.dimension_id', "datos_demograficos.{$fk}")
            ->get()
            ->groupBy('grupo_id');

        $dimensiones        = Dimension::orderBy('orden')->get();
        $labelsDemograficos = DB::table($tabla)->orderBy('orden')->get();
        $categorias         = $dimensiones->pluck('nombre')->toArray();
        $seriesData         = [];

        foreach ($labelsDemograficos as $labelObj) {
            $grupoResultados      = $resultados->get($labelObj->id, collect());
            $puntajesPorDimension = $grupoResultados->keyBy('dimension_id');
            $puntajes             = [];
            $hasData              = false;

            foreach ($dimensiones as $dimension) {
                $row = $puntajesPorDimension->get($dimension->id);

                if ($row && $row->promedio !== null) {
                    $puntajes[] = round((($row->promedio - 1) / 2) * 100, 1);
                    $hasData    = true;
                } else {
                    $puntajes[] = 0.0;
                }
            }

            if ($hasData) {
                $seriesData[] = ['name' => $labelObj->opcion, 'data' => $puntajes];
            }
        }

        return ['categorias' => $categorias, 'series' => $seriesData];
    }

    public function getComparativasProperty(): array
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
