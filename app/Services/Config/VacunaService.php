<?php

namespace App\Services\Config;

use App\Models\Vacuna;
use Illuminate\Pagination\LengthAwarePaginator;

class VacunaService
{
    /**
     * Obtiene todas las vacunas paginadas
     * 
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return Vacuna::orderBy('nombre')->paginate($perPage);
    }

    /**
     * Busca una vacuna por ID
     * 
     * @param int $id
     * @return Vacuna|null
     */
    public function findById(int $id): ?Vacuna
    {
        return Vacuna::find($id);
    }

    /**
     * Crea una nueva vacuna
     * 
     * @param array $data
     * @return Vacuna
     */
    public function create(array $data): Vacuna
    {
        return Vacuna::create($data);
    }

    /**
     * Actualiza una vacuna existente
     * 
     * @param Vacuna $vacuna
     * @param array $data
     * @return Vacuna
     */
    public function update(Vacuna $vacuna, array $data): Vacuna
    {
        $vacuna->update($data);
        return $vacuna;
    }

    /**
     * Elimina una vacuna
     * 
     * @param Vacuna $vacuna
     * @return bool|null
     */
    public function delete(Vacuna $vacuna): ?bool
    {
        return $vacuna->delete();
    }

    /**
     * Busca vacunas por nombre
     * 
     * @param string $search
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $search, int $perPage = 10): LengthAwarePaginator
    {
        return Vacuna::where('nombre', 'like', "%{$search}%")
                    ->orderBy('nombre')
                    ->paginate($perPage);
    }
}