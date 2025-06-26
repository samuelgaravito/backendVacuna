<?php

namespace App\Services\Registro;

use App\Models\Registro;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShowRegistroService
{
    /**
     * Busca un registro por su ID y carga todas sus relaciones.
     * Maneja las excepciones y devuelve una JsonResponse.
     *
     * @param int $id El ID del registro que se desea buscar.
     * @return JsonResponse
     */
    public function execute(int $id): JsonResponse
    {
        try {
            // findOrFail() lanza una ModelNotFoundException si no encuentra el registro,
            // la cual es capturada en este mismo bloque.
            $registro = Registro::with([
                'representado' => fn ($query) => $query->with(['user', 'parroquia.municipio.estado', 'grupoRiesgo', 'indigena']),
                'personalSalud',
                'vacuna'
            ])->findOrFail($id);

            // Returns a successful JSON response with the detailed record data.
            return response()->json([
                'success' => true,
                'data'    => $registro,
                'message' => 'Detalles del registro obtenidos exitosamente.',
            ], 200);

        } catch (ModelNotFoundException $e) {
            // Handles the case where the record is not found and returns a 404 response.
            return response()->json([
                'success' => false,
                'message' => 'El registro con el ID ' . $id . ' no fue encontrado.',
                'data'    => null,
            ], 404);
        } catch (\Exception $e) {
            // Handles any other unexpected error and returns a 500 response.
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al obtener el registro: ' . $e->getMessage(),
            ], 500);
        }
    }
}