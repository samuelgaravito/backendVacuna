<?php

namespace App\Services\Config;

use App\Models\Estado;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EstadoService
{
    /**
     * Obtiene todos los estados paginados.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return Estado::orderBy('nombre')->paginate($perPage);
    }

    /**
     * Encuentra un estado por su ID.
     *
     * @param int $id
     * @return Estado|null
     */
    public function findById(int $id): ?Estado
    {
        return Estado::find($id);
    }

    /**
     * Crea un nuevo estado.
     *
     * @param array $data
     * @return Estado
     */
    public function create(array $data): Estado
    {
        return Estado::create($data);
    }

    /**
     * Actualiza un estado existente.
     *
     * @param Estado $estado
     * @param array $data
     * @return Estado
     */
    public function update(Estado $estado, array $data): Estado
    {
        $estado->update($data);
        return $estado;
    }

    /**
     * Elimina un estado.
     *
     * @param Estado $estado
     * @return bool|null
     */
    public function delete(Estado $estado): ?bool
    {
        return $estado->delete();
    }
}