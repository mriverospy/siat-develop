<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolConsultaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Consultar
        Rol::create([
            'name' => 'consulta',
            'display_name' => 'Consultar',
            'description' => 'Auditor del Sistema',
        ]);

        /*TODO: implementar el recorrido para agregar el permiso
         * $arr_permisos = ['consulta' => [
            'accidentes-list',
            'accidentes-pdf-report',
            'sedes-list'
        ]];*/

        $rol = Rol::where('name', 'consulta')->first();

        $permiso = Permiso::where(['name' => 'accidentes-list'])->first();
        $rol->attachPermission($permiso);

        $permiso = Permiso::where(['name' => 'accidentes-pdf-report'])->first();
        $rol->attachPermission($permiso);

        $permiso = Permiso::where(['name' => 'sedes-list'])->first();
        $rol->attachPermission($permiso);
    }
}
