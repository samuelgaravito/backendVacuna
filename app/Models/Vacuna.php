<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacuna extends Model
{
    use HasFactory;

    protected $table = 'vacunas'; // Nombre de la tabla

    protected $fillable = [
        'nombre',
        'descripcion',
        'cantidad',

    ];


}