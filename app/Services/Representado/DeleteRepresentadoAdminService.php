<?php

namespace App\Services\Representado;

use App\Models\Representado;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DeleteRepresentadoAdminService
{
    /**
     * Permite a un administrador eliminar un representado.
     *
     * @param int $representadoId El ID del representado a eliminar.
     * @return JsonResponse
     */
    public function execute(int $representadoId): JsonResponse
    {
        // La autorización de rol 'admin' se gestionará en el middleware de ruta.

        try {
            $representado = Representado::find($representadoId);

            if (!$representado) {
                return response()->json(['message' => 'Representado no encontrado.'], 404);
            }

            $representado->delete();

            return response()->json([
                'success' => true,
                'message' => 'Representado eliminado exitosamente.'
            ], 200); // 200 OK para eliminación exitosa (o 204 No Content)

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error eliminando representado: ' . $e->getMessage(),
            ], 500);
        }
    }
}