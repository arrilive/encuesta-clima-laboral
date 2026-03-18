<?php

namespace App\Livewire\Encuesta;

use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\OpcionRespuesta;
use App\Models\Respuesta;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class PreguntaCerrada extends Component
{
    public Encuesta $encuesta;
    public Pregunta $pregunta;
    public ?int $opcionSeleccionada = null;

    #[Reactive]
    public bool $mostrarError = false;

    public function mount(): void
    {
        $respuesta = Respuesta::where('encuesta_id', $this->encuesta->id)
            ->where('pregunta_id', $this->pregunta->id)
            ->first();

        $this->opcionSeleccionada = $respuesta?->opcion_respuesta_id;
    }

    public function seleccionar(int $opcionId): void
    {
        $this->opcionSeleccionada = $opcionId;

        Respuesta::updateOrCreate(
            [
                'encuesta_id' => $this->encuesta->id,
                'pregunta_id' => $this->pregunta->id,
            ],
            [
                'opcion_respuesta_id' => $opcionId,
            ]
        );

        // Avisa al padre que se respondió una pregunta
        $this->dispatch('pregunta-respondida', preguntaId: $this->pregunta->id);
    }

    public function render(): View
    {
        return view('livewire.encuesta.pregunta-cerrada', [
            'opciones' => OpcionRespuesta::orderBy('orden')->get(),
        ]);
    }
}
