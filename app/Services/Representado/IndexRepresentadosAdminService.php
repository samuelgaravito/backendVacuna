<?php

namespace App\Services\Representado;

use App\Models\Representado;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder; // Importar para usar whereHas

class IndexRepresentadosAdminService
{
    /**
     * Obtiene una lista paginada de todos los representados.
     * Permite filtrar por user_id.
     *
     * @param Request $request La solicitud HTTP entrante, puede contener 'per_page' y 'user_id'.
     * @return JsonResponse La respuesta JSON con los representados paginados.
     */
    public function execute(Request $request): JsonResponse
    {
        // La autorización de rol 'admin' se gestionará en el middleware de ruta.

        try {
            $perPage = $request->input('per_page', 15);
            $userId = $request->input('user_id'); // Parámetro opcional para filtrar por usuario

            $query = Representado::query();

            // Si se proporciona un user_id, filtrar los representados por ese usuario
            if ($userId) {
                $query->where('user_id', $userId);
            }

            $representados = $query->with(['user', 'parroquia.municipio.estado', 'grupoRiesgo', 'indigena'])
                                   ->orderBy('nombre_completo')
                                   ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $representados->items(),
                    'pagination' => [
                        'total'        => $representados->total(),
                        'per_page'     => $representados->perPage(),
                        'current_page' => $representados->currentPage(),
                        'last_page'    => $representados->lastPage(),
                        'from'         => $representados->firstItem(),
                        'to'           => $representados->lastItem()
                    ]
                ],
                'message' => 'Representados obtenidos exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error obteniendo representados: ' . $e->getMessage(),
            ], 500);
        }
    }
}