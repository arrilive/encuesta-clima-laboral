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

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
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

    public function scopeEnAdvertencia(Builder $query, ?int $dias = null, ?int $diasRiesgo = null): Builder
    {
        $dias = $dias ?? config('encuesta.tokens.dias_advertencia');
        $diasRiesgo = $diasRiesgo ?? config('encuesta.tokens.dias_riesgo');

        return $query->where('estado', 'asignado')
            ->where('fecha_asignacion', '<', now()->subDays($dias))
            ->where('fecha_asignacion', '>=', now()->subDays($diasRiesgo));
    }

    public function scopeEnRiesgo(Builder $query, ?int $dias = null): Builder
    {
        $dias = $dias ?? config('encuesta.tokens.dias_riesgo');

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

    public function marcarComoCompletada(): void
    {
        $this->update([
            'estado' => 'completado',
            'fecha_completada' => now(),
        ]);
    }
}
