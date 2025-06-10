<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role; // Asegúrate de importar el modelo Role si vas a gestionar roles
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // Para las reglas de validación 'unique'
use Illuminate\Support\Facades\Hash; // Para actualizar la contraseña

class UserAdminController extends Controller
{
    /**
     * Obtiene el listado paginado de usuarios
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Verificar que el usuario autenticado es admin
        if (!Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos de administrador'
            ], 403);
        }

        // Paginación con parámetros personalizables
        $perPage = $request->input('per_page', 15); // Default 15 items por página
        $page = $request->input('page', 1); // Default página 1

        // Consulta con eager loading para los roles
        $users = User::with('roles:id,name,description')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users->items(),
                'pagination' => [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem()
                ]
            ],
            'message' => 'Listado de usuarios obtenido correctamente'
        ]);
    }

    /**
     * Obtiene los detalles de un usuario específico
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        // Verificar que el usuario autenticado es admin
        if (!Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos de administrador'
            ], 403);
        }

        $user = User::with('roles:id,name,description')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'roles' => $user->roles // Esto ya está incluido en $user si usas with()
            ],
            'message' => 'Detalles del usuario obtenidos correctamente'
        ]);
    }

    /**
     * Actualiza la información de un usuario existente.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // 1. Verificar permisos de administrador
        if (!Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para actualizar usuarios'
            ], 403);
        }

        // 2. Encontrar el usuario
        $user = User::findOrFail($id);

        // 3. Validar los datos de entrada
        $validatedData = $request->validate([
            // 'cedula' debe ser único, pero ignorar la cédula del usuario actual
            'cedula' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'name' => 'required|string|max:255',
            // 'email' debe ser único, pero ignorar el email del usuario actual
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:8', // 'nullable' permite no cambiar la contraseña
            'role_id' => 'sometimes|exists:roles,id' // 'sometimes' solo valida si está presente
        ]);

        // 4. Actualizar la información del usuario
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->cedula = $validatedData['cedula'];

        // Si se proporciona una nueva contraseña, encriptarla
        if (isset($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        $user->save(); // Guarda los cambios básicos del usuario

        // 5. Actualizar los roles (si 'role_id' está presente en la solicitud)
        if (isset($validatedData['role_id'])) {
            // Asegúrate de que $validatedData['role_id'] sea un array si el usuario puede tener múltiples roles,
            // o un single ID si solo puede tener uno. Aquí asumo un solo rol por 'role_id'.
            // Si el usuario puede tener múltiples roles, el campo debería ser 'role_ids[]' y usarias sync().
            $user->roles()->sync([$validatedData['role_id']]); // sync() es mejor que attach() para actualizar
        }

        // Recargar el usuario con la nueva relación de roles para la respuesta
        $user->load('roles');

        // 6. Retornar respuesta JSON
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->only(['id', 'name', 'email', 'cedula']),
                'role' => $user->roles->first()?->only(['id', 'name']) // Usar null-safe operator para roles
            ],
            'message' => 'Usuario actualizado exitosamente'
        ]);
    }
}