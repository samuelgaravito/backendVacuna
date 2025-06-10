<?php

namespace App\Services\Config;

use App\Models\Municipio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MunicipioService
{
    /**
     * Obtiene todos los municipios paginados, con su estado relacionado.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Municipio::with('estado')->orderBy('nombre')->paginate($perPage);
    }

    /**
     * Encuentra un municipio por su ID, con su estado relacionado.
     *
     * @param int $id
     * @return Municipio|null
     */
    public function findById(int $id): ?Municipio
    {
        return Municipio::with('estado')->find($id);
    }

    /**
     * Crea un nuevo municipio.
     *
     * @param array $data
     * @return Municipio
     */
    public function create(array $data): Municipio
    {
        return Municipio::create($data);
    }

    /**
     * Actualiza un municipio existente.
     *
     * @param Municipio $municipio
     * @param array $data
     * @return Municipio
     */
    public function update(Municipio $municipio, array $data): Municipio
    {
        $municipio->update($data);
        return $municipio;
    }

    /**
     * Elimina un municipio.
     *
     * @param Municipio $municipio
     * @return bool|null
     */
    public function delete(Municipio $municipio): ?bool
    {
        return $municipio->delete();
    }

    /**
     * Obtiene los municipios de un estado específico.
     *
     * @param int $estadoId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByEstado(int $estadoId, int $perPage = 15): LengthAwarePaginator
    {
        return Municipio::where('estado_id', $estadoId)
                        ->orderBy('nombre')
                        ->paginate($perPage);
    }
}