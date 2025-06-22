<?php

namespace App\Services\Registro; // Ajusta el namespace según tu estructura

use App\Models\Registro;
use App\Models\Representado;
use App\Models\Vacuna;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Para transacciones si es necesario

class StoreRegistroVacunaService
{
    /**
     * Registra una nueva vacuna administrada a un representado, determinando la dosis automáticamente.
     *
     * @param Request $request La solicitud HTTP con los datos del registro (sin incluir 'dosis').
     * @return JsonResponse
     */
    public function execute(Request $request): JsonResponse
    {
        // Obtener el usuario autenticado (quien registra la vacuna)
        $personalSalud = Auth::user();

        // 1. Validar que el usuario esté autenticado
        if (!$personalSalud) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        // 2. Validar el rol del usuario: debe ser 'admin' o 'personal_de_salud'
        if (!$personalSalud->hasAnyRole(['admin', 'personal_de_salud'])) {
            return response()->json(['message' => 'Permiso denegado. No tiene el rol para registrar vacunas.'], 403);
        }

        try {
            // 3. Validar los datos del request (la dosis ya NO se recibe aquí, se calcula automáticamente)
            $validatedData = $request->validate([
                'representado_id' => 'required|exists:representados,id',
                'vacuna_id'       => 'required|exists:vacunas,id',
                // 'dosis' ya no está en la validación del request
            ], [
                'representado_id.required' => 'El ID del representado es obligatorio.',
                'representado_id.exists'   => 'El representado especificado no existe.',
                'vacuna_id.required'       => 'El ID de la vacuna es obligatorio.',
                'vacuna_id.exists'         => 'La vacuna especificada no existe.',
            ]);

            // Obtener el Representado y la Vacuna para validaciones y cálculos
            $representado = Representado::findOrFail($validatedData['representado_id']);
            $vacuna = Vacuna::findOrFail($validatedData['vacuna_id']);

            // 4. Lógica de Negocio: Determinar automáticamente la siguiente dosis
            // Buscar la última dosis registrada para este representado y esta vacuna.
            $ultimoRegistroDosis = Registro::where('representado_id', $representado->id)
                                           ->where('vacuna_id', $vacuna->id)
                                           ->orderBy('dosis', 'desc') // Ordenar de mayor a menor para obtener la última dosis
                                           ->first(); // Obtener el primer resultado (la mayor dosis)

            $dosisARegistrar = 1; // Si no hay registros previos, la primera dosis es 1
            if ($ultimoRegistroDosis) {
                $dosisARegistrar = $ultimoRegistroDosis->dosis + 1; // Incrementar la última dosis
            }

            // 5. Lógica de Negocio: Validar que la dosis calculada NO exceda la cantidad máxima de la vacuna
            // Comparamos la dosis calculada con la 'cantidad' máxima/total de la vacuna.
            if ($dosisARegistrar > $vacuna->cantidad) {
                throw ValidationException::withMessages([
                    'dosis' => "No se puede registrar la dosis {$dosisARegistrar}. La cantidad máxima de dosis para la vacuna '{$vacuna->nombre}' es {$vacuna->cantidad} y ya ha sido alcanzada o excedida."
                ]);
            }

            // 6. Asignar la dosis calculada y el ID del personal de salud (usuario autenticado) a los datos validados
            $validatedData['dosis'] = $dosisARegistrar; // <-- La dosis se asigna automáticamente aquí
            $validatedData['personal_salud_id'] = $personalSalud->id;

            // 7. Crear el registro de la vacuna
            $registro = Registro::create($validatedData);

            // 8. Cargar las relaciones para la respuesta JSON
            $registro->load(['representado', 'personalSalud', 'vacuna']);

            // 9. Retornar una respuesta JSON de éxito
            return response()->json([
                'success' => true,
                'message' => "Vacuna '{$vacuna->nombre}' (Dosis {$dosisARegistrar}) registrada exitosamente para el representado '{$representado->nombre_completo}'.",
                'data'    => $registro
            ], 201);

        } catch (ValidationException $e) {
            // Capturar errores de validación
            return response()->json([
                'message' => 'Error de validación al registrar la vacuna.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Capturar si Representado o Vacuna no fueron encontrados
            return response()->json([
                'success' => false,
                'message' => 'Recurso no encontrado (Representado o Vacuna).',
                'error'   => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            // Capturar cualquier otra excepción inesperada
            return response()->json([
                'message' => 'Ocurrió un error inesperado al registrar la vacuna: ' . $e->getMessage(),
            ], 500);
        }
    }
}
