<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importa BelongsTo para la relación con Municipio
use Illuminate\Database\Eloquent\Relations\HasMany;   // Importa HasMany para la relación con Representado

class Parroquia extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'municipio_id', // Asegúrate de que esta columna exista en tu tabla 'parroquias'
    ];

    /**
     * Define la relación: una parroquia pertenece a un municipio.
     */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Define la relación: una parroquia puede tener muchos representados.
     */
    public function representados(): HasMany
    {
        return $this->hasMany(Representado::class, 'parroquia_id');
    }
}