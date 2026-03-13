<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TokenLote extends Model
{
    protected $fillable = [
        'empresa_id',
        'user_id',
        'cantidad',
        'nombre',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function encuestas(): HasMany
    {
        return $this->hasMany(Encuesta::class, 'lote_id');
    }
}