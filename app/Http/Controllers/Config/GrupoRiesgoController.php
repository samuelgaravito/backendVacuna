<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\GrupoRiesgo; // Importa el modelo directamente
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GrupoRiesgoController extends Controller
{
    public function __construct()
    {
        // No se necesita inyectar el servicio aquí
    }

    /**
     * Muestra una lista de grupos de riesgo o busca por término.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = 10; // Número de elementos por página

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $grupos = GrupoRiesgo::where('nombre', 'like', "%{$searchTerm}%")
                                ->orderBy('nombre')
                                ->paginate($perPage);
        } else {
            $grupos = GrupoRiesgo::orderBy('nombre')->paginate($perPage);
        }

        return response()->json($grupos);
    }

    /**
     * Almacena un nuevo grupo de riesgo.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|max:255'
        ]);

        // Crea el grupo de riesgo directamente desde el modelo
        $grupo = GrupoRiesgo::create($validated);

        return response()->json($grupo, 201); // 201 Created
    }

    /**
     * Muestra un grupo de riesgo específico.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        // Busca el grupo de riesgo directamente desde el modelo
        $grupo = GrupoRiesgo::find($id);

        if (!$grupo) {
            return response()->json([
                'message' => 'Grupo de riesgo no encontrado'
            ], 404);
        }

        return response()->json($grupo);
    }

    /**
     * Actualiza un grupo de riesgo existente.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Busca el grupo de riesgo directamente desde el modelo
        $grupo = GrupoRiesgo::find($id);

        if (!$grupo) {
            return response()->json([
                'message' => 'Grupo de riesgo no encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'tipo' => 'sometimes|string|max:255'
        ]);

        // Actualiza el grupo de riesgo directamente desde el modelo
        $grupo->update($validated);

        return response()->json($grupo);
    }

    /**
     * Elimina un grupo de riesgo.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        // Busca el grupo de riesgo directamente desde el modelo
        $grupo = GrupoRiesgo::find($id);

        if (!$grupo) {
            return response()->json([
                'message' => 'Grupo de riesgo no encontrado'
            ], 404);
        }

        // Elimina el grupo de riesgo directamente desde el modelo
        $grupo->delete();

        return response()->json([
            'message' => 'Grupo de riesgo eliminado correctamente'
        ], 200); // 200 OK es apropiado para una eliminación exitosa
    }
}