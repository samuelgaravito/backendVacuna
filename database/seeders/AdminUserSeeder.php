<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role; // <-- Importamos tu modelo Role
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Usa una transacción para asegurar que la creación de usuario y rol sea atómica.
        DB::transaction(function () {

            // 1. Encuentra el rol 'admin' (ya creado por el RoleSeeder).
            $adminRole = Role::where('name', 'admin')->first();

            // Si el rol no existe, detenemos la ejecución para evitar errores.
            if (!$adminRole) {
                $this->command->error('El rol "admin" no fue encontrado. Asegúrate de que RoleSeeder se haya ejecutado primero.');
                return;
            }

            // 2. Crea el usuario administrador si no existe.
            $admin = User::firstOrCreate(
                [
                    'email' => 'admin@example.com' // Criterio de búsqueda
                ],
                [
                    'name' => 'Admin User',
                    'cedula' => '00000001', // Cédula por defecto para el admin
                    'password' => Hash::make('password'), // Clave: 'password'
                ]
            );

            // 3. Asigna el rol 'admin' al usuario usando la relación de Eloquent.
            // La función sync() es ideal porque asegura que el usuario solo tenga los roles que le indicas.
            $admin->roles()->sync([$adminRole->id]); // <-- ¡CORRECCIÓN CLAVE AQUÍ!

            $this->command->info('Usuario admin creado exitosamente!');
            $this->command->warn('Email: admin@example.com');
            $this->command->warn('Clave: password');
        });
    }
}