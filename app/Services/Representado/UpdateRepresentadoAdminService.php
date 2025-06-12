<?php

namespace App\Services\Representado;

use App\Models\Representado;
use App\Models\User; // Asegúrate de importar el modelo User
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Closure; // ¡Importa la clase Closure para la validación personalizada!

class UpdateRepresentadoAdminService
{
    /**
     * Permite a un administrador actualizar un representado asignado a un usuario.
     *
     * @param Request $request La solicitud HTTP.
     * @param int $representadoId El ID del representado a actualizar.
     * @return JsonResponse
     */
    public function execute(Request $request, int $representadoId): JsonResponse
    {
        // La autorización de rol 'admin' se gestionará en el middleware de ruta.

        try {
            $representado = Representado::find($representadoId);
            if (!$representado) {
                return response()->json(['message' => 'Representado no encontrado.'], 404);
            }

            $validatedData = $request->validate([
                'user_id'          => [
                    'nullable', // El user_id es opcional al actualizar
                    'exists:users,id', // Valida que el ID exista en la tabla de usuarios
                    // Usa una clausura personalizada para verificar el rol si el user_id está presente
                    function (string $attribute, mixed $value, Closure $fail) {
                        // Solo ejecuta esta lógica si un user_id ha sido proporcionado en la solicitud
                        if ($value !== null) {
                            $user = User::find($value); // Busca el usuario por su ID
                            // Si el usuario no existe (ya cubierto por 'exists') o no tiene el rol 'representante', falla
                            if (!$user || !$user->hasRole('representante')) {
                                $fail("El usuario seleccionado no tiene el rol 'representante'.");
                            }
                        }
                    },
                ],
                'cedula'           => ['required', 'string', 'max:20', Rule::unique('representados', 'cedula')->ignore($representado->id)],
                'nombre_completo'  => 'required|string|max:255',
                'fecha_nacimiento' => 'required|date',
                'sexo'             => 'required|in:M,F',
                'nacionalidad'     => 'required|in:venezolano,extranjero',
                'direccion'        => 'required|string',
                'parroquia_id'     => 'nullable|exists:parroquias,id',
                'grupo_riesgo_id'  => 'nullable|exists:grupos_riesgo,id',
                'indigena_id'      => 'nullable|exists:indigenas,id',
            ], [
                'user_id.exists'    => 'El usuario especificado no existe.', // Mensaje más genérico ahora
                'cedula.unique'     => 'Ya existe un representado con esta cédula.',
            ]);

            $representado->update($validatedData);
            $representado->load(['user', 'parroquia', 'grupoRiesgo', 'indigena']);

            return response()->json([
                'success' => true,
                'data'    => $representado,
                'message' => 'Representado actualizado exitosamente.'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error actualizando representado: ' . $e->getMessage(),
            ], 500);
        }
    }
}