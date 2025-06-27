<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // El orden es crucial: los roles deben existir antes de que se asignen.
        $this->call([
            RoleSeeder::class,        // <-- Primero, crea los roles
            AdminUserSeeder::class,   // <-- Segundo, crea el usuario y asigna el rol
            // ... otros seeders que necesites
        ]);
    }
}