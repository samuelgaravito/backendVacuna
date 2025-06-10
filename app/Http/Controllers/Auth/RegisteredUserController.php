<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'cedula' => ['required', 'string', 'max:20', 'unique:users'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'cedula' => $request->cedula,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Asignar rol predeterminado (ID 3 para paciente)
        $user->roles()->attach(3);
        $user->load('roles'); // Cargar la relación de roles

        event(new Registered($user));

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Registro exitoso',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'cedula' => $user->cedula,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')
                ],
                'token' => $user->createToken('auth_token')->plainTextToken
            ]
        ], 201); // Código HTTP 201 para "Created"
    }
}