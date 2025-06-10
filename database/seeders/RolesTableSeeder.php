<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        Role::create(['name' => 'admin', 'description' => 'Administrador del sistema']);
        Role::create(['name' => 'personal', 'description' => 'Personal médico']);
        Role::create(['name' => 'user', 'description' => 'Paciente']);
    }
}