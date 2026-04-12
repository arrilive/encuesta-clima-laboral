<?php

namespace App\Livewire\Encuesta;

use App\Models\Encuesta;
use App\Models\PreguntaAbierta;
use App\Models\RespuestaAbierta;
use Illuminate\View\View;
use Livewire\Component;

class PreguntasAbiertas extends Component
{
    public Encuesta $encuesta;

    public array $respuestas = [];

    public function mount(string $token): void
    {
        $this->encuesta = Encuesta::where('estado', 'en_progreso')
            ->where('token', $token)
            ->firstOrFail();

        // Cargar respuestas abiertas existentes si ya había respondido antes
        $this->encuesta->respuestasAbiertas->each(function ($respuesta) {
            $this->respuestas[$respuesta->pregunta_abierta_id] = $respuesta->texto;
        });
    }

    public function updatedRespuestas(string $value, string $key): void
    {
        if (strlen($value) > 300) {
            return;
        }

        RespuestaAbierta::updateOrCreate(
            [
                'encuesta_id' => $this->encuesta->id,
                'pregunta_abierta_id' => (int) $key,
            ],
            ['texto' => $value]
        );
    }

    public function finalizar(): void
    {
        $this->encuesta->marcarComoCompletada();
        $this->redirect(route('encuesta.gracias', $this->encuesta->token));
    }

    public function render(): View
    {
        return view('livewire.encuesta.preguntas-abiertas', [
            'preguntas' => PreguntaAbierta::orderBy('orden')->get(),
        ]);
    }
}
