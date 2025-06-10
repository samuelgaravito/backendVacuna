<?php

namespace App\Services\Config;

use App\Models\GrupoRiesgo;
use Illuminate\Pagination\LengthAwarePaginator;

class GrupoRiesgoService
{
    /**
     * Obtiene todos los grupos de riesgo paginados
     */
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return GrupoRiesgo::orderBy('nombre')->paginate($perPage);
    }

    /**
     * Busca un grupo de riesgo por ID
     */
    public function findById(int $id): ?GrupoRiesgo
    {
        return GrupoRiesgo::find($id);
    }

    /**
     * Crea un nuevo grupo de riesgo
     */
    public function create(array $data): GrupoRiesgo
    {
        return GrupoRiesgo::create($data);
    }

    /**
     * Actualiza un grupo de riesgo existente
     */
    public function update(GrupoRiesgo $grupoRiesgo, array $data): GrupoRiesgo
    {
        $grupoRiesgo->update($data);
        return $grupoRiesgo;
    }

    /**
     * Elimina un grupo de riesgo
     */
    public function delete(GrupoRiesgo $grupoRiesgo): bool
    {
        return $grupoRiesgo->delete();
    }

    /**
     * Busca grupos de riesgo por nombre
     */
    public function search(string $searchTerm, int $perPage = 10): LengthAwarePaginator
    {
        return GrupoRiesgo::where('nombre', 'like', "%{$searchTerm}%")
                        ->orderBy('nombre')
                        ->paginate($perPage);
    }
}