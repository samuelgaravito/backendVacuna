<?php

namespace App\Services\Representado;

use App\Models\Representado;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ShowRepresentadoAdminService
{
    /**
     * Obtiene los detalles de un representado específico.
     *
     * @param int $representadoId El ID del representado a mostrar.
     * @return JsonResponse
     */
    public function execute(int $representadoId): JsonResponse
    {
        // La autorización de rol 'admin' se gestionará en el middleware de ruta.

        try {
            $representado = Representado::with(['user', 'parroquia.municipio.estado', 'grupoRiesgo', 'indigena'])
                                        ->find($representadoId);

            if (!$representado) {
                return response()->json(['message' => 'Representado no encontrado.'], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => $representado,
                'message' => 'Representado obtenido exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error obteniendo representado: ' . $e->getMessage(),
            ], 500);
        }
    }
}