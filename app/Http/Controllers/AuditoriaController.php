<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestUtils;
use App\Models\Login;
use App\Models\Revisions;
use App\Models\Usuario;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    private $route_name = 'auditoria';

    private $params = [];

    public function __construct()
    {
        $this->params = [
            'title' => 'AUDITORIA',
            'subtitle' => 'LISTADO DE REGISTROS DE AUDITORIA',
            'panel_color' => 'panel-midnightblue',
            'search' => 'NO',
            'filter' => '',
            'items_per_page' => 10,
            'route_name' => $this->route_name,
            'route' => '/'.$this->route_name,
            'data' => null,
        ];

        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (! RequestUtils::havePermission($this->route_name, 'any')) {
            return redirect('/home');
        }

        $current_filters = [
            'fecha_inicio' => null,
            'fecha_fin' => null,
            'objeto' => null,
            'usuario' => null,
        ];
        foreach ($current_filters as $field => $value) {
            if ($request->has($field)) {
                $current_filters[$field] = $request->$field;
            }
        }

        $this->params['filter'] = $request->input('filter');
        $this->params['data']['items'] = Revisions::filteredPaginate($request->all(), $this->params['items_per_page']);
        $this->params['data']['objetos'] = [
            '' => 'Todos los Objetos',
            'accidente' => 'Accidente',
            'autorizacion' => 'Autorización',
            'departamento' => 'Departamento',
            'derivacion' => 'Derivación',
            'distrito' => 'Distrito',
            'estado' => 'Estado',
            'grupodepreguntas' => 'Grupo de Preguntas',
            'grupoporinstitucion' => 'Grupo de Preguntas por Institución',
            'institucion' => 'Institución',
            'localidad' => 'Localidad',
            'permiso' => 'Permiso',
            'pregunta' => 'Pregunta',
            'reporte' => 'Reporte',
            'respuesta' => 'Respuesta',
            'rol' => 'Rol',
            'sede' => 'Sede',
            'usuario' => 'Usuario',
        ];
        $usuarios = [null => 'Todos los Usuarios'] + Usuario::orderBy('username')->pluck('username', 'id')->toArray();
        $this->params['data']['usuarios'] = collect($usuarios);
        $this->params['current_filters'] = $current_filters;

        return view($this->route_name.'.index')->with('params', $this->params);
    }

    /**
     * Reporte de Logins
     */
    public function logins(Request $request)
    {
        if (! RequestUtils::havePermission($this->route_name, 'any')) {
            return redirect('/home');
        }

        $current_filters = [
            'fecha_inicio' => null,
            'fecha_fin' => null,
            'accion' => null,
            'usuario' => null,
        ];
        foreach ($current_filters as $field => $value) {
            if ($request->has($field)) {
                $current_filters[$field] = $request->$field;
            }
        }

        $this->params['filter'] = $request->input('filter');
        $this->params['data']['items'] = Login::filteredPaginate($request->all(), $this->params['items_per_page']);
        $this->params['data']['acciones'] = [
            '' => 'Todos las Acciones',
            'login' => 'Inicio Sesión',
            'logout' => 'Cierre Sesión',
        ];
        $usuarios = [null => 'Todos los Usuarios'] + Usuario::orderBy('username')->pluck('username', 'id')->toArray();
        $this->params['data']['usuarios'] = collect($usuarios);
        $this->params['current_filters'] = $current_filters;

        return view($this->route_name.'.logins')->with('params', $this->params);
    }
}
