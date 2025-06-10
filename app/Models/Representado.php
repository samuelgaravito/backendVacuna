<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Add these lines if they are not already there
use App\Models\Parroquia;
use App\Models\GrupoRiesgo;
use App\Models\Indigena;
use App\Models\User; // Assuming User is also in App\Models


class Representado extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cedula',
        'nombre_completo',
        'fecha_nacimiento',
        'sexo',
        'nacionalidad',
        'direccion',
        'parroquia_id',
        'grupo_riesgo_id',
        'indigena_id',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parroquia()
    {
        return $this->belongsTo(Parroquia::class);
    }

    public function grupoRiesgo()
    {
        return $this->belongsTo(GrupoRiesgo::class);
    }

    public function indigena()
    {
        return $this->belongsTo(Indigena::class);
    }
}