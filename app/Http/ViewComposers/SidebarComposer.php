<?php

namespace App\Http\ViewComposers;

use Auth;
use Illuminate\Contracts\View\View;

class SidebarComposer
{
    /**
     * Create a new sidebar composer.
     *
     * @param  AuthManager  $auth
     * @return void
     */
    public function __construct()
    {
        // Do nothing for now
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     **/
    public function compose(View $view)
    {
        $mainMenu = [];

        // Dashboard
        $mainMenu[] = [
            'is_home' => true,
            'title' => 'Dashboard',
            'url' => url('/'),
            'route' => null,
            'container_classes' => null,
            'anchor_classes' => null,
            'icon_classes' => 'fa fa-home',
            'items' => [],
        ];

        /**
         * Accidentes
         */
        $subItems = [];

        // Crear Accidente
        if (Auth::user()->ability('sysadmin', 'accidentes-create')) {
            $subItems[] = [
                'title' => 'Cargar Accidente',
                'url' => url('accidentes/cargar'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-fw fa-plus',
            ];
        }

        // Listar Accidentes
        if (Auth::user()->ability('sysadmin', 'accidentes-list,accidentes-create,accidentes-edit,accidentes-delete,accidentes-import')) {
            $subItems[] = [
                'title' => 'Accidentes',
                'url' => url('accidentes'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-fw fa-car',
            ];

            $subItems[] = [
                'title' => 'Busqueda por Indicadores',
                'url' => url('accidentes/busqueda-por-indicadores'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-fw fa-search',
            ];
        }

        // Formulario Único en Blanco
        $subItems[] = [
            'title' => 'Formulario Único en Blanco',
            'url' => url('accidentes/bajar-formulario-unico'),
            'route' => null,
            'container_classes' => null,
            'anchor_classes' => null,
            'icon_classes' => 'fa fa-fw fa-file-pdf-o',
        ];

        // Formulario Anexo Vehículos
        $subItems[] = [
            'title' => 'Bajar Anexo Vehículos',
            'url' => url('accidentes/bajar-anexo-vehiculos'),
            'route' => null,
            'container_classes' => null,
            'anchor_classes' => null,
            'icon_classes' => 'fa fa-fw fa-file-pdf-o',
        ];

        // Formulario Anexo Personas
        $subItems[] = [
            'title' => 'Bajar Anexo Personas',
            'url' => url('accidentes/bajar-anexo-personas'),
            'route' => null,
            'container_classes' => null,
            'anchor_classes' => null,
            'icon_classes' => 'fa fa-fw fa-file-pdf-o',
        ];

        // Importar Datos
        if (Auth::user()->ability('sysadmin', 'accidentes-import')) {
            $subItems[] = [
                'title' => 'Importar Datos',
                'url' => url('accidentes/importar'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-fw fa-upload',
            ];
        }

        // Menu principal accidentes
        $mainMenu[] = [
            'is_home' => false,
            'title' => 'Accidentes',
            'url' => 'javascript:;',
            'route' => null,
            'container_classes' => null,
            'anchor_classes' => null,
            'icon_classes' => 'fa fa-road',
            'items' => $subItems,
        ];

        /**
         * Derivaciones
         */
        $subItems = [];

        // Derivaciones enviadas
        if (Auth::user()->ability('sysadmin', 'accidentes-send')) {
            $subItems[] = [
                'title' => 'Derivaciones Enviadas',
                'url' => url('derivaciones/sent'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-fw fa-level-down',
            ];
        }

        // Derivaciones recibidas
        if (Auth::user()->ability('sysadmin', 'accidentes-receive')) {
            $subItems[] = [
                'title' => 'Derivaciones Recibidas',
                'url' => url('derivaciones/received'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-fw fa-level-up',
            ];
        }

        // Menu principal derivaciones
        $mainMenu[] = [
            'is_home' => false,
            'title' => 'Derivaciones',
            'url' => 'javascript:;',
            'route' => null,
            'container_classes' => null,
            'anchor_classes' => null,
            'icon_classes' => 'fa fa-retweet',
            'items' => $subItems,
        ];

        /**
         * Autorizaciones
         */
        $subItems = [];

        // Autorizaciones enviadas
        if (Auth::user()->ability('sysadmin', 'accidentes-send-authorization')) {
            $subItems[] = [
                'title' => 'Autorizaciones Enviadas',
                'url' => url('autorizaciones/sent'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-fw fa-unlock',
            ];
        }

        // Autorizaciones recibidas
        if (Auth::user()->ability('sysadmin', 'accidentes-receive-authorization')) {
            $subItems[] = [
                'title' => 'Autorizaciones Recibidas',
                'url' => url('autorizaciones/received'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-fw fa-unlock-alt',
            ];
        }

        // Menu principal autorizaciones
        $mainMenu[] = [
            'is_home' => false,
            'title' => 'Autorizaciones',
            'url' => 'javascript:;',
            'route' => null,
            'container_classes' => null,
            'anchor_classes' => null,
            'icon_classes' => 'fa fa-unlock-alt',
            'items' => $subItems,
        ];

        /**
         * Reportes
         */
        $subItems = [];

        if (Auth::user()->ability('sysadmin', 'reportes-list')) {
            $subItems[] = [
                'title' => 'Reportes Gráficos',
                'url' => url('reportes'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-bar-chart',
            ];

            $subItems[] = [
                'title' => 'Reporte de Datos',
                'url' => url('reporte_datos'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-book',
            ];

            $mainMenu[] = [
                'is_home' => false,
                'title' => 'Reportes',
                'url' => 'javascript:;',
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-line-chart',
                'items' => $subItems,
            ];
        }

        /*
         * Reportes Genéricos
         */
        $subItems = [];

        if (Auth::user()->ability('sysadmin', 'reportes_genericos-list')) {
            $subItems[] = [
                'title' => 'Carga Registros por Institución',
                'url' => url('reportes_genericos/carga_registros_por_institucion'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-book',
            ];

            $subItems[] = [
                'title' => 'Total Accidentes',
                'url' => url('reportes_genericos/total_accidentes'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-book',
            ];

            $subItems[] = [
                'title' => 'Ubicación Accidentes',
                'url' => url('reportes_genericos/ubicacion_accidentes'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-map-marker',
            ];

            $subItems[] = [
                'title' => 'Totales por Indicador',
                'url' => url('reportes_genericos/totales_por_indicador'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-car',
            ];

            $mainMenu[] = [
                'is_home' => false,
                'title' => 'Reportes Genéricos',
                'url' => 'javascript:;',
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-line-chart',
                'items' => $subItems,
            ];
        }

        /**
         * Auditoría
         */
        $subItems = [];

        if (Auth::user()->ability('sysadmin', 'auditoria-list')) {
            $subItems[] = [
                'title' => 'Auditoría',
                'url' => url('auditoria'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => null,
            ];

            $subItems[] = [
                'title' => 'Registro de Sesiones',
                'url' => url('auditoria/logins'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => null,
            ];
        }

        if (Auth::user()->ability('sysadmin', 'auditoria-list') and count($subItems) > 0) {
            $mainMenu[] = [
                'is_home' => false,
                'title' => 'Auditoría',
                'url' => 'javascript:;',
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => 'fa fa-eye',
                'items' => $subItems,
            ];
        }

        /**
         * Mantenimiento
         */
        $subItems = [];

        if (Auth::user()->hasRole('sysadmin')) {
            $subItems[] = [
                'title' => 'Instituciones',
                'url' => url('instituciones'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => null,
            ];
        }

        if (Auth::user()->ability('sysadmin', 'sedes-list,sedes-create,sedes-edit,sedes-delete')) {
            $subItems[] = [
                'title' => 'Sedes',
                'url' => url('sedes'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => null,
            ];
        }

        if (Auth::user()->ability('sysadmin', 'departamentos-list,departamentos-create,departamentos-edit,departamentos-delete')) {
            $subItems[] = [
                'title' => 'Departamentos',
                'url' => url('departamentos'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => null,
            ];
        }

        if (Auth::user()->ability('sysadmin', 'distritos-list,distritos-create,distritos-edit,distritos-delete')) {
            $subItems[] = [
                'title' => 'Distritos',
                'url' => url('distritos'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => null,
            ];
        }

        if (Auth::user()->ability('sysadmin', 'localidades-list,localidades-create,localidades-edit,localidades-delete')) {
            $subItems[] = [
                'title' => 'Localidades',
                'url' => url('localidades'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => null,
            ];
        }

        if (Auth::user()->ability('sysadmin', 'usuarios-list,usuarios-create,usuarios-edit,usuarios-delete')) {
            $subItems[] = [
                'title' => 'Usuarios',
                'url' => url('usuarios'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => null,
            ];
        }

        if (Auth::user()->hasRole('sysadmin')) {
            $subItems[] = [
                'title' => 'Roles de Usuarios',
                'url' => url('roles'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => null,
            ];
        }

        if (Auth::user()->ability('sysadmin', 'grupos-list,grupos-create,grupos-edit,grupos-delete')) {
            $subItems[] = [
                'title' => 'Grupos de Preguntas',
                'url' => url('grupos'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => null,
            ];
        }

        // if (Auth::user()->hasRole('sysadmin'))
        // {
        //     $subItems[] = [
        //         'title' => 'Estados de Accidentes',
        //         'url' => url('estados'),
        //         'route' => null,
        //         'container_classes' => null,
        //         'anchor_classes' => null,
        //         'icon_classes' => null
        //     ];
        // }

        if (Auth::user()->ability('sysadmin', 'api_soap-view,api_soap-edit')) {
            $subItems[] = [
                'title' => 'SOAP Web Service',
                'url' => url('api_soap'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => null,
            ];
        }

        if (Auth::user()->hasRole('sysadmin')) {
            $subItems[] = [
                'title' => 'Configuración',
                'url' => url('configuracion'),
                'route' => null,
                'container_classes' => null,
                'anchor_classes' => null,
                'icon_classes' => null,
            ];
        }

        // Menu principal mantenimiento
        $mainMenu[] = [
            'is_home' => false,
            'title' => 'Mantenimiento',
            'url' => 'javascript:;',
            'route' => null,
            'container_classes' => null,
            'anchor_classes' => null,
            'icon_classes' => 'fa fa-wrench',
            'items' => $subItems,
        ];

/**
 * Documentos
 */
$subItems = [];

// Diccionario de variables
$subItems[] = [
    'title' => 'Diccionario de variables ',
    'url' => route('diccionario.pdf'),
    'route' => null,
    'container_classes' => null,
    'anchor_classes' => null,
    'icon_classes' => 'fa fa-book',
];

// Manual de uso del sistema
$subItems[] = [
    'title' => 'Manual de uso del sistema',
    'url' => route('download.pdf'),
    'route' => null,
    'container_classes' => null,
    'anchor_classes' => null,
    'icon_classes' => 'fa fa-fw fa-file-pdf-o',
];

$mainMenu[] = [
    'is_home' => false,
    'title' => 'Documentos',
    'url' => 'javascript:;',
    'route' => null,
    'container_classes' => null,
    'anchor_classes' => null,
    'icon_classes' => 'fa fa-book',
    'items' => $subItems,
];

$view->with('mainMenu', $mainMenu);

    }
}
