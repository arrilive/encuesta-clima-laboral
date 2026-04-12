<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreguntaAbierta extends Model
{
    protected $table = 'preguntas_abiertas';

    protected $fillable = [
        'texto',
        'orden',
    ];

    public function respuestasAbiertas(): HasMany
    {
        return $this->hasMany(RespuestaAbierta::class);
    }
}
