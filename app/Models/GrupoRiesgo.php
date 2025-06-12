<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoRiesgo extends Model
{
    use HasFactory;

        protected $table = 'grupos_riesgo'; // Nombre de la tabla

    protected $fillable = [
        'nombre',
        'tipo',
    ];

    public function representados(): HasMany
    {
        return $this->hasMany(Representado::class, 'grupo_riesgo_id');
    }
}