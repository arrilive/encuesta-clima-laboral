<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpVerificacion extends Model
{
    use HasFactory;

    protected $table = 'otp_verificaciones';

    const UPDATED_AT = null;

    protected $fillable = [
        'numero_e164',
        'otp_hash',
        'lote_id',
        'empresa_id',
        'intentos',
        'expira_en',
    ];

    protected function casts(): array
    {
        return [
            'expira_en' => 'datetime',
            'intentos' => 'integer',
        ];
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function estaVigente(): bool
    {
        return $this->expira_en->isFuture();
    }

    public function agotaronIntentos(): bool
    {
        return $this->intentos >= 3;
    }
}
