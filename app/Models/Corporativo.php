<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Corporativo extends Model
{
    /** @use HasFactory<\Database\Factories\CorporativoFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function empresas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Empresa::class);
    }
}
