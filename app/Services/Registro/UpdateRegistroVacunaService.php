<?php

namespace App\Services\Registro;

use App\Models\Registro; // Importa el modelo Registro
use App\Models\Representado;
use App\Models\Vacuna;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // Necesario para la regla 'unique' ignorando el registro actual

class UpdateRegistroVacunaService
{
    /**
     * Actualiza un registro de vacuna existente.
     * Solo los usuarios con rol 'admin' pueden ejecutar esta acción.
     *
     * @param Request $request La solicitud HTTP con los datos actualizados del registro.
     * @param int $registroId El ID del registro de vacuna a actualizar.
     * @return JsonResponse
     */
    public function execute(Request $request, int $registroId): JsonResponse
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // 1. Validar que el usuario esté autenticado
        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        // 2. Validar el rol del usuario: solo 'admin' puede editar
        if (!$user->hasRole('admin')) {
            return response()->json(['message' => 'Permiso denegado. Solo un administrador puede editar registros de vacuna.'], 403);
        }

        try {
            // Buscar el registro de vacuna existente
            $registro = Registro::findOrFail($registroId);

            // 3. Validar los datos del request. Todos los campos son opcionales en la edición,
            // pero si se envían, deben ser válidos.
            $validatedData = $request->validate([
                'representado_id' => ['nullable', 'exists:representados,id'],
                'vacuna_id'       => ['nullable', 'exists:vacunas,id'],
                'dosis'           => ['nullable', 'integer', 'min:1'],
            ], [
                // Mensajes de validación personalizados
                'representado_id.exists' => 'El ID del representado especificado no existe.',
                'vacuna_id.exists'       => 'La vacuna especificada no existe.',
                'dosis.integer'          => 'La dosis debe ser un número entero.',
                'dosis.min'              => 'La dosis debe ser al menos 1.',
            ]);

            // Obtener Representado, Vacuna y Dosis actuales o desde los datos validados
            // Usamos un merge para asegurar que tenemos los datos más actualizados para las validaciones
            $currentData = array_merge($registro->toArray(), $validatedData);

            $representadoId = $currentData['representado_id'];
            $vacunaId = $currentData['vacuna_id'];
            $dosisARegistrar = $currentData['dosis'];

            $representado = Representado::findOrFail($representadoId);
            $vacuna = Vacuna::findOrFail($vacunaId);

            // 4. Lógica de Negocio: Validar la dosis contra la cantidad de la vacuna (solo si la dosis está presente y es válida)
            if (isset($validatedData['dosis'])) { // Solo valida si la dosis fue enviada para actualizar
                if ($dosisARegistrar > $vacuna->cantidad) {
                    throw ValidationException::withMessages([
                        'dosis' => "La dosis a registrar ({$dosisARegistrar}) excede la cantidad máxima para esta vacuna ({$vacuna->cantidad})."
                    ]);
                }
            }


            // 5. Lógica de Negocio: Validar que la combinación (representado_id, vacuna_id, dosis) NO exista ya para OTRA entrada
            // Esto es crucial para no crear duplicados cuando se actualiza.
            $registroExistente = Registro::where('representado_id', $representadoId)
                                         ->where('vacuna_id', $vacunaId)
                                         ->where('dosis', $dosisARegistrar)
                                         ->where('id', '!=', $registro->id) // Ignorar el registro que estamos actualizando
                                         ->exists();

            if ($registroExistente) {
                throw ValidationException::withMessages([
                    'dosis' => "La dosis {$dosisARegistrar} de la vacuna '{$vacuna->nombre}' ya ha sido registrada para el representado: {$representado->nombre_completo} en otro registro."
                ]);
            }

            // 6. Actualizar el registro con los datos validados
            $registro->update($validatedData);

            // 7. Cargar las relaciones para la respuesta JSON
            $registro->load(['representado', 'personalSalud', 'vacuna']);

            // 8. Retornar una respuesta JSON de éxito
            return response()->json([
                'success' => true,
                'message' => 'Registro de vacuna actualizado exitosamente.',
                'data'    => $registro
            ], 200); // Código de estado 200 OK para actualizaciones

        } catch (ValidationException $e) {
            // Capturar errores de validación
            return response()->json([
                'message' => 'Error de validación al actualizar el registro.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Capturar si el Registro, Representado o Vacuna no fueron encontrados
            return response()->json([
                'success' => false,
                'message' => 'El registro de vacuna, representado o vacuna especificado no fue encontrado.',
                'error'   => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            // Capturar cualquier otra excepción inesperada
            return response()->json([
                'message' => 'Ocurrió un error inesperado al actualizar el registro: ' . $e->getMessage(),
            ], 500);
        }
    }
}