<?php

namespace App\Services\Representado;

use App\Models\Representado;
use App\Models\User; // Asegúrate de importar el modelo User
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule; // Todavía se usa para Rule::unique
use Closure; // Importa la clase Closure para la validación personalizada

class StoreRepresentadoAdminService
{
    /**
     * Permite a un administrador crear y asignar un representado a un usuario específico.
     *
     * @param Request $request La solicitud HTTP.
     * @return JsonResponse
     */
    public function execute(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'user_id'          => [
                    'required',
                    'exists:users,id', // Primero, valida que el ID exista en la tabla de usuarios
                    // Luego, usa una clausura de validación personalizada para verificar el rol
                    function (string $attribute, mixed $value, Closure $fail) {
                        $user = User::find($value); // Busca el usuario por su ID
                        // Si el usuario no tiene el rol 'representante', falla la validación
                        if (!$user || !$user->hasRole('representante')) {
                            $fail("El usuario seleccionado no tiene el rol 'representante'.");
                        }
                    },
                ],
                'cedula'           => ['required', 'string', 'max:20', Rule::unique('representados', 'cedula')],
                'nombre_completo'  => 'required|string|max:255',
                'fecha_nacimiento' => 'required|date',
                'sexo'             => 'required|in:M,F',
                'nacionalidad'     => 'required|in:venezolano,extranjero',
                'direccion'        => 'required|string',
                'parroquia_id'     => 'nullable|exists:parroquias,id',
                'grupo_riesgo_id'  => 'nullable|exists:grupos_riesgo,id',
                'indigena_id'      => 'nullable|exists:indigenas,id',
            ], [
                'user_id.required'  => 'El ID del usuario es requerido para asignar el representado.',
                'user_id.exists'    => 'El usuario especificado no existe.', // Mensaje más genérico ahora
                'cedula.unique'     => 'Ya existe un representado con esta cédula.',
            ]);

            $representado = Representado::create($validatedData);
            $representado->load(['user', 'parroquia', 'grupoRiesgo', 'indigena']);

            return response()->json([
                'success' => true,
                'data'    => $representado,
                'message' => 'Representado creado y asignado exitosamente al usuario.'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creando representado para el usuario: ' . $e->getMessage(),
            ], 500);
        }
    }
}