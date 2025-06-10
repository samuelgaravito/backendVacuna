<?php

namespace App\Services\Config;

use App\Models\Indigena;
use Illuminate\Pagination\LengthAwarePaginator;

class IndigenaService
{
    /**
     * Obtiene todos los registros indígenas paginados
     */
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return Indigena::orderBy('nombre')->paginate($perPage);
    }

    /**
     * Busca un registro indígena por ID
     */
    public function findById(int $id): ?Indigena
    {
        return Indigena::find($id);
    }

    /**
     * Crea un nuevo registro indígena
     */
    public function create(array $data): Indigena
    {
        return Indigena::create($data);
    }

    /**
     * Actualiza un registro indígena existente
     */
    public function update(Indigena $indigena, array $data): Indigena
    {
        $indigena->update($data);
        return $indigena;
    }

    /**
     * Elimina un registro indígena
     */
    public function delete(Indigena $indigena): bool
    {
        return $indigena->delete();
    }

    /**
     * Busca registros indígenas por nombre
     */
    public function search(string $searchTerm, int $perPage = 10): LengthAwarePaginator
    {
        return Indigena::where('nombre', 'like', "%{$searchTerm}%")
                      ->orderBy('nombre')
                      ->paginate($perPage);
    }
}