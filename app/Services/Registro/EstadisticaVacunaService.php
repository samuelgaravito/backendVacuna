<?php

namespace App\Services\Registro;

use App\Models\Registro;
use App\Models\Vacuna;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EstadisticaVacunaService
{
    /**
     * Calcula estadísticas de vacunas aplicadas por rango de fechas y género.
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return JsonResponse
     */
    public function execute(string $fechaInicio, string $fechaFin): JsonResponse
    {
        try {
            $start = Carbon::parse($fechaInicio)->startOfDay();
            $end = Carbon::parse($fechaFin)->endOfDay();

            // 1. Obtener todas las vacunas para la lista de referencia
            $vacunas = Vacuna::select('id', 'nombre')->get();

            // 2. Obtener el conteo de registros por vacuna y género
            $registros = Registro::join('vacunas', 'registros.vacuna_id', '=', 'vacunas.id')
                ->join('representados', 'registros.representado_id', '=', 'representados.id')
                ->select(
                    'vacunas.id as vacuna_id',
                    'vacunas.nombre as vacuna_nombre',
                    'representados.sexo',
                    DB::raw('count(registros.id) as total_registros')
                )
                ->whereBetween('registros.created_at', [$start, $end])
                ->groupBy('vacunas.id', 'vacunas.nombre', 'representados.sexo')
                ->orderBy('vacunas.nombre')
                ->get();

            // 3. Procesar los resultados para agruparlos por vacuna
            $estadisticas = [];
            foreach ($vacunas as $vacuna) {
                // Inicializa el conteo de cada vacuna en 0
                $estadisticas[$vacuna->id] = [
                    'vacuna_id' => $vacuna->id,
                    'vacuna_nombre' => $vacuna->nombre,
                    'total_aplicadas' => 0,
                    'genero' => [
                        'M' => 0,
                        'F' => 0,
                    ],
                ];
            }

            // 4. Llenar los datos de las estadísticas con los resultados de la consulta
            foreach ($registros as $registro) {
                $vacunaId = $registro->vacuna_id;
                $genero = $registro->sexo;
                $total = $registro->total_registros;

                // Suma el total general de la vacuna
                $estadisticas[$vacunaId]['total_aplicadas'] += $total;

                // Suma por género
                if (isset($estadisticas[$vacunaId]['genero'][$genero])) {
                    $estadisticas[$vacunaId]['genero'][$genero] += $total;
                }
            }

            // 5. Convertir el array asociativo en una lista para la respuesta JSON
            $resultadosFinales = array_values($estadisticas);

            return response()->json([
                'success' => true,
                'data' => $resultadosFinales,
                'message' => 'Estadísticas de vacunas obtenidas exitosamente para el rango de fechas.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al obtener las estadísticas: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}