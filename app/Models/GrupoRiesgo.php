<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Necesitas importar HasMany para definir la relación

class GrupoRiesgo extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre', // Asumo que el grupo de riesgo tiene un campo 'nombre'
        // Agrega aquí cualquier otro campo que tu tabla 'grupos_riesgo' tenga
    ];

    /**
     * Define la relación uno a muchos con Representado.
     * Un Grupo de Riesgo puede tener muchos representados asociados.
     */
    public function representados(): HasMany
    {
        return $this->hasMany(Representado::class, 'grupo_riesgo_id');
    }
}