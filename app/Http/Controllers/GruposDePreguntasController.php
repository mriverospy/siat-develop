<?php

namespace App\Http\Controllers;

use App\Http\Requests\GrupoDePreguntasRequest;
use App\Http\Requests\RequestUtils;
use App\Models\Departamento;
use App\Models\GrupoDePreguntas;
use App\Models\GrupoPorInstitucion;
use App\Models\Institucion;
use Auth;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Log;

class GruposDePreguntasController extends Controller
{
    private $route_name = 'grupos';

    private $params = [];

    public function __construct()
    {
        $this->params = [
            'title' => 'GRUPOS DE PREGUNTAS',
            'subtitle' => 'LISTADO DE GRUPOS',
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

        if (Auth::user()->hasRole('sysadmin')) {
            $this->params['data']['items'] = GrupoDePreguntas::with('preguntas')->filteredPaginate($this->params['filter'], $this->params['items_per_page']);

            return view($this->route_name.'.index')->with('params', $this->params);
        } else {
            return redirect($this->getRouteName());
        }
    }

    public function show($clave)
    {
        if ($clave != 'masters_order') {
            return redirect(url($this->route_parent));
        }

        if (! Auth::user()->hasRole('sysadmin')) {
            return redirect($this->route_name)
                ->withMessages(['type' => 'error', 'text' => 'No posee permisos para realizar esta acción!!']);
        }

        $this->params['data']['items'] = GrupoDePreguntas::where('master', true)->orderBy('orden')->get();

        return view($this->route_name.'.order')->with('params', $this->params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->params['subtitle'] = 'NUEVO GRUPO DE PREGUNTAS';
        $this->params['data']['patern_list'] = Departamento::pluck('nombre', 'id');

        return view('layouts.form_layout')->with('params', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(GrupoDePreguntasRequest $request)
    {
        //Verifica el la institucion seleccionada
        $institucion = $this->get_institucion(null);
        if (! $institucion) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'Institución inválida!!!']);
        }

        $data = $request->all();

        $gdp = new GrupoDePreguntas();
        $gdp->nombre = $data['nombre'];
        $gdp->descripcion = $data['descripcion'];

        try {
            $gdp->save();

            if (! Auth::user()->hasRole('sysadmin')) {
                //Agrega el Grupo de Preguntas
                $gxi = new GrupoPorInstitucion();
                $gxi['institucion_id'] = $institucion->id;
                $gxi['grupo_de_preguntas_id'] = $gdp->id;
                $gxi['orden'] = $gxi->ordenActual();
                $gxi->save();
            }
        } catch (QueryException $e) {
            Log::channel('single')->info($e->getMessage());
            return redirect($this->getRouteName())
                ->withMessages(['type' => 'error', 'text' => 'No se pudo agregar el nuevo registro!!!']);
        }

        return redirect($this->getRouteName())
                ->withMessages(['type' => 'success', 'text' => 'Nuevo Registro creado exitosamente!!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $this->params['data']['item'] = GrupoDePreguntas::findOrFail($id);
        if (! Auth::user()->hasRole('sysadmin')) {
            if (Auth::user()->sede->institucion->gruposPorInstitucion()->where('grupo_de_preguntas_id', $id)->count() == 0) {
                return redirect($this->getRouteName())
                    ->withMessages(['type' => 'error', 'text' => 'Grupo de Preguntas inválido!!']);
            }
        }
        $this->params['subtitle'] = 'EDITAR GRUPO DE PREGUNTAS';
        $this->params['data']['patern_list'] = Departamento::pluck('nombre', 'id');

        return view('layouts.form_layout')->with('params', $this->params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, GrupoDePreguntasRequest $request)
    {
        $row = GrupoDePreguntas::findOrFail($id);

        if (! Auth::user()->hasRole('sysadmin')) {
            if (Auth::user()->sede->institucion->gruposPorInstitucion()->where('grupo_de_preguntas_id', $id)->count() == 0) {
                return redirect($this->getRouteName())
                    ->withMessages(['type' => 'error', 'text' => 'Grupo de Preguntas inválido!!']);
            }
        }

        try {
            $row->update($request->all());
        } catch (QueryException $e) {
            return redirect($this->getRouteName())
                ->withMessages(['type' => 'error', 'text' => 'Las modificaciones No fueron guardadas!!!']);
        }

        return redirect($this->getRouteName())
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
        $row = GrupoDePreguntas::findOrFail($id);

        if (! Auth::user()->hasRole('sysadmin')) {
            if (Auth::user()->sede->institucion->gruposPorInstitucion()->where('grupo_de_preguntas_id', $id)->count() == 0) {
                return redirect($this->getRouteName())
                    ->withMessages(['type' => 'error', 'text' => 'Grupo de Preguntas inválido!!']);
            }
        }

        if ($row->master == 1) {
            return redirect($this->getRouteName())
                    ->withMessages(['type' => 'error', 'text' => 'No se puede eliminar un Grupo Master!!']);
        }

        try {
            $row->delete();
        } catch (QueryException $e) {
            return redirect($this->route_name)
                ->withMessages(['type' => 'error', 'text' => 'El registro seleccionado no puede ser eliminado!!!']);
        }

        return redirect($this->route_name)
                ->withMessages(['type' => 'success', 'text' => 'Registro eliminado exitosamente!!!']);
    }

    /**
     * Obtiene la institucion actual y setea los paramatros principales para la vista
     */
    private function get_institucion($id)
    {
        if ($id == null) {
            $id = Auth::user()->sede->institucion->id;
        }
        try {
            return Institucion::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return false;
        }

        return true;
    }

    private function getRouteName()
    {
        return Auth::user()->hasRole('sysadmin') ? $this->route_name : 'instituciones/'.Auth::user()->sede->institucion->id.'/grupos_por_institucion';
    }

    /**
     * Guarda los cambios realizados al orden de los grupos de preguntas
     */
    public function save_masters_order(Request $request)
    {
        //Verifica que la institucion corresponda a la del usuario
        if (! Auth::user()->hasRole('sysadmin')) {
            return redirect()->back()
                ->withMessages(['type' => 'error', 'text' => 'No posee permisos para realizar esta acción!!']);
        }

        //Verifica que los datos recibidos sean ajax
        if (! $request->ajax()) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'Los datos recibidos no son válidos!!!']);
        }

        $data = $request->all();
        $newList = $data['items'];

        try {
            foreach ($newList as $n) {
                GrupoDePreguntas::where('id', '=', $n['id'])->where('master', true)->update(['orden' => $n['orden']]);
            }
        } catch (QueryException $e) {
            Session::flash('messages', ['type' => 'error', 'text' => 'No se pudieron guardar los cambios!!!']);

            return 'ERROR';
        }
        Session::flash('messages', ['type' => 'success', 'text' => 'El nuevo orden de los Grupos de Preguntas ha sido guardado!!!']);

        return 'OK';
    }

    private function reordenarMasters()
    {
        $list = GrupoDePreguntas::where('master', true)->orderBy('orden')->get();
        foreach ($list as $key => $item) {
            $item['orden'] = $key + 1;
            $item->save();
        }
    }

    public function set_master($id)
    {
        try {
            $grupo = GrupoDePreguntas::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                    ->withMessages(['type' => 'error', 'text' => 'Los datos recibidos no son válidos!!!']);
        }

        $gxi = $grupo->grupoPorInstitucion()->get();

        if (count($gxi) > 0) {
            return redirect()->back()
                    ->withMessages(['type' => 'error', 'text' => 'Este Grupo ya esta asignado a una Institución, no se puede establecer como Master!']);
        }

        $grupo->master = 1;
        $grupo->orden = $grupo->ordenActual();

        try {
            $grupo->save();
        } catch (QueryException $e) {
            return redirect()->back()
                    ->withMessages(['type' => 'error', 'text' => 'No se pudo establecer como Master al Grupo!!!']);
        }

        return redirect()->back()
                    ->withMessages(['type' => 'success', 'text' => 'Grupo establecido como Master!!!']);
    }

    public function unset_master($id)
    {
        try {
            $grupo = GrupoDePreguntas::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                    ->withMessages(['type' => 'error', 'text' => 'Los datos recibidos no son válidos!!!']);
        }

        if ($grupo->master == 0) {
            return redirect()->back()
                    ->withMessages(['type' => 'error', 'text' => 'Este Grupo no es Master!!!']);
        }

        $grupo->master = 0;
        $grupo->orden = null;

        try {
            $grupo->save();
        } catch (QueryException $e) {
            return redirect()->back()
                    ->withMessages(['type' => 'error', 'text' => 'No se pudo restablecer como Normal el Grupo!!!']);
        }

        $this->reordenarMasters();

        return redirect()->back()
                    ->withMessages(['type' => 'success', 'text' => 'Grupo restablecido como Normal!!!']);
    }
}
