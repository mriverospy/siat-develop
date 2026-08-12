<?php

namespace App\Http\Controllers;

use App\Http\Requests\InstitucionRequest;
use App\Http\Requests\RequestUtils;
use App\Models\Institucion;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Storage;

class InstitucionesController extends Controller
{
    private $route_name = 'instituciones';

    private $params = [];

    public function __construct()
    {
        $this->params = [
            'title' => 'INSTITUCIONES',
            'subtitle' => 'LISTADO DE INSTITUCIONES',
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
        $this->params['data']['items'] = Institucion::orderBy('nombre')->filteredPaginate($this->params['filter'], $this->params['items_per_page']);

        return view($this->route_name.'.index')->with('params', $this->params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->params['subtitle'] = 'NUEVA INSTITUCION';
        $this->params['files'] = true;

        return view('layouts.form_layout')->with('params', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(InstitucionRequest $request)
    {
        try {
            $item = Institucion::create($request->all());
        } catch (QueryException $e) {
            return redirect($this->route_name)
                ->withMessages(['type' => 'error', 'text' => 'No se pudo agregar el nuevo registro!!!']);
        }

        // Verificar si se seleccionó un logo
        if ($request->hasFile('logo')) {
            $targetPath = public_path().'/logos-instituciones';
            $baseFileName = Str::random(30);
            $targetFileName = $item->id.'-'.$baseFileName.'.'.$request->file('logo')->getClientOriginalExtension();

            Storage::makeDirectory($targetPath);

            $request->file('logo')->move($targetPath, $targetFileName);

            $item->logo = str_replace(public_path(), '', $targetPath).$targetFileName;
            $item->save();
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
        $this->params['subtitle'] = 'EDITAR INSTITUCION';
        $this->params['files'] = true;
        $this->params['data'] = ['item' => Institucion::findOrFail($id)];

        return view('layouts.form_layout')->with('params', $this->params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, InstitucionRequest $request)
    {
        $item = Institucion::findOrFail($id);

        try {
            $data = $request->only(['nombre', 'descripcion', 'dias_edicion']);
            if (trim($data['dias_edicion']) == '') {
                $data['dias_edicion'] = null;
            }
            $item->update($data);
        } catch (QueryException $e) {
            return redirect($this->route_name)
                ->withMessages(['type' => 'error', 'text' => 'Las modificaciones No fueron guardadas!!!']);
        }

        // Verificar si se seleccionó un logo
        if ($request->hasFile('logo')) {
            $item = Institucion::findOrFail($id);

            $targetPath = public_path().'/logos-instituciones/';
            $baseFileName = Str::random(30);
            $targetFileName = $item->id.'-'.$baseFileName.'.'.$request->file('logo')->getClientOriginalExtension();

            // Borrar logo anterior, si existe
            if ($item->logo != '' and file_exists(public_path().$item->logo)) {
                @unlink(public_path().$item->logo);
            }

            Storage::makeDirectory($targetPath);

            $request->file('logo')->move($targetPath, $targetFileName);

            $item->logo = str_replace(public_path(), '', $targetPath).$targetFileName;
            $item->save();
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
        $row = Institucion::findOrFail($id);

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
