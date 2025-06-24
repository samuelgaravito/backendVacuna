<?php

namespace App\Services\Representado;

use App\Models\Representado;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class ShowRepresentadoService
{
    /**
     * Muestra los detalles de un representado específico, asegurando que pertenezca al usuario autenticado.
     *
     * @param int $representadoId El ID del representado a mostrar.
     * @return JsonResponse
     */
    public function execute(int $representadoId): JsonResponse
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // 1. Verificar si el usuario está autenticado (aunque el middleware de ruta ya lo haría, es una buena práctica)
        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        // 2. Verificar que el usuario tenga el rol 'representante'
        if (!$user->hasRole('representante')) {
            return response()->json(['message' => 'Permiso denegado. Solo un representante puede ver sus propios representados.'], 403);
        }

        try {
            // 3. Buscar el representado por ID
            $representado = Representado::findOrFail($representadoId);

            // 4. Verificar que el representado pertenezca al usuario autenticado
            if ($representado->user_id !== $user->id) {
                return response()->json(['message' => 'Acceso denegado. Este representado no pertenece a su cuenta.'], 403);
            }

            // 5. Cargar relaciones si es necesario para la respuesta
            $representado->load(['user', 'parroquia', 'grupoRiesgo', 'indigena']);

            // 6. Retornar los datos del representado
            return response()->json([
                'success' => true,
                'message' => 'Representado obtenido exitosamente.',
                'data'    => $representado
            ], 200);

        } catch (ModelNotFoundException $e) {
            // Capturar si el representado no fue encontrado
            return response()->json([
                'success' => false,
                'message' => 'Representado no encontrado.',
                'error'   => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            // Capturar cualquier otro error inesperado
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al obtener el representado: ' . $e->getMessage(),
            ], 500);
        }
    }
}
