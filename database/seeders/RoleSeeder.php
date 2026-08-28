<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Crear los roles iniciales del sistema.
     */
    public function run(): void
    {
        Role::create([
            'nombre' => 'Administrador',
            'descripcion' => 'Acceso completo a todos los módulos del sistema.',
            'activo' => true,
        ]);

        Role::create([
            'nombre' => 'Recursos Humanos',
            'descripcion' => 'Gestiona empleados, reclutamiento y planillas.',
            'activo' => true,
        ]);

        Role::create([
            'nombre' => 'Contabilidad',
            'descripcion' => 'Gestiona planillas, tributación y reportes contables.',
            'activo' => true,
        ]);

        Role::create([
            'nombre' => 'Consulta',
            'descripcion' => 'Acceso de consulta a la información autorizada.',
            'activo' => true,
        ]);
    }
}