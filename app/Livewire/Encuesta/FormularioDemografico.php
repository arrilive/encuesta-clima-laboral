<?php

namespace App\Livewire\Encuesta;

use App\Models\Antiguedad;
use App\Models\Cargo;
use App\Models\DatoDemografico;
use App\Models\Edad;
use App\Models\Encuesta;
use App\Models\GradoAcademico;
use App\Models\LugarTrabajo;
use App\Models\Sexo;
use Livewire\Component;

class FormularioDemografico extends Component
{
    public Encuesta $encuesta;

    public ?int $edad_id = null;
    public ?int $sexo_id = null;
    public ?int $antiguedad_id = null;
    public ?int $lugar_trabajo_id = null;
    public ?int $grado_academico_id = null;
    public ?int $cargo_id = null;

    public function mount(string $token): void
    {
        $this->encuesta = Encuesta::whereIn('estado', ['asignado', 'en_progreso'])
            ->where('token', $token)
            ->firstOrFail();

        if ($this->encuesta->estado === 'asignado') {
            $this->encuesta->marcarEnProgreso();
        }

        // Cargar datos previos si ya existen
        if ($dato = $this->encuesta->datoDemografico) {
            $this->edad_id          = $dato->edad_id;
            $this->sexo_id          = $dato->sexo_id;
            $this->antiguedad_id    = $dato->antiguedad_id;
            $this->lugar_trabajo_id = $dato->lugar_trabajo_id;
            $this->grado_academico_id = $dato->grado_academico_id;
            $this->cargo_id         = $dato->cargo_id;
        }
    }

    public function updated(string $field): void
    {
        $this->guardarProgreso();
    }

    public function guardarProgreso(): void
    {
        DatoDemografico::updateOrCreate(
            ['encuesta_id' => $this->encuesta->id],
            [
                'edad_id'           => $this->edad_id,
                'sexo_id'           => $this->sexo_id,
                'antiguedad_id'     => $this->antiguedad_id,
                'lugar_trabajo_id'  => $this->lugar_trabajo_id,
                'grado_academico_id' => $this->grado_academico_id,
                'cargo_id'          => $this->cargo_id,
            ]
        );
    }

    public function continuar(): void
    {
        $this->validate([
            'edad_id'            => ['required', 'exists:edades,id'],
            'sexo_id'            => ['required', 'exists:sexos,id'],
            'antiguedad_id'      => ['required', 'exists:antiguedades,id'],
            'lugar_trabajo_id'   => ['required', 'exists:lugares_trabajo,id'],
            'grado_academico_id' => ['required', 'exists:grados_academicos,id'],
            'cargo_id'           => ['required', 'exists:cargos,id'],
        ]);

        $this->guardarProgreso();

        // Temporal hasta Sprint 3
        session()->flash('message', 'Datos demográficos guardados. Sprint 3 pendiente.');
        $this->redirect(route('encuesta.bienvenida'));
    }

    public function render()
    {
        return view('livewire.encuesta.formulario-demografico', [
            'edades'          => Edad::orderBy('orden')->get(),
            'sexos'           => Sexo::orderBy('orden')->get(),
            'antiguedades'    => Antiguedad::orderBy('orden')->get(),
            'lugaresTrabajo'  => LugarTrabajo::orderBy('orden')->get(),
            'gradosAcademicos' => GradoAcademico::orderBy('orden')->get(),
            'cargos'          => Cargo::orderBy('orden')->get(),
        ]);
    }
}
