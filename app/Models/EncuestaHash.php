<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncuestaHash extends Model
{
    use HasFactory;

    protected $table = 'encuesta_hashes_usados';

    const UPDATED_AT = null;

    protected $fillable = [
        'phone_hash',
        'lote_id',
    ];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }
}
