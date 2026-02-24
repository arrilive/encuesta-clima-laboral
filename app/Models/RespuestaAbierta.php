<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RespuestaAbierta extends Model
{
    protected $table = 'respuestas_abiertas';

    protected $fillable = [
        'encuesta_id',
        'pregunta_abierta_id',
        'texto',
    ];

    public function encuesta(): BelongsTo
    {
        return $this->belongsTo(Encuesta::class);
    }

    public function preguntaAbierta(): BelongsTo
    {
        return $this->belongsTo(PreguntaAbierta::class);
    }
}
