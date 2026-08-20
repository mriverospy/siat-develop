<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestUtils;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class PermisosController extends Controller
{
    /**
     * Ruta
     *
     * @var string
     */
    private $route_name = 'permisos';

    /**
     * Ruta padre
     *
     * @var string
     */
    private $route_parent = '/roles';

    /**
     * Título
     *
     * @var string
     */
    private $title = 'PERMISOS POR ROL';

    /**
     * Parámetros
     *
     * @var array
     */
    private $params = [];

    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        $this->params = [
            'title' => $this->title,
            'subtitle' => 'PERMISOS DEL ROL',
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
     * @return Response
     */
    public function index(Request $request)
    {
        // Verifica permiso
        if (! RequestUtils::havePermission($this->route_name, 'edit')) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        // Verifica el ROL
        if (! $this->get_rol($request->segment(2))) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'El Rol seleccionado es inválido!!!']);
        }

        $this->params['data']['items'] = Permiso::select('permisos.*', 'permiso_rol.rol_id')
            ->leftJoin('permiso_rol', function ($join) {
                $join->on('permisos.id', '=', 'permiso_rol.permiso_id')
                     ->where('permiso_rol.rol_id', '=', $this->params['data']['rol']['id']);
            })
            ->orderBy('name')
            ->get();

        return view($this->route_name.'.index')->with('params', $this->params);
    }

    /**
     * Guarda los cambios realizados al orden de los grupos de preguntas
     */
    public function save_permisos($rol_id, Request $request)
    {
        // Verifica permiso
        if (! RequestUtils::havePermission($this->route_name, 'edit')) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        // Verifica el Grupo de Preguntas
        if (! $this->get_rol($rol_id)) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'El Rol seleccionado es inválido!!!']);
        }

        $data = $request->all();
        $newList = $data['permisos'];

        try {
            $this->params['data']['rol']->permissions()->sync($newList);
        } catch (QueryException $e) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'No se pudieron guardar los cambios!!!'.$e]);
        }

        return redirect($this->params['route'])
                ->withMessages(['type' => 'success', 'text' => 'Los Permisos han sido guardados!!!']);
    }

    /**
     * Obtiene la institucion actual y setea los paramatros principales para la vista
     */
    private function get_rol($route_id)
    {
        try {
            $this->params['data']['rol'] = Rol::findOrFail($route_id);
        } catch (ModelNotFoundException $e) {
            return false;
        }
        $this->params['title'] = $this->title."<br><span class='h5'>".$this->params['data']['rol']['display_name'].'</span>';
        $this->params['route'] = $this->route_parent.'/'.$route_id.'/'.$this->route_name;

        return true;
    }
}
