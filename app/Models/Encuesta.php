<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Encuesta extends Model
{
    use HasFactory;

    protected $table = 'encuestas';

    protected $fillable = [
        'lote_id',
        'token',
        'empresa_id',
        'estado',
        'fecha_asignacion',
        'fecha_completada',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_completada' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relaciones
    // -------------------------------------------------------------------------

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(TokenLote::class, 'lote_id');
    }

    public function datoDemografico(): HasOne
    {
        return $this->hasOne(DatoDemografico::class);
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(Respuesta::class);
    }

    public function respuestasAbiertas(): HasMany
    {
        return $this->hasMany(RespuestaAbierta::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeCompletadas(Builder $query): Builder
    {
        return $query->where('estado', 'completado');
    }

    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('estado', 'disponible');
    }

    public function scopeEnRiesgo(Builder $query, int $dias = 7): Builder
    {
        return $query->where('estado', 'asignado')
                     ->where('fecha_asignacion', '<', now()->subDays($dias));
    }

    // -------------------------------------------------------------------------
    // Métodos de negocio
    // -------------------------------------------------------------------------

    public function marcarEnProgreso(): bool
    {
        return $this->update(['estado' => 'en_progreso']);
    }

    public function asignar(): void
    {
        $this->update([
            'estado'           => 'asignado',
            'fecha_asignacion' => now(),
        ]);
    }

    public function marcarComoCompletada(): void
    {
        $this->update([
            'estado'           => 'completado',
            'fecha_completada' => now(),
        ]);
    }
}
