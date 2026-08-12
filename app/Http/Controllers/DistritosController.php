<?php

namespace App\Http\Controllers;

use App\Http\Requests\DistritoRequest;
use App\Http\Requests\RequestUtils;
use App\Models\Departamento;
use App\Models\Distrito;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class DistritosController extends Controller
{
    private $route_name = 'distritos';

    private $params = [];

    public function __construct()
    {
        $this->params = [
            'title' => 'DISTRITOS',
            'subtitle' => 'LISTADO DE DISTRITOS',
            'panel_color' => 'panel-midnightblue',
            'search' => 'YES',
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
        $this->params['data']['items'] = Distrito::filteredPaginate($this->params['filter'], $this->params['items_per_page']);

        return view($this->route_name.'.index')->with('params', $this->params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->params['subtitle'] = 'NUEVO DISTRITO';
        $this->params['data']['patern_list'] = Departamento::pluck('nombre', 'id');

        return view('layouts.form_layout')->with('params', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(DistritoRequest $request)
    {
        try {
            Distrito::create($request->all());
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
        $this->params['subtitle'] = 'EDITAR DISTRITO';
        $this->params['data']['patern_list'] = Departamento::pluck('nombre', 'id');
        $this->params['data']['item'] = Distrito::findOrFail($id);

        return view('layouts.form_layout')->with('params', $this->params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, DistritoRequest $request)
    {
        $row = Distrito::findOrFail($id);

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
        $row = Distrito::findOrFail($id);

        try {
            $row->delete();
        } catch (QueryException $e) {
            return redirect($this->route_name)
                ->withMessages(['type' => 'error', 'text' => 'El registro seleccionado no puede ser eliminado!!!']);
        }

        return redirect($this->route_name)
                ->withMessages(['type' => 'success', 'text' => 'Registro eliminado exitosamente!!!']);
    }
}
