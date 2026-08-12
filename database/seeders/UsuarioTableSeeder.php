<?php

namespace Database\Seeders;

use App\Models\Institucion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Obtener ID's de las seedes para el factory de Usuarios así se ejecuta
        // sólo una vez y no por cada usuario
//        $sedeIds = Sede::pluck('id')->toArray();

        // Obtiene el Rol Admin
        $rolSysAdmin = Rol::findOrFail(1);
        $rolAdministrador = Rol::findOrFail(2);
        $rolDirectivo = Rol::findOrFail(3);
        $rolAgente = Rol::findOrFail(4);
        $rolUsuario = Rol::findOrFail(5);

        // Super Admins
       Usuario::factory()->count(1)->create([
            'genero' => 'Masculino',
            'nombres' => 'Super',
            'apellidos' => 'Administrador',
            'email' => 'admin@siat.gov.py',
            'username' => 'sysadmin',
            'password' => Hash::make('secreto1234'),
            'api_key' => '00000',
            'activo' => 1,
        ])[0]->roles()->attach($rolSysAdmin);

        Usuario::factory()->count(1)->create([
            'genero' => 'Masculino',
            'nombres' => 'Nicolás',
            'apellidos' => 'Rodríguez',
            'email' => 'nico@borealishq.com',
            'username' => 'nico',
            'password' => Hash::make('m'),
            'api_key' => '00001',
            'activo' => 1,
        ])[0]->roles()->attach($rolSysAdmin);

        // Usuario Ever
        Usuario::factory()->count(1)->create([
            'documento' => 1782823,
            'genero' => 'Masculino',
            'nombres' => 'Ever Daniel',
            'apellidos' => 'Barreto',
            'email' => 'ever@borealishq.com',
            'username' => 'ever',
            'password' => Hash::make('m'),
            'api_key' => '00002',
            'activo' => 1,
        ])[0]->roles()->attach($rolSysAdmin);

        // Institución 1
        $institucion1 = Institucion::where('nombre', 'Institución 1')->first();
        $sedeId = $institucion1->sedes()->first()->id;
        Usuario::factory()->count(1)->create([
            'sede_id' => $sedeId,
            'genero' => 'Masculino',
            'nombres' => 'Administrador',
            'apellidos' => 'Institución 1',
            'email' => 'admin@institucion1.gov.py',
            'username' => 'admin1',
            'password' => Hash::make('secreto1234'),
            'activo' => 1,
        ])[0]->roles()->attach($rolAdministrador);

        Usuario::factory()->count(1)->create([
            'sede_id' => $sedeId,
            'genero' => 'Femenino',
            'nombres' => 'Marcela',
            'apellidos' => 'Baukloh',
            'email' => 'marcela@siat.gov.py',
            'username' => 'marcela',
            'password' => Hash::make('secreto1234'),
            'activo' => 1,
        ])[0]->roles()->attach($rolDirectivo);

        Usuario::factory()->count(1)->create([
            'sede_id' => $sedeId,
            'genero' => 'Masculino',
            'nombres' => 'Julián',
            'apellidos' => 'Cáceres',
            'email' => 'julian@siat.gov.py',
            'username' => 'julian',
            'password' => Hash::make('secreto1234'),
            'activo' => 1,
        ])[0]->roles()->attach($rolDirectivo);

        // Institución 2
        $institucion2 = Institucion::where('nombre', 'Institución 2')->first();
        $sedeId = $institucion2->sedes()->first()->id;
        Usuario::factory()->count(1)->create([
            'sede_id' => $sedeId,
            'genero' => 'Masculino',
            'nombres' => 'Administrador',
            'apellidos' => 'Institución 2',
            'email' => 'admin@institucion2.gov.py',
            'username' => 'admin2',
            'password' => Hash::make('secreto1234'),
            'activo' => 1,
        ])[0]->roles()->attach($rolAdministrador);

        Usuario::factory()->count(1)->create([
            'sede_id' => $sedeId,
            'genero' => 'Masculino',
            'nombres' => 'Rodrigo',
            'apellidos' => 'Benítez',
            'email' => 'rodrigo@siat.gov.py',
            'username' => 'rodrigo',
            'password' => Hash::make('secreto1234'),
            'activo' => 1,
        ])[0]->roles()->attach($rolDirectivo);

        Usuario::factory()->count(1)->create([
            'sede_id' => $sedeId,
            'genero' => 'Masculino',
            'nombres' => 'Teddy',
            'apellidos' => 'Limousin',
            'email' => 'teddy@siat.gov.py',
            'username' => 'teddy',
            'password' => Hash::make('secreto1234'),
            'activo' => 1,
        ])[0]->roles()->attach($rolDirectivo);

        // Usuario Policia Usuario
        $institucionPolicia = Institucion::where('nombre', 'Policía Nacional')->first();
        $sedeId = $institucionPolicia->sedes()->first()->id;
        Usuario::factory()->count(1)->create([
            'sede_id' => $sedeId,
            'genero' => 'Masculino',
            'nombres' => 'Rodrigo',
            'apellidos' => 'Ballesteros',
            'email' => 'usuario@policia.gov.py',
            'username' => 'policia_usuario',
            'password' => Hash::make('123456'),
            'activo' => 1,
        ])[0]->roles()->attach($rolUsuario);
    }
}
