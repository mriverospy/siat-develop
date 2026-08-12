<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Tables to truncate
     *
     * @var array
     */
    protected $tables = [
        'logins',
        'reportes',
        'revisions',
        'autorizaciones',
        'derivaciones',
        'grupos_por_instituciones',
        'instituciones',
        'departamentos',
        'distritos',
        'localidades',
        'sedes',
        'permiso_rol',
        'roles',
        'permisos',
        'usuarios',
        'rol_usuario',
        'estados',
        'accidentes',
        'grupos_de_preguntas',
        'preguntas',
        'respuestas',
        'jobs',
        'password_resets',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->cleanDatabase();
        $this->call([
            InstitucionTableSeeder::class,
            LocalidadTableSeeder::class,
            SedeTableSeeder::class,
            RolTableSeeder::class,
            PermisoTableSeeder::class,
            UsuarioTableSeeder::class,
            EstadoTableSeeder::class,
            PreguntasYGruposSeeder::class,
            RespuestasSeeder::class,
        ]);

        // "Corregimos" log de auditoria asignando todo a user 1
        // el seeder se ejecuta sin un usuario logueado
        DB::statement('UPDATE `revisions` SET `user_id` = 1 WHERE `user_id` IS NULL');

        Model::reguard();
    }

    /**
     * Cleans database
     *
     * @return void
     */
    public function cleanDatabase()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($this->tables as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
