<?php

namespace App\Services\Representado;

use App\Models\Representado;
use App\Models\User; // You'll need this import if you access User model directly for any reason
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // Still used for rules, though 'unique' is handled manually

class StoreRepresentadoService
{
    public function execute(Request $request): JsonResponse
    {
        $user = Auth::user(); // The authenticated representative user

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$user->hasRole('representante')) {
            return response()->json(['message' => 'Permission denied. You do not have the required role.'], 403);
        }

        try {
            // 1. Validate the core fields (excluding 'cedula' from request validation)
            $validatedData = $request->validate([
                // 'cedula' is no longer expected from the request body
                'nombre_completo'  => 'required|string|max:255',
                'fecha_nacimiento' => 'required|date',
                'sexo'             => 'required|in:M,F',
                'nacionalidad'     => 'required|in:venezolano,extranjero',
                'direccion'        => 'required|string',
                'parroquia_id'     => 'nullable|exists:parroquias,id',
                'grupo_riesgo_id'  => 'nullable|exists:grupos_riesgo,id',
                'indigena_id'      => 'nullable|exists:indigenas,id',
            ], [
                // Custom validation messages for the other fields
                'nombre_completo.required' => 'El nombre completo del representado es obligatorio.',
                'fecha_nacimiento.required' => 'La fecha de nacimiento del representado es obligatoria.',
                'sexo.required'             => 'El sexo del representado es obligatorio.',
                'nacionalidad.required'     => 'La nacionalidad del representado es obligatoria.',
                'direccion.required'        => 'La dirección del representado es obligatoria.',
            ]);

            // **NEW LOGIC: Use the representative's cedula as the base for the represented**
            $baseCedula = $user->cedula;
            $cedulaParaCrear = $baseCedula; // Start with the representative's cedula

            // 2. LOGIC: Check if this cedula (or a derivative) already exists for this representative
            // We'll count how many represented records this user already has
            // whose cedula starts with the representative's base cedula.
            $representadosExistentesCount = Representado::where('user_id', $user->id)
                                                     ->where('cedula', 'LIKE', $baseCedula . '%')
                                                     ->count();

            // If there are existing represented with this base cedula, append a suffix
            if ($representadosExistentesCount > 0) {
                // The suffix will be the count of existing + 1
                $sufijo = $representadosExistentesCount + 1;
                $cedulaParaCrear = $baseCedula . $sufijo;
            }

            // 3. MANUAL UNIQUENESS VALIDATION for the final cedula
            // Check if the final 'cedulaParaCrear' already exists in the 'representados' table globally
            if (Representado::where('cedula', $cedulaParaCrear)->exists()) {
                throw ValidationException::withMessages([
                    'cedula' => 'La cédula generada (' . $cedulaParaCrear . ') ya existe para otro representado. Por favor, contacte a soporte si cree que es un error.',
                ]);
            }
            // Also check if the final 'cedulaParaCrear' exists in the 'users' table
            if (User::where('cedula', $cedulaParaCrear)->exists()) {
                throw ValidationException::withMessages([
                    'cedula' => 'La cédula generada (' . $cedulaParaCrear . ') ya está registrada como usuario. Por favor, contacte a soporte si cree que es un error.',
                ]);
            }

            // 4. Assign the final cedula and the user_id for creation
            $validatedData['cedula'] = $cedulaParaCrear;
            $validatedData['user_id'] = $user->id; // Assign the authenticated user's ID

            // 5. Create the represented record
            $representado = Representado::create($validatedData);
            $representado->load(['user', 'parroquia', 'grupoRiesgo', 'indigena']);

            return response()->json([
                'success' => true,
                'data'    => $representado,
                'message' => 'Representado creado exitosamente con la cédula: ' . $representado->cedula
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el representado: ' . $e->getMessage(),
            ], 500);
        }
    }
}