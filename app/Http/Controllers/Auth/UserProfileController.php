<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class UserProfileController extends Controller
{
    /**
     * Muestra la información del usuario autenticado
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $user = Auth::user()->load('roles:id,name');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->only(['id', 'cedula', 'name', 'email']),
                'roles' => $user->roles
            ],
            'message' => 'Información del usuario obtenida correctamente'
        ]);
    }

    /**
     * Actualiza la información del usuario autenticado
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'current_password' => ['sometimes', 'required_with:password', 'current_password'],
            'password' => ['sometimes', 'confirmed', Rules\Password::defaults()],
        ]);

        // Actualizar campos básicos
        if ($request->has('name')) {
            $user->name = $validated['name'];
        }

        if ($request->has('email')) {
            $user->email = $validated['email'];
        }

        // Actualizar contraseña si se proporciona
        if ($request->has('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'data' => $user->only(['id', 'cedula', 'name', 'email']),
            'message' => 'Perfil actualizado correctamente'
        ]);
    }

    /**
     * Elimina la cuenta del usuario autenticado
     *
     * @return JsonResponse
     */
    public function destroy(): JsonResponse
    {
        $user = Auth::user();

        // Opcional: validar contraseña antes de eliminar
        // if (!Hash::check($request->password, $user->password)) {
        //     return response()->json(['message' => 'Contraseña incorrecta'], 403);
        // }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cuenta eliminada correctamente'
        ]);
    }
}