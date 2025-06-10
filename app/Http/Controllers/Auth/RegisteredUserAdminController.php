<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserAdminController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cedula' => 'required|string|max:20|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Rules\Password::defaults()],
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::create([
            'cedula' => $validated['cedula'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->roles()->attach($validated['role_id']);

        event(new Registered($user));

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->only(['id', 'name', 'email', 'cedula']),
                'role' => $user->roles->first()->only(['id', 'name'])
            ],
            'message' => 'Usuario registrado exitosamente'
        ], 201);
    }
}