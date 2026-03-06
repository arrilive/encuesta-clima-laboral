<?php

namespace App\Livewire\Encuesta;

use App\Models\Dimension;
use App\Models\Encuesta;
use App\Models\OpcionRespuesta;
use App\Models\Pregunta;
use Illuminate\View\View;
use Livewire\Component;

class EncuestaBloque extends Component
{
    public Encuesta $encuesta;
    public int $dimensionActual;
    public int $dimensionId;
    public string $dimensionNombre = '';
    public int $totalDimensiones;

    // Escucha el evento que disparan los hijos
    protected $listeners = ['pregunta-respondida' => 'refrescarProgreso'];

    public function mount(string $token, int $dimension): void
    {
        $this->encuesta = Encuesta::whereIn('estado', ['asignado', 'en_progreso'])
            ->where('token', $token)
            ->firstOrFail();

        $this->dimensionActual  = $dimension;
        $this->totalDimensiones = Dimension::count();
        $dimensionModelo        = Dimension::where('orden', $dimension)->firstOrFail();
        $this->dimensionId      = $dimensionModelo->id;
        $this->dimensionNombre  = $dimensionModelo->nombre;
    }

    public array $preguntasSinRespuesta = [];

    // El hijo avisa que respondió — Livewire re-renderiza automáticamente
    public function refrescarProgreso($preguntaId = null): void
    {
        // Removemos la pregunta del arreglo de errores si la acaban de responder
        if ($preguntaId && in_array($preguntaId, $this->preguntasSinRespuesta)) {
            $this->preguntasSinRespuesta = array_diff($this->preguntasSinRespuesta, [$preguntaId]);
        }
    }

    public function siguienteBloque(): void
    {
        $preguntasBloqueId = Pregunta::whereHas('subdimension', fn ($q) =>
            $q->where('dimension_id', $this->dimensionId)
        )->pluck('id')->toArray();

        $respondidasId = $this->encuesta->respuestas()
            ->whereIn('pregunta_id', $preguntasBloqueId)
            ->pluck('pregunta_id')->toArray();

        $this->preguntasSinRespuesta = array_diff($preguntasBloqueId, $respondidasId);

        if (count($this->preguntasSinRespuesta) > 0) {
            $this->addError('bloque', 'Debes responder todas las preguntas antes de continuar.');
            
            // Emitir evento para hacer scroll a la primera pregunta sin respuesta
            $primeraFaltante = reset($this->preguntasSinRespuesta);
            $this->dispatch('scroll-to-pregunta', preguntaId: $primeraFaltante);
            
            return;
        }

        $this->redirect(route('encuesta.bloque.completado', [
            'token'     => $this->encuesta->token,
            'dimension' => $this->dimensionActual,
        ]));
    }

    public function calcularProgreso(): int
    {
        $totalPreguntas = Pregunta::whereHas('subdimension', fn($q) =>
            $q->where('dimension_id', $this->dimensionId)
        )->count();

        $respondidas = $this->encuesta->respuestas()
            ->whereHas('pregunta.subdimension', fn($q) =>
                $q->where('dimension_id', $this->dimensionId)
            )->count();

        if ($totalPreguntas === 0) return 0;

        return (int) (($respondidas / $totalPreguntas) * 100);
    }

    public function render(): View
    {
        $preguntas = Pregunta::with('subdimension')
            ->whereHas('subdimension', fn ($q) =>
                $q->where('dimension_id', $this->dimensionId)
            )
            ->join('subdimensiones', 'preguntas.subdimension_id', '=', 'subdimensiones.id')
            ->orderBy('subdimensiones.orden')
            ->orderBy('preguntas.orden')
            ->select('preguntas.*')
            ->get();

        $opciones = OpcionRespuesta::orderBy('orden')->get();

        return view('livewire.encuesta.encuesta-bloque', [
            'preguntasPorSubdimension' => $preguntas->groupBy('subdimension_id'),
            'opciones'                 => $opciones,
            'progreso'                 => $this->calcularProgreso(),
        ]);
    }
}