<?php

namespace App\Http\Controllers\Registro; // Your current namespace

use App\Http\Controllers\Controller; // <--- Add this line
use App\Services\Registro\BuscarPersonaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RegistroController extends Controller // Now it knows what "Controller" is
{
    /**
     * Busca información de representado(s) por número de cédula.
     *
     * @param Request $request La solicitud HTTP.
     * @param BuscarPersonaService $buscarPersonaService El servicio para realizar la búsqueda.
     * @return JsonResponse La respuesta JSON con los resultados.
     */
    public function searchByCedula(Request $request, BuscarPersonaService $buscarPersonaService): JsonResponse
    {
        try {
            // El controlador ahora solo delega la validación y la lógica al servicio.
            $result = $buscarPersonaService->search($request); // Pasa el objeto Request completo

            if ($result) {
                return response()->json([
                    'success' => true,
                    'type'    => $result['type'],
                    'message' => $result['message'],
                    'data'    => $result['data']
                ], 200);

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'La cédula no fue encontrada en el sistema.',
                    'data'    => null
                ], 404);
            }

        } catch (ValidationException $e) {
            // El controlador captura la excepción de validación lanzada por el servicio.
            return response()->json([
                'success' => false,
                'message' => 'Error de validación en la solicitud.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Manejo de cualquier otro error inesperado.
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al buscar la cédula: ' . $e->getMessage(),
            ], 500);
        }
    }
}