<?php

namespace App\Services\Representado;

use App\Models\Representado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Closure;

class StoreRepresentadoAdminService
{
    /**
     * Permite a un administrador crear y asignar un representado a un usuario específico.
     * La cédula del representado se genera automáticamente a partir de la cédula del usuario representante
     * y se le añade un sufijo numérico para garantizar la unicidad.
     *
     * @param Request $request La solicitud HTTP.
     * @return JsonResponse
     */
    public function execute(Request $request): JsonResponse
    {
        try {
            // 1. Validación de los campos restantes (la cédula ya no se espera del request)
            $validatedData = $request->validate([
                'user_id'          => [
                    'required',
                    'exists:users,id',
                    // Valida que el usuario seleccionado tenga el rol 'representante'
                    function (string $attribute, mixed $value, Closure $fail) {
                        $user = User::find($value);
                        if (!$user || !$user->hasRole('representante')) {
                            $fail("El usuario seleccionado no tiene el rol 'representante'.");
                        }
                    },
                ],
                // La 'cedula' del representado no se pide en el request, se generará.
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
                'user_id.exists'    => 'El usuario especificado no existe.',
                'nombre_completo.required' => 'El nombre completo es obligatorio.',
                'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
                'sexo.required'     => 'El sexo es obligatorio.',
                'nacionalidad.required' => 'La nacionalidad es obligatoria.',
                'direccion.required' => 'La dirección es obligatoria.',
            ]);

            $userId = $validatedData['user_id'];

            // Obtener el usuario representante al que el admin está asignando el representado
            $representanteUser = User::find($userId);

            // Verificar si el representante existe y tiene cédula
            if (!$representanteUser || empty($representanteUser->cedula)) {
                 throw ValidationException::withMessages([
                    'user_id' => 'El usuario representante seleccionado no tiene una cédula asignada o no existe.',
                ]);
            }

            // --- LÓGICA DE GENERACIÓN DE CÉDULA AUTOMÁTICA (CORREGIDA) ---
            $baseCedula = $representanteUser->cedula;

            // Contar cuántos representados ya tiene este usuario con una cédula que empieza
            // con la cédula del representante (esto incluye sufijos).
            $representadosExistentesCount = Representado::where('user_id', $userId)
                                                     ->where('cedula', 'LIKE', $baseCedula . '%')
                                                     ->count();

            // El sufijo será la cantidad de existentes + 1. Esto asegura que siempre haya un sufijo
            // y que nunca se duplique la cédula del representante.
            $sufijo = $representadosExistentesCount + 1;
            $cedulaParaCrear = $baseCedula . $sufijo;

            // --- FIN DE LA LÓGICA CORREGIDA ---

            // 3. VALIDACIÓN DE UNICIDAD para la cédula definitiva (generada)
            // Comprueba si la cédula final (con sufijo) ya existe en 'representados'.
            if (Representado::where('cedula', $cedulaParaCrear)->exists()) {
                throw ValidationException::withMessages([
                    'cedula' => 'La cédula generada (' . $cedulaParaCrear . ') ya existe para otro representado. Por favor, contacte a soporte si cree que es un error.',
                ]);
            }
            // Comprueba que la cédula generada no exista ya en la tabla 'users' (para evitar conflictos con usuarios existentes)
            if (User::where('cedula', $cedulaParaCrear)->exists()) {
                 throw ValidationException::withMessages([
                    'cedula' => 'La cédula generada (' . $cedulaParaCrear . ') ya está registrada como usuario.',
                ]);
            }

            // 4. Asigna la cédula definitiva y el user_id para la creación
            $validatedData['cedula'] = $cedulaParaCrear; // <-- Asignamos la cédula generada
            $validatedData['user_id'] = $userId; // Ya obtenido de la validación inicial

            // 5. Creación del representado
            $representado = Representado::create($validatedData);
            $representado->load(['user', 'parroquia', 'grupoRiesgo', 'indigena']);

            return response()->json([
                'success' => true,
                'data'    => $representado,
                'message' => 'Representado creado y asignado exitosamente al usuario con la cédula: ' . $representado->cedula
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