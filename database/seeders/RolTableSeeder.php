<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ADMINISTRADOR
        Rol::create([
            'name' => 'sysadmin',
            'display_name' => 'Administrador del Sistema',
            'description' => 'Administrador del Sistema, tiene todos los permisos',
        ]);

        // ADMINISTRADOR DE LA INSTITUCION
        Rol::create([
            'name' => 'administrador',
            'display_name' => 'Administrador de la Institucion',
            'description' => 'Administradores de las Instituciones',
        ]);

        // DIRECTIVOS
        Rol::create([
            'name' => 'directivo',
            'display_name' => 'Directivo',
            'description' => 'Directivos de Instituciones',
        ]);

        // AGENTES
        Rol::create([
            'name' => 'agente',
            'display_name' => 'Agente',
            'description' => 'Agentes autorizados',
        ]);

        // USUARIO
        Rol::create([
            'name' => 'usuario',
            'display_name' => 'Usuario',
            'description' => 'Usuario del sistema',
        ]);

        //Consultar
        Rol::create([
            'name' => 'consulta',
            'display_name' => 'Consultar',
            'description' => 'Auditor del Sistema',
        ]);
    }
}
