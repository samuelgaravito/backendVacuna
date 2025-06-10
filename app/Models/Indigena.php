<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Importa HasMany si vas a usarlo

class Indigena extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tipo' 
    ];


    /**
     * Define la relación uno a muchos con Representado.
     * Un grupo indígena puede tener muchos representados.
     */
    public function representados(): HasMany
    {
        return $this->hasMany(Representado::class, 'indigena_id');
    }
}