<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocalidadRequest;
use App\Http\Requests\RequestUtils;
use App\Models\Distrito;
use App\Models\Localidad;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class LocalidadesController extends Controller
{
    private $route_name = 'localidades';

    private $params = [];

    public function __construct()
    {
        $this->params = [
            'title' => 'LOCALIDADES',
            'subtitle' => 'LISTADO DE LOCALIDADES',
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
        $this->params['data']['items'] = Localidad::filteredPaginate($this->params['filter'], $this->params['items_per_page']);

        return view($this->route_name.'.index')->with('params', $this->params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $distritos = Distrito::with('departamento')
            ->orderBy('nombre')
            ->get();

        $distritosSelect = [];
        foreach ($distritos as $distrito) {
            $distritosSelect[$distrito->id] = $distrito->nombre.' - '.$distrito->departamento->nombre;
        }

        $this->params['subtitle'] = 'NUEVA LOCALIDAD';
        $this->params['data']['patern_list'] = $distritosSelect;

        return view('layouts.form_layout')->with('params', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(LocalidadRequest $request)
    {
        try {
            Localidad::create($request->all());
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
        $distritos = Distrito::with('departamento')
            ->orderBy('nombre')
            ->get();

        $distritosSelect = [];
        foreach ($distritos as $distrito) {
            $distritosSelect[$distrito->id] = $distrito->nombre.' - '.$distrito->departamento->nombre;
        }

        $this->params['subtitle'] = 'EDITAR LOCALIDAD';
        $this->params['data']['patern_list'] = $distritosSelect;
        $this->params['data']['item'] = Localidad::findOrFail($id);

        return view('layouts.form_layout')->with('params', $this->params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, LocalidadRequest $request)
    {
        $row = Localidad::findOrFail($id);

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
        $row = Localidad::findOrFail($id);

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
