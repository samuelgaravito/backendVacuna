<?php

namespace App\Services\Representado;

use App\Models\Representado;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateRepresentadoService
{
    public function execute(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$user->hasRole('representante')) {
            return response()->json(['message' => 'Permission denied. You do not have the required role.'], 403);
        }

        try {
            $representado = Representado::find($id);
            if (!$representado) {
                return response()->json(['message' => 'Represented individual not found.'], 404);
            }

            if ($user->id !== $representado->user_id) {
                 return response()->json(['message' => 'Access denied. You do not have permission to edit this represented individual.'], 403);
            }

            $validatedData = $request->validate([
                'cedula'           => ['required', 'string', 'max:20', Rule::unique('representados', 'cedula')->ignore($representado->id)],
                'nombre_completo'  => 'required|string|max:255',
                'fecha_nacimiento' => 'required|date',
                'sexo'             => 'required|in:M,F',
                'nacionalidad'     => 'required|in:venezolano,extranjero',
                'direccion'        => 'required|string',
                'parroquia_id'     => 'nullable|exists:parroquias,id', // <-- Cambiado a nullable
                'grupo_riesgo_id'  => 'nullable|exists:grupos_riesgo,id', // <-- Cambiado a nullable
                'indigena_id'      => 'nullable|exists:indigenas,id', // <-- Cambiado a nullable
            ]);

            $representado->update($validatedData);
            $representado->load(['user', 'parroquia', 'grupoRiesgo', 'indigena']);

            return response()->json([
                'success' => true,
                'data'    => $representado,
                'message' => 'Represented individual updated successfully.'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating represented individual: ' . $e->getMessage(),
            ], 500);
        }
    }
}