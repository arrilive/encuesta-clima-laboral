<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Dimension extends Model
{
    protected $table = 'dimensiones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
    ];

    public function subdimensiones(): HasMany
    {
        return $this->hasMany(Subdimension::class);
    }

    public function preguntas(): HasManyThrough
    {
        return $this->hasManyThrough(Pregunta::class, Subdimension::class);
    }
}
