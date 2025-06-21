<?php

namespace App\Services\Representado;

use App\Models\Representado; // Importa el modelo Representado
use App\Models\Vacuna;       // Importa el modelo Vacuna
use App\Models\Registro;     // Importa el modelo Registro
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetTarjetaVacunacionService
{
    /**
     * Genera la tarjeta de vacunación para un representado específico.
     * Incluye todas las vacunas disponibles y el detalle de las dosis recibidas.
     *
     * @param int $representadoId El ID del representado.
     * @return JsonResponse
     */
    public function execute(int $representadoId): JsonResponse
    {
        try {
            // 1. Verificar si el representado existe. Si no, lanza una excepción 404.
            $representado = Representado::findOrFail($representadoId);

            // 2. Obtener todas las vacunas disponibles en el sistema.
            $todasLasVacunas = Vacuna::all();

            // 3. Obtener todos los registros de vacunación para este representado,
            // cargando la relación 'vacuna' y ordenando por ID de vacuna y dosis.
            $registrosVacunacion = Registro::where('representado_id', $representadoId)
                                            ->orderBy('vacuna_id')
                                            ->orderBy('dosis')
                                            ->get();

            $tarjetaVacunacion = [];

            // 4. Recorrer todas las vacunas y construir la tarjeta de vacunación.
            foreach ($todasLasVacunas as $vacuna) {
                $dosisRecibidas = [];

                // Filtrar los registros de vacunación que corresponden a la vacuna actual
                $registrosDeEstaVacuna = $registrosVacunacion->filter(function ($registro) use ($vacuna) {
                    return $registro->vacuna_id === $vacuna->id;
                });

                // Si hay registros para esta vacuna y este representado, añadir las dosis
                if ($registrosDeEstaVacuna->isNotEmpty()) {
                    foreach ($registrosDeEstaVacuna as $registro) {
                        $dosisRecibidas[] = [
                            'dosis'             => $registro->dosis,
                            // Formatear la fecha a un string legible
                            'fecha_colocacion'  => $registro->created_at->format('Y-m-d H:i:s'),
                        ];
                    }
                }

                // --- MODIFICACIÓN AQUÍ: Si no hay dosis recibidas, mostrar el mensaje específico con acentos y mayúsculas ---
                $dosisOutput = $dosisRecibidas;
                if (empty($dosisRecibidas)) {
                    $dosisOutput = "Aún no se ha suministrado esta vacuna en el paciente.";
                }

                // Añadir la información de la vacuna a la tarjeta
                $tarjetaVacunacion[] = [
                    'id'                        => $vacuna->id,
                    'nombre'                    => $vacuna->nombre,
                    'descripcion'               => $vacuna->descripcion,
                    'cantidad_dosis_requeridas' => $vacuna->cantidad, // La cantidad total de dosis para esta vacuna (según Vacuna model)
                    'dosis_recibidas'           => $dosisOutput, // Usar la variable modificada
                ];
            }

            // 5. Retornar la respuesta JSON con la tarjeta de vacunación
            return response()->json([
                'data'    => [
                    'representado'      => $representado->only(['id', 'nombre_completo', 'cedula']), // Información básica del representado
                    'tarjeta_vacunacion' => $tarjetaVacunacion
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            // Si el representado no es encontrado
            return response()->json([
                'success' => false,
                'message' => "El representado con ID: {$representadoId} no fue encontrado.",
                'error'   => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            // Capturar cualquier otro error inesperado
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al generar la tarjeta de vacunación: ' . $e->getMessage(),
            ], 500);
        }
    }
}
