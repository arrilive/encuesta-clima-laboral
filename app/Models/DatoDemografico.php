<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatoDemografico extends Model
{
    protected $table = 'datos_demograficos';

    protected $fillable = [
        'encuesta_id',
        'antiguedad_id',
        'edad_id',
        'lugar_trabajo_id',
        'sexo_id',
        'grado_academico_id',
        'cargo_id',
    ];

    public function encuesta(): BelongsTo
    {
        return $this->belongsTo(Encuesta::class);
    }

    public function antiguedad(): BelongsTo
    {
        return $this->belongsTo(Antiguedad::class);
    }

    public function edad(): BelongsTo
    {
        return $this->belongsTo(Edad::class);
    }

    public function lugarTrabajo(): BelongsTo
    {
        return $this->belongsTo(LugarTrabajo::class);
    }

    public function sexo(): BelongsTo
    {
        return $this->belongsTo(Sexo::class);
    }

    public function gradoAcademico(): BelongsTo
    {
        return $this->belongsTo(GradoAcademico::class);
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class);
    }
}
