<?php

namespace App\Services\Representado;

use App\Models\Representado;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class IndexUserRepresentadosService
{
    /**
     * Obtiene una lista paginada de los representados asignados al usuario autenticado.
     *
     * @param Request $request La solicitud HTTP entrante, puede contener 'per_page'.
     * @return JsonResponse La respuesta JSON con los representados paginados.
     */
    public function execute(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Verifica que el usuario tenga el rol 'user'
        if (!$user->hasRole('representante')) { // Ajusta 'user' al nombre real de tu rol con ID 3
            return response()->json(['message' => 'Permission denied. You do not have the required role to view assigned represented individuals.'], 403);
        }

        try {
            $perPage = $request->input('per_page', 15);

            // Obtiene solo los representados que están asignados a este usuario
            $representados = Representado::where('user_id', $user->id)
                                        ->with(['parroquia.municipio.estado', 'grupoRiesgo', 'indigena']) // Carga las relaciones
                                        ->orderBy('nombre_completo')
                                        ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $representados->items(), // Los datos actuales de la página
                    'pagination' => [
                        'total' => $representados->total(),
                        'per_page' => $representados->perPage(),
                        'current_page' => $representados->currentPage(),
                        'last_page' => $representados->lastPage(),
                        'from' => $representados->firstItem(),
                        'to' => $representados->lastItem()
                    ]
                ],
                'message' => 'Assigned represented individuals retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving assigned represented individuals: ' . $e->getMessage(),
            ], 500);
        }
    }
}