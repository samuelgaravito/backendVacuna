<?php

namespace App\Services\Registro; // Ajusta el namespace según tu estructura

use App\Models\Registro;
use App\Models\Representado; // Asegúrate de que esta línea esté presente
use App\Models\Vacuna;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Para transacciones si es necesario

class StoreRegistroVacunaService
{
    /**
     * Registra una nueva vacuna administrada a un representado.
     *
     * @param Request $request La solicitud HTTP con los datos del registro.
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
            // 3. Validar los datos del request
            $validatedData = $request->validate([
                'representado_id' => 'required|exists:representados,id',
                'vacuna_id'       => 'required|exists:vacunas,id',
                'dosis'           => 'required|integer|min:1', // La dosis debe ser un entero positivo
            ], [
                'representado_id.required' => 'El ID del representado es obligatorio.',
                'representado_id.exists'   => 'El representado especificado no existe.',
                'vacuna_id.required'       => 'El ID de la vacuna es obligatorio.',
                'vacuna_id.exists'         => 'La vacuna especificada no existe.',
                'dosis.required'           => 'El número de dosis es obligatorio.',
                'dosis.integer'            => 'La dosis debe ser un número entero.',
                'dosis.min'                => 'La dosis debe ser al menos 1.',
            ]);

            // Obtener el Representado y la Vacuna para validaciones adicionales
            $representado = Representado::findOrFail($validatedData['representado_id']);
            $vacuna = Vacuna::findOrFail($validatedData['vacuna_id']);
            $dosisARegistrar = $validatedData['dosis'];

            // 4. Lógica de Negocio: Validar la dosis contra la cantidad de la vacuna
            // Comparamos la dosis que se quiere registrar con la 'cantidad' máxima/total de la vacuna.
            // Si la dosis a registrar es mayor o igual a la cantidad de la vacuna, asumimos que no es válida.
            // Por ejemplo, si una vacuna 'Covid' tiene cantidad 2 (dosis máxima), no puedes registrar la dosis 3.
            if ($dosisARegistrar > $vacuna->cantidad) {
                throw ValidationException::withMessages([
                    'dosis' => "La dosis a registrar ({$dosisARegistrar}) excede la cantidad máxima para esta vacuna ({$vacuna->cantidad})."
                ]);
            }

            // 5. Lógica de Negocio: Validar que esta dosis específica para esta vacuna NO haya sido registrada ya para este representado
            // Esto evita registros duplicados para la misma dosis de la misma vacuna para el mismo representado.
            $registroExistente = Registro::where('representado_id', $representado->id)
                                         ->where('vacuna_id', $vacuna->id)
                                         ->where('dosis', $dosisARegistrar)
                                         ->exists();

            if ($registroExistente) {
                // --- CAMBIO AQUÍ: Se agrega el nombre completo del representado ---
                throw ValidationException::withMessages([
                    'dosis' => "La dosis {$dosisARegistrar} de la vacuna '{$vacuna->nombre}' ya ha sido registrada para el representado: {$representado->nombre_completo}."
                ]);
            }

            // 6. Asignar el ID del personal de salud (usuario autenticado) a los datos validados
            $validatedData['personal_salud_id'] = $personalSalud->id;

            // 7. Crear el registro de la vacuna
            $registro = Registro::create($validatedData);

            // 8. Cargar las relaciones para la respuesta JSON
            $registro->load(['representado', 'personalSalud', 'vacuna']);

            // 9. Retornar una respuesta JSON de éxito
            return response()->json([
                'success' => true,
                'message' => 'Vacuna registrada exitosamente.',
                'data'    => $registro
            ], 201);

        } catch (ValidationException $e) {
            // Capturar errores de validación
            return response()->json([
                'message' => 'Error de validación al registrar la vacuna.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Capturar si Representado o Vacuna no fueron encontrados (aunque 'exists' debería prevenir esto)
            return response()->json([
                'success' => false,
                'message' => 'Recurso no encontrado (Representado o Vacuna).'
            ], 404);
        } catch (\Exception $e) {
            // Capturar cualquier otra excepción inesperada
            return response()->json([
                'message' => 'Ocurrió un error inesperado al registrar la vacuna: ' . $e->getMessage(),
            ], 500);
        }
    }
}