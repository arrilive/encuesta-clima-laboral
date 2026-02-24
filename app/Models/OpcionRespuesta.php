<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpcionRespuesta extends Model
{
    protected $table = 'opciones_respuesta';

    protected $fillable = [
        'opcion',
        'valor_numerico',
        'orden',
    ];

    public function respuestas(): HasMany
    {
        return $this->hasMany(Respuesta::class);
    }
}
