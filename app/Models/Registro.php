<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'registros'; // Explicitly define table name if it's not the plural of the model name

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'representado_id',
        'personal_salud_id',
        'vacuna_id',
        'dosis',
    ];

    public function representado()
    {
        return $this->belongsTo(Representado::class);
    }


    public function personalSalud()
    {

        return $this->belongsTo(User::class, 'personal_salud_id');
    }


    public function vacuna()
    {
        return $this->belongsTo(Vacuna::class);
    }
}