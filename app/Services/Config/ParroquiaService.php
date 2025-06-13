<?php

namespace App\Services\Config;

use App\Models\Parroquia; // Asegúrate de importar el modelo Parroquia
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection; // Si es necesario, para retornos de colecciones sin paginar

class ParroquiaService
{
    /**
     * Obtiene todas las parroquias paginadas.
     *
     * @param int $perPage Número de elementos por página.
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Parroquia::orderBy('nombre')->paginate($perPage);
    }

    /**
     * Obtiene parroquias por ID de municipio, paginadas.
     *
     * @param int $municipioId El ID del municipio.
     * @param int $perPage Número de elementos por página.
     * @return LengthAwarePaginator
     */
    public function getByMunicipio(int $municipioId, int $perPage = 15): LengthAwarePaginator
    {
        return Parroquia::where('municipio_id', $municipioId)
                        ->orderBy('nombre')
                        ->paginate($perPage);
    }

    /**
     * Encuentra una parroquia por su ID.
     *
     * @param int $id El ID de la parroquia.
     * @return Parroquia|null
     */
    public function findById(int $id): ?Parroquia
    {
        return Parroquia::find($id);
    }

    /**
     * Crea una nueva parroquia.
     *
     * @param array $data Los datos para crear la parroquia.
     * @return Parroquia
     */
    public function create(array $data): Parroquia
    {
        return Parroquia::create($data);
    }

    /**
     * Actualiza una parroquia existente.
     *
     * @param Parroquia $parroquia La instancia de la parroquia a actualizar.
     * @param array $data Los datos para actualizar la parroquia.
     * @return Parroquia
     */
    public function update(Parroquia $parroquia, array $data): Parroquia
    {
        $parroquia->update($data);
        return $parroquia;
    }

    /**
     * Elimina una parroquia.
     *
     * @param Parroquia $parroquia La instancia de la parroquia a eliminar.
     * @return bool|null
     */
    public function delete(Parroquia $parroquia): ?bool
    {
        // Se recomienda manejar las dependencias (ej. eliminar datos asociados si aplica)
        // antes de la eliminación si la base de datos no tiene restricciones ON DELETE CASCADE.
        return $parroquia->delete();
    }
}