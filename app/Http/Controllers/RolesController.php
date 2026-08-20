<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestUtils;
use App\Http\Requests\RolRequest;
use App\Models\Rol;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    /**
     * Ruta
     *
     * @var string
     */
    private $route_name = 'roles';

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
            'title' => 'ROLES',
            'subtitle' => 'LISTADO DE ROLES DE USUARIO',
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
        if (! RequestUtils::havePermission($this->route_name, 'any')) {
            return redirect('/home');
        }

        $this->params['filter'] = $request->input('filter');
        $this->params['data']['items'] = Rol::filteredPaginate($this->params['filter'], $this->params['items_per_page']);

        return view($this->route_name.'.index')->with('params', $this->params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->params['subtitle'] = 'NUEVO ROL DE USUARIO';

        return view('layouts.form_layout')->with('params', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(RolRequest $request)
    {
        try {
            Rol::create($request->all());
        } catch (QueryException $e) {
            return redirect($this->route_name)
                ->withMessages(['type' => 'error', 'text' => 'No se pudo agregar el nuevo registro!!!']);
        }

        return redirect($this->route_name)
                ->withMessages(['type' => 'success', 'text' => 'Nuevo Registro creado exitosamente!!!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        return redirect($this->route_name);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $this->params['subtitle'] = 'EDITAR ROL DE USUARIO';
        $this->params['data']['item'] = Rol::findOrFail($id);

        return view('layouts.form_layout')->with('params', $this->params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, RolRequest $request)
    {
        $row = Rol::findOrFail($id);

        try {
            $row->update($request->all());
        } catch (QueryException $e) {
            return redirect($this->route_name)
                ->withMessages(['type' => 'error', 'text' => 'Las modificaciones No fueron guardadas!!!']);
        }

        return redirect($this->route_name)
                ->withMessages(['type' => 'success', 'text' => 'Registro actualizado con éxito!!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $row = Rol::with(['usuarios', 'permisos'])->findOrFail($id);

        if ($row->usuarios->count() > 0 or $row->permisos->count() > 0) {
            return redirect($this->route_name)
                    ->withMessages([
                        'type' => 'error',
                        'text' => "Rol {$row->name} no se puede borrar, existen {$row->usuarios->count()} usuario(s) asignados y {$row->permisos->count()} permiso(s) asignados a este rol.",
                    ]);
        }

        try {
            $row->delete();
        } catch (Exception $e) {
            return redirect($this->route_name)
                    ->withMessages(['type' => 'error', 'text' => 'El registro seleccionado no puede ser eliminado!!!'.$e]);
        }

        return redirect($this->route_name)
                    ->withMessages(['type' => 'success', 'text' => 'Registro eliminado exitosamente!!!']);
    }
}
