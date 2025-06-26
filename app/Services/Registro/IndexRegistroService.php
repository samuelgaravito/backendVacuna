<?php

namespace App\Services\Registro;

use App\Models\Registro;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class IndexRegistroService
{
    /**
     * Obtiene una lista paginada de registros con datos esenciales.
     *
     * @param array $data Un array con 'fecha_inicio', 'fecha_fin' y 'per_page'.
     * @return JsonResponse
     * @throws ValidationException Si los datos no son válidos.
     */
    public function execute(array $data): JsonResponse
    {
        $validator = Validator::make($data, [
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'fecha_fin'    => 'required|date_format:Y-m-d|after_or_equal:fecha_inicio',
            'per_page'     => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validatedData = $validator->validated();

        try {
            $fechaInicio = Carbon::parse($validatedData['fecha_inicio'])->startOfDay();
            $fechaFin = Carbon::parse($validatedData['fecha_fin'])->endOfDay();
            $perPage = $validatedData['per_page'] ?? 15;

            // 2. Construir la consulta con solo los campos que necesitas
            $registros = Registro::whereBetween('created_at', [$fechaInicio, $fechaFin])
                                 ->select('id', 'dosis', 'created_at', 'representado_id', 'personal_salud_id', 'vacuna_id')
                                 ->with([
                                     'representado' => function ($query) {
                                         // Selecciona solo los campos de Representado que necesitas
                                         $query->select('id', 'cedula', 'nombre_completo', 'sexo', 'user_id', 'parroquia_id')
                                               ->with([
                                                    // Muestra los datos del usuario relacionado
                                                    'user:id,name,cedula',
                                                    // Carga la información de la ubicación
                                                    'parroquia.municipio.estado',
                                               ]);
                                     },
                                     // Selecciona solo los campos del personal de salud que necesitas
                                     'personalSalud' => function ($query) {
                                         $query->select('id', 'name', 'cedula');
                                     },
                                     // Selecciona solo los campos de la vacuna
                                     'vacuna:id,nombre',
                                 ])
                                 ->orderBy('created_at', 'desc')
                                 ->paginate($perPage);

            // 3. Devolver la respuesta JSON
            return response()->json([
                'success' => true,
                'data'    => [
                    'items'      => $registros->items(),
                    'pagination' => [
                        'total'        => $registros->total(),
                        'per_page'     => $registros->perPage(),
                        'current_page' => $registros->currentPage(),
                        'last_page'    => $registros->lastPage(),
                        'from'         => $registros->firstItem(),
                        'to'           => $registros->lastItem(),
                    ],
                ],
                'message' => 'Registros obtenidos exitosamente para el rango de fechas especificado.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al obtener los registros: ' . $e->getMessage(),
            ], 500);
        }
    }
}