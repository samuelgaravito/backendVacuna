<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role; // <-- ¡ESTA ES LA LÍNEA QUE FALTABA!

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Borra los roles existentes para evitar duplicados si quieres reiniciar la tabla.
        // Descomenta la siguiente línea si deseas un borrado limpio antes de sembrar.
        // DB::table('roles')->truncate();

        // Define los roles que quieres crear, incluyendo la descripción.
        $roles = [
            ['name' => 'admin', 'description' => 'Administrador del sistema'],
            ['name' => 'personal_de_salud', 'description' => 'Personal de salud que registra vacunas'],
            ['name' => 'representante', 'description' => 'Representante de personas a vacunar'],
        ];

        // Usa firstOrCreate para encontrar el rol por su nombre o crearlo si no existe.
        // Esto previene duplicados si ejecutas el seeder varias veces.
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }

        $this->command->info('Roles creados exitosamente!');
    }
}