<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'nombre',
        'password',
        'activa',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'activa' => 'boolean',
        ];
    }

    public function encuestas(): HasMany
    {
        return $this->hasMany(Encuesta::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
