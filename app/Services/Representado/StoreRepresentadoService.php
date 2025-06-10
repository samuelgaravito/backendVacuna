<?php

namespace App\Services\Representado;

use App\Models\Representado;
use App\Models\Parroquia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreRepresentadoService
{
    public function execute(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$user->hasRole('user')) {
            return response()->json(['message' => 'Permission denied. You do not have the required role.'], 403);
        }

        try {
            $validatedData = $request->validate([
                'cedula'           => ['required', 'string', 'max:20', Rule::unique('representados', 'cedula')],
                'nombre_completo'  => 'required|string|max:255',
                'fecha_nacimiento' => 'required|date',
                'sexo'             => 'required|in:M,F',
                'nacionalidad'     => 'required|in:venezolano,extranjero',
                'direccion'        => 'required|string',
                'parroquia_id'     => 'nullable|exists:parroquias,id', // <-- Cambiado a nullable
                'grupo_riesgo_id'  => 'nullable|exists:grupos_riesgo,id', // <-- Cambiado a nullable
                'indigena_id'      => 'nullable|exists:indigenas,id', // <-- Cambiado a nullable
            ], [
                'cedula.unique' => 'A represented individual with this ID already exists.',
            ]);

            $validatedData['user_id'] = $user->id;

            $representado = Representado::create($validatedData);
            $representado->load(['user', 'parroquia', 'grupoRiesgo', 'indigena']);

            return response()->json([
                'success' => true,
                'data'    => $representado,
                'message' => 'Represented individual created successfully.'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating represented individual: ' . $e->getMessage(),
            ], 500);
        }
    }
}