<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Respuesta extends Model
{
    protected $table = 'respuestas';

    protected $fillable = [
        'encuesta_id',
        'pregunta_id',
        'opcion_respuesta_id',
    ];

    public function encuesta(): BelongsTo
    {
        return $this->belongsTo(Encuesta::class);
    }

    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(Pregunta::class);
    }

    public function opcionRespuesta(): BelongsTo
    {
        return $this->belongsTo(OpcionRespuesta::class);
    }
}
