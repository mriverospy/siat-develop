<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class PermisoTableSeeder extends Seeder
{
    /**
     * Lista por defecto de permisos
     *
     * @return array
     */
    private function default_list()
    {
        $all = [
            ['name' => 'list',      'display_name' => 'Listar'],
            ['name' => 'create',    'display_name' => 'Crear'],
            ['name' => 'edit',      'display_name' => 'Editar'],
            ['name' => 'delete',    'display_name' => 'Borrar'],
        ];

        $accidente_specific = [
            ['name' => 'import',            'display_name' => 'Importar'],
            ['name' => 'pdf-report',        'display_name' => 'Generar Informe'],
            ['name' => 'send',              'display_name' => 'Derivar'],
            ['name' => 'receive',           'display_name' => 'Recibir Derivación'],
            ['name' => 'send-authorization',    'display_name' => 'Autorizar Cambios'],
            ['name' => 'receive-authorization', 'display_name' => 'Recibir Autorización de Cambios'],
        ];

        $list = [
            // Mantenimiento
            // ['name' => 'instituciones',             'display_name' => 'Instituciones',          'permisos' => $all],
            ['name' => 'sedes',                     'display_name' => 'Sedes',                  'permisos' => $all],
            ['name' => 'departamentos',             'display_name' => 'Departamentos',          'permisos' => $all],
            ['name' => 'distritos',                 'display_name' => 'Distritos',              'permisos' => $all],
            ['name' => 'localidades',               'display_name' => 'Localidades',            'permisos' => $all],
            ['name' => 'usuarios',                  'display_name' => 'Usuarios',               'permisos' => $all],
            // ['name' => 'roles',                     'display_name' => 'Roles de Usuarios',      'permisos' => $all],
            ['name' => 'permisos',                  'display_name' => 'Permisos por Rol',       'permisos' => [['name' => 'edit', 'display_name' => 'Editar']]],
            ['name' => 'grupos',                    'display_name' => 'Grupos de Preguntas',    'permisos' => array_merge($all, [['name' => 'nest', 'display_name' => 'Agrupar']])],
            ['name' => 'grupos_por_institucion',    'display_name' => 'Grupos por Institución', 'permisos' => $all],
            ['name' => 'preguntas',                 'display_name' => 'Preguntas',              'permisos' => $all],
            ['name' => 'accidentes',                'display_name' => 'Accidentes',             'permisos' => array_merge($all, $accidente_specific)],
            ['name' => 'respuestas',                'display_name' => 'Respuestas',             'permisos' => $all],
            ['name' => 'api_soap',                  'display_name' => 'Api Soap',               'permisos' => [['name' => 'view', 'display_name' => 'Ver'], ['name' => 'edit', 'display_name' => 'Editar']]],
            ['name' => 'auditoria',                 'display_name' => 'Auditoría',              'permisos' => [['name' => 'list', 'display_name' => 'Listar']]],
            ['name' => 'reportes',                  'display_name' => 'Reportes',               'permisos' => array_merge($all, [['name' => 'show', 'display_name' => 'Ejecutar']])],
        ];

        return $list;
    }

    public function default_permissions()
    {
        return [
            'administrador' => [
                'accidentes-create',
                'accidentes-edit',
                'accidentes-import',
                'accidentes-list',
                'accidentes-pdf-report',
                'accidentes-receive',
                'accidentes-receive-authorization',
                'accidentes-send',
                'accidentes-send-authorization',
                'api_soap-edit',
                'api_soap-view',
                'auditoria-list',
                'grupos_por_institucion-create',
                'grupos_por_institucion-delete',
                'grupos_por_institucion-edit',
                'grupos_por_institucion-list',
                'grupos-create',
                'grupos-delete',
                'grupos-edit',
                'grupos-list',
                'grupos-nest',
                'preguntas-create',
                'preguntas-delete',
                'preguntas-edit',
                'preguntas-list',
                'reportes-create',
                'reportes-delete',
                'reportes-edit',
                'reportes-list',
                'reportes-show',
                'respuestas-create',
                'respuestas-edit',
                'respuestas-list',
                'sedes-create',
                'sedes-delete',
                'sedes-edit',
                'sedes-list',
                'usuarios-create',
                'usuarios-delete',
                'usuarios-edit',
                'usuarios-list',
            ],

            'agente' => [
                'accidentes-create',
                'accidentes-edit',
                'accidentes-list',
                'accidentes-pdf-report',
                'accidentes-receive',
                'accidentes-receive-authorization',
                'accidentes-send',
                'preguntas-list',
                'reportes-create',
                'reportes-delete',
                'reportes-edit',
                'reportes-list',
                'reportes-show',
                'respuestas-create',
                'respuestas-edit',
                'respuestas-list',
            ],

            'directivo' => [
                'accidentes-create',
                'accidentes-edit',
                'accidentes-import',
                'accidentes-list',
                'accidentes-pdf-report',
                'accidentes-receive',
                'accidentes-receive-authorization',
                'accidentes-send',
                'accidentes-send-authorization',
                'api_soap-edit',
                'api_soap-view',
                'reportes-create',
                'reportes-delete',
                'reportes-edit',
                'reportes-list',
                'reportes-show',
                'respuestas-create',
                'respuestas-edit',
                'respuestas-list',
            ],

            'usuario' => [
                'accidentes-edit',
                'accidentes-list',
                'accidentes-pdf-report',
                'accidentes-receive',
                'accidentes-send',
                'grupos-list',
                'preguntas-list',
                'reportes-create',
                'reportes-delete',
                'reportes-edit',
                'reportes-list',
                'reportes-show',
                'respuestas-list',
            ],

            'consulta' => [
                'accidentes-list',
                'accidentes-pdf-report',
                'sedes-list'
            ]
        ];
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = Rol::where('name', '!=', 'sysadmin')->get();

        $list = $this->default_list();
        $defaultPermissions = $this->default_permissions();
        foreach ($list as $item) {
            foreach ($item['permisos'] as $p) {
                $permiso = Permiso::create(['name' => $item['name'].'-'.$p['name'], 'display_name' => $p['display_name'].' '.$item['display_name']]);

                foreach ($roles as $rol) {
                    if (isset($defaultPermissions[$rol->name]) and in_array($item['name'].'-'.$p['name'], $defaultPermissions[$rol->name])) {
                        $rol->attachPermission($permiso);
                    }
                }
            }
        }
    }
}
