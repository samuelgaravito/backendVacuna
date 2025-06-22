<?php

namespace App\Services\Config;

use App\Models\Vacuna; // Importa el modelo Vacuna
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class VacunaService
{
    /**
     * Obtiene todas las vacunas de la base de datos.
     *
     * @return JsonResponse
     */
    public function getAll(): JsonResponse
    {
        $vacunas = Vacuna::all();

        if ($vacunas->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No se encontraron vacunas.',
                'data' => []
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vacunas obtenidas exitosamente.',
            'data' => $vacunas
        ], 200);
    }

    /**
     * Busca vacunas por nombre.
     *
     * @param string $searchTerm El término de búsqueda.
     * @return JsonResponse
     */
    public function search(string $searchTerm): JsonResponse
    {
        $vacunas = Vacuna::where('nombre', 'LIKE', '%' . $searchTerm . '%')->get();

        if ($vacunas->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => "No se encontraron vacunas que coincidan con '{$searchTerm}'.",
                'data' => []
            ], 200);
        }

        return response()->json([
            'data' => $vacunas
        ], 200);
    }

    /**
     * Crea una nueva vacuna.
     *
     * @param array $data Los datos validados para la nueva vacuna.
     * @return JsonResponse
     */
    public function create(array $data): JsonResponse
    {
        $vacuna = Vacuna::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Vacuna creada exitosamente.',
            'data' => $vacuna
        ], 201);
    }

    /**
     * Busca una vacuna por su ID.
     *
     * @param int $id El ID de la vacuna.
     * @return Vacuna|null
     */
    public function findById(int $id): ?Vacuna
    {
        return Vacuna::find($id);
    }

    /**
     * Actualiza una vacuna existente.
     *
     * @param Vacuna $vacuna La instancia del modelo Vacuna a actualizar.
     * @param array $data Los datos validados para la actualización.
     * @return JsonResponse
     */
    public function update(Vacuna $vacuna, array $data): JsonResponse
    {
        $vacuna->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Vacuna actualizada exitosamente.',
            'data' => $vacuna
        ], 200);
    }

    /**
     * Elimina una vacuna.
     *
     * @param Vacuna $vacuna La instancia del modelo Vacuna a eliminar.
     * @return JsonResponse
     */
    public function delete(Vacuna $vacuna): JsonResponse
    {
        $vacuna->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vacuna eliminada correctamente.'
        ], 200);
    }
}
