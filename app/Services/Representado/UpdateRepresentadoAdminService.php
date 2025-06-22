<?php

namespace App\Services\Representado;

use App\Models\Representado;
use App\Models\User; // Asegúrate de importar el modelo User
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Closure; // Importa la clase Closure para la validación personalizada

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
        // La autorización del rol 'admin' para quien ejecuta esta acción
        // se gestiona a nivel de middleware en las rutas (ej: 'role:admin').

        try {
            $representado = Representado::find($representadoId);
            if (!$representado) {
                return response()->json(['message' => 'Representado no encontrado.'], 404);
            }

            $validatedData = $request->validate([
                'user_id'          => [
                    'nullable', // El user_id es opcional al actualizar. Si no se envía, no se cambia.
                    'exists:users,id', // Valida que el ID, si se proporciona, exista en la tabla de usuarios.
                    // Usa una clausura personalizada para verificar el rol si el user_id está presente.
                    function (string $attribute, mixed $value, Closure $fail) {
                        // Solo ejecuta esta lógica si un user_id ha sido proporcionado en la solicitud
                        if ($value !== null) {
                            $user = User::find($value); // Busca el usuario por su ID.
                            // --- CAMBIO AQUÍ: Ahora el usuario asignado debe tener el rol 'admin' ---
                            // Si el usuario no existe (ya cubierto por 'exists') o NO tiene el rol 'admin', falla.
                            if (!$user || !$user->hasRole('admin')) { // <-- Se cambió 'representante' por 'admin'
                                $fail("El usuario seleccionado no tiene el rol 'admin'.");
                            }
                        }
                    },
                ],
                'cedula'           => [
                    'required', // La cédula es requerida al actualizar
                    'string',
                    'max:20',
                    Rule::unique('representados', 'cedula')->ignore($representado->id), // Única en representados (ignora el actual)
                    Rule::unique('users', 'cedula')->ignore($representado->user_id, 'id') // Única en usuarios (ignora si es la misma que el usuario actual)
                ],
                'nombre_completo'  => 'required|string|max:255',
                'fecha_nacimiento' => 'required|date',
                'sexo'             => 'required|in:M,F',
                'nacionalidad'     => 'required|in:venezolano,extranjero',
                'direccion'        => 'required|string',
                'parroquia_id'     => 'nullable|exists:parroquias,id',
                'grupo_riesgo_id'  => 'nullable|exists:grupos_riesgo,id',
                'indigena_id'      => 'nullable|exists:indigenas,id',
            ], [
                'user_id.exists'            => 'El usuario especificado para asignar el representado no existe.',
                'cedula.unique'             => 'Ya existe un representado o usuario con esta cédula.', // Mensaje unificado
                'cedula.required'           => 'La cédula es obligatoria.',
                'nombre_completo.required'  => 'El nombre completo es obligatorio.',
                'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
                'sexo.required'             => 'El sexo es obligatorio.',
                'nacionalidad.required'     => 'La nacionalidad es obligatoria.',
                'direccion.required'        => 'La dirección es obligatoria.',
            ]);

            // Se agregó Rule::unique('users', 'cedula')->ignore(...) para la cédula
            // esto evita conflictos si la cédula que se está actualizando es la misma que
            // la de algún user_id, PERO si ese user_id es el mismo al que está asignado este Representado,
            // entonces no debe dar error.

            $representado->update($validatedData);
            $representado->load(['user', 'parroquia', 'grupoRiesgo', 'indigena']);

            return response()->json([
                'success' => true,
                'data'    => $representado,
                'message' => 'Representado actualizado exitosamente.'
            ], 200);

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
