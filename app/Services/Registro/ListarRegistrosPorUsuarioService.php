<?php

namespace App\Services\Registro;

use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // Lo mantendremos para otros posibles errores

class ListarRegistrosPorUsuarioService
{
    /**
     * Recupera una lista de registros de vacunas creados por el usuario autenticado en un rango de fechas.
     * El usuario debe tener el rol 'personal_de_salud'.
     *
     * @param Request $request
     * @param string $fechaDesde  // <-- Parámetro de la ruta
     * @param string $fechaHasta  // <-- Parámetro de la ruta
     * @return JsonResponse
     */
    public function execute(Request $request, string $fechaDesde, string $fechaHasta): JsonResponse
    {
        // 1. Obtener y verificar el usuario autenticado
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autenticado.'], 401);
        }

        // 2. Verificar el rol del usuario
        if (!$user->hasRole('personal_de_salud')) {
            return response()->json(['success' => false, 'message' => 'Permiso denegado. No tienes el rol requerido.'], 403);
        }

        try {
            // 3. Validar la lógica del rango de fechas
            // Como las fechas vienen de la ruta, no hay que validar si existen, solo su formato y lógica.
            // Una simple comprobación es suficiente para la lógica 'antes_o_igual'
            if (strtotime($fechaDesde) > strtotime($fechaHasta)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación en los parámetros de la fecha.',
                    'errors'  => ['fecha_desde' => ['La fecha de inicio debe ser anterior o igual a la fecha de fin.']],
                ], 422);
            }

            // 4. Consultar la base de datos
            $registros = Registro::with(['vacuna', 'representado', 'personalSalud'])
                ->where('personal_salud_id', $user->id)
                ->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->orderBy('created_at', 'desc')
                ->get();

            // 5. Devolver la respuesta con los registros
            return response()->json([
                'success' => true,
                'data'    => [
                    'items'           => $registros,
                    'total_registros' => $registros->count(),
                ],
                'message' => 'Registros de vacunas recuperados exitosamente para el rango de fechas especificado.',
            ], 200);

        } catch (\Exception $e) {
            // Manejar cualquier otro error inesperado (ej. formato de fecha incorrecto)
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al recuperar los registros: ' . $e->getMessage(),
            ], 500);
        }
    }
}