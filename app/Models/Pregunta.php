<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pregunta extends Model
{
    protected $table = 'preguntas';

    protected $fillable = [
        'subdimension_id',
        'texto',
        'orden',
    ];

    public function subdimension(): BelongsTo
    {
        return $this->belongsTo(Subdimension::class);
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(Respuesta::class);
    }
}
