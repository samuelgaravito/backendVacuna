<?php

namespace App\Services\Representado;

use App\Models\Representado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class StoreRepresentadoService
{
    /**
     * Ejecuta la lógica para crear un nuevo representado.
     * Genera una cédula única basada en la del usuario autenticado.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function execute(Request $request): JsonResponse
    {
        // 1. Obtener el usuario autenticado y verificar el rol
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        if (!$user->hasRole('representante')) {
            return response()->json(['message' => 'Permiso denegado. No tienes el rol requerido.'], 403);
        }

        try {
            // 2. Validar los campos principales de la solicitud
            $validatedData = $request->validate([
                'nombre_completo'  => 'required|string|max:255',
                'fecha_nacimiento' => 'required|date',
                'sexo'             => 'required|in:M,F',
                'nacionalidad'     => 'required|in:venezolano,extranjero',
                'direccion'        => 'required|string',
                'parroquia_id'     => 'nullable|exists:parroquias,id',
                'grupo_riesgo_id'  => 'nullable|exists:grupos_riesgo,id',
                'indigena_id'      => 'nullable|exists:indigenas,id',
            ], [
                'nombre_completo.required'  => 'El nombre completo del representado es obligatorio.',
                'fecha_nacimiento.required' => 'La fecha de nacimiento del representado es obligatoria.',
                'sexo.required'             => 'El sexo del representado es obligatorio.',
                'nacionalidad.required'     => 'La nacionalidad del representado es obligatoria.',
                'direccion.required'        => 'La dirección del representado es obligatoria.',
            ]);

            // 3. Generar una cédula única para el representado
            $baseCedula = $user->cedula;

            // Contar cuántos representados ya tiene este usuario con una cédula basada en la suya.
            $representadosExistentesCount = Representado::where('user_id', $user->id)
                                                     ->where('cedula', 'LIKE', $baseCedula . '%')
                                                     ->count();

            // Generar un sufijo único (empezando en 1, 2, 3...)
            // Esto asegura que la cédula del representado NUNCA sea igual a la del representante.
            $sufijo = $representadosExistentesCount + 1;
            $cedulaParaCrear = $baseCedula . $sufijo;

            // 4. Validar la unicidad de la cédula generada
            // Verificamos que la nueva cédula no exista en la tabla 'representados'
            if (Representado::where('cedula', $cedulaParaCrear)->exists()) {
                throw ValidationException::withMessages([
                    'cedula' => 'La cédula generada (' . $cedulaParaCrear . ') ya existe para otro representado. Por favor, intente nuevamente.',
                ]);
            }
            
            // Y también verificamos que no exista en la tabla 'users'
            if (User::where('cedula', $cedulaParaCrear)->exists()) {
                throw ValidationException::withMessages([
                    'cedula' => 'La cédula generada (' . $cedulaParaCrear . ') ya está registrada como usuario. Por favor, contacte a soporte.',
                ]);
            }

            // 5. Asignar la cédula generada y el ID del usuario al array de datos
            $validatedData['cedula'] = $cedulaParaCrear;
            $validatedData['user_id'] = $user->id;

            // 6. Crear el registro del representado
            $representado = Representado::create($validatedData);

            // 7. Cargar las relaciones para la respuesta
            $representado->load(['user', 'parroquia', 'grupoRiesgo', 'indigena']);

            // 8. Devolver la respuesta exitosa
            return response()->json([
                'success' => true,
                'data'    => $representado,
                'message' => 'Representado creado exitosamente con la cédula: ' . $representado->cedula
            ], 201);

        } catch (ValidationException $e) {
            // Manejar errores de validación
            return response()->json([
                'message' => 'Error de validación.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Manejar errores inesperados del servidor
            return response()->json([
                'message' => 'Error al crear el representado: ' . $e->getMessage(),
            ], 500);
        }
    }
}