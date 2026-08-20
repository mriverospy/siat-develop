<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestUtils;
use App\Models\GrupoDePreguntas;
use App\Models\GrupoPorInstitucion;
use App\Models\Institucion;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Session;

class GruposPorInstitucionController extends Controller
{
    private $route_name = 'grupos_por_institucion';

    private $route_parent = '/instituciones';

    private $title = 'GRUPOS POR INSTITUCION';

    private $params = [];

    public function __construct()
    {
        $this->params = [
            'title' => $this->title,
            'subtitle' => 'LISTADO DE GRUPOS',
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

        //Verifica que la institucion corresponda a la del usuario
        if (! Auth::user()->hasRole('sysadmin')) {
            if (Auth::user()->sede->institucion->id != $request->segment(2)) {
                return redirect('/home')
                    ->withMessages(['type' => 'error', 'text' => 'Institución inválida!!']);
            }
        }

        //Verifica el Grupo de Preguntas
        if (! $this->get_institucion($request->segment(2))) {
            return redirect(url('/home'))
                ->withMessages(['type' => 'error', 'text' => 'No se pudo determinar la Institución!!!']);
        }

        $this->params['filter'] = $request->input('filter');
        $this->params['data']['items'] = $this->params['data']['institucion']->gruposPorInstitucion()->orderBy('orden')->get();

        return view($this->route_name.'.index')->with('params', $this->params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($institucion_id)
    {
        // Verifica que la institucion corresponda a la del usuario
        if (! Auth::user()->hasRole('sysadmin')) {
            if (Auth::user()->sede->institucion->id != $institucion_id) {
                return redirect('/home')
                    ->withMessages(['type' => 'error', 'text' => 'Institución inválida!!']);
            }
        }

        // Verifica el Grupo de Preguntas
        if (! $this->get_institucion($institucion_id)) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'La Institución seleccionada no es válida!!!']);
        }

        $this->params['subtitle'] = 'AGREGAR GRUPO DE PREGUNTAS';
        $this->params['data']['items'] = GrupoDePreguntas::where('master', false)->whereNotIn('id', $this->params['data']['institucion']->gruposPorInstitucion()->select('grupo_de_preguntas_id')->pluck('grupo_de_preguntas_id'))->get();

        return view($this->route_name.'.add')->with('params', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store($institucion_id, $grupo_id)
    {
        //Verifica que la institucion corresponda a la del usuario
        if (! Auth::user()->hasRole('sysadmin')) {
            if (Auth::user()->sede->institucion->id != $institucion_id) {
                return redirect('/home')
                    ->withMessages(['type' => 'error', 'text' => 'Institución inválida!!']);
            }
        }

        // Verifica la institucion seleccionada
        if (! $this->get_institucion($institucion_id)) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'El grupo de preguntas seleccionado es inválido!!!']);
        }

        // Verifica si el grupo de preguntas no existe en la institucion
        if ($this->params['data']['institucion']->gruposPorInstitucion()->where('grupo_de_preguntas_id', $grupo_id)->count() > 0) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'El Grupo de Preguntas seleccionado ya se encuentra asociado a esta Institución!!!']);
        }

        // Verifica si el grupo seleccionado existe
        try {
            $grupoDePreguntas = GrupoDePreguntas::findOrFail($grupo_id);
        } catch (ModelNotFoundException $e) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'No encontró el registro solicitado!!!']);
        }

        if ($grupoDePreguntas->master == 1) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'No puede agregar un Grupo Master a una Institución!!!']);
        }

        // Agrega el Grupo de Preguntas
        $gxi = new GrupoPorInstitucion();
        $gxi['institucion_id'] = $this->params['data']['institucion']['id'];
        $gxi['grupo_de_preguntas_id'] = $grupo_id;
        $gxi['orden'] = $gxi->ordenActual();

        try {
            $gxi->save();
        } catch (QueryException $e) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'No se pudo agregar el nuevo Grupo de Preguntas!!!']);
        }

        return redirect($this->params['route'])
                ->withMessages(['type' => 'success', 'text' => 'Nuevo Grupo de Preguntas agregado exitosamente!!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($institucion_id, $grupo_id)
    {
        //Verifica que la institucion corresponda a la del usuario
        if (! Auth::user()->hasRole('sysadmin')) {
            if (Auth::user()->sede->institucion->id != $institucion_id) {
                return redirect('/home')
                    ->withMessages(['type' => 'error', 'text' => 'Institución inválida!!']);
            }
        }

        //Verifica el Grupo de Preguntas
        if (! $this->get_institucion($institucion_id)) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'El grupo de preguntas seleccionado es inválido!!!']);
        }

        //Verifica si el grupo seleccionado existe
        try {
            $grupoDePreguntas = GrupoDePreguntas::findOrFail($grupo_id);
        } catch (ModelNotFoundException $e) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'No encontró el registro solicitado!!!']);
        }

        //Elimina el registro
        try {
            $this->params['data']['institucion']->grupoDePreguntas()->detach($grupoDePreguntas);
            $this->reordenarGrupos();
        } catch (QueryException $e) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'El Grupo de Preguntas seleccionado no puede ser removido!!!']);
        }

        return redirect($this->params['route'])
                ->withMessages(['type' => 'success', 'text' => 'El Grupo de Preguntas ha sido removido exitosamente!!!']);
    }

    /**
     * Guarda los cambios realizados al orden de los grupos de preguntas
     */
    public function save_order($institucion_id, Request $request)
    {
        //Verifica que la institucion corresponda a la del usuario
        if (! Auth::user()->hasRole('sysadmin')) {
            if (Auth::user()->sede->institucion->id != $institucion_id) {
                return redirect('/home')
                    ->withMessages(['type' => 'error', 'text' => 'Institución inválida!!']);
            }
        }

        //Verifica el Grupo de Preguntas
        if (! $this->get_institucion($institucion_id)) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'La institución seleccionada es inválida!!!']);
        }

        //Verifica que los datos recibidos sean ajax
        if (! $request->ajax()) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'Los datos recibidos son inválidos!!!']);
        }

        $data = $request->all();
        $newList = $data['items'];

        try {
            foreach ($newList as $n) {
                GrupoPorInstitucion::where('id', '=', $n['id'])->update(['orden' => $n['orden']]);
            }
        } catch (QueryException $e) {
            Session::flash('messages', ['type' => 'error', 'text' => 'No se pudieron guardar los cambios!!!']);

            return 'ERROR';
        }
        Session::flash('messages', ['type' => 'success', 'text' => 'El nuevo orden de los Grupos de Preguntas ha sido guardado!!!']);

        return 'OK';
    }

    /**
     * Obtiene la institucion actual y setea los paramatros principales para la vista
     */
    private function get_institucion($route_id)
    {
        if ($route_id == null) {
            $route_id = Auth::user()->sede->institucion->id;
        }
        try {
            $this->params['data']['institucion'] = Institucion::findOrFail($route_id);
        } catch (ModelNotFoundException $e) {
            return false;
        }
        $this->params['title'] = $this->title."<br><span class='h5'>".$this->params['data']['institucion']['nombre'].'</span>';
        $this->params['route'] = $this->route_parent.'/'.$route_id.'/'.$this->route_name;

        return true;
    }

    /**
     * Reordena la numeración de los grupos
     */
    private function reordenarGrupos()
    {
        $list = $this->params['data']['institucion']->gruposPorInstitucion()->orderBy('orden')->get();
        foreach ($list as $key => $item) {
            $item['orden'] = $key + 1;
            $item->save();
        }
    }

    public function nestGroups(Request $request)
    {
        if (! RequestUtils::havePermission($this->route_name, 'any')) {
            return redirect('/home');
        }

        // Verifica que la institucion corresponda a la del usuario
        if (! Auth::user()->hasRole('sysadmin')) {
            if (Auth::user()->sede->institucion->id != $request->segment(2)) {
                return redirect('/home')->withMessages(['type' => 'error', 'text' => 'Institución inválida!!']);
            }
        }

        // Verifica el Grupo de Preguntas
        if (! $this->get_institucion($request->segment(2))) {
            return redirect(url('/home'))->withMessages(['type' => 'error', 'text' => 'No se pudo determinar la Institución!!!']);
        }

        $grupos = [];
        $grupoPreguntas = GrupoDePreguntas::where('master', true)->orderBy('orden')->get();
        foreach ($grupoPreguntas as $grupoPregunta) {
            $grupos[$grupoPregunta->id] = [
                'id' => $grupoPregunta->id,
                'nombre' => $grupoPregunta->nombre,
                'master' => $grupoPregunta->master,
            ];
        }
        $gruposInstitucion = $this->params['data']['institucion']->gruposPorInstitucion()->with('grupoDePreguntas')->orderBy('orden')->get();
        foreach ($gruposInstitucion as $grupoInstitucion) {
            $grupos[$grupoInstitucion->grupoDePreguntas->id] = [
                'id' => $grupoInstitucion->grupoDePreguntas->id,
                'nombre' => $grupoInstitucion->grupoDePreguntas->nombre,
                'master' => $grupoInstitucion->grupoDePreguntas->master,
            ];
        }

        $nestable = $this->params['data']['institucion']->grupos_agrupados;
        if (! is_array($this->params['data']['institucion']->grupos_agrupados)) {
            $nestable = [];
            foreach ($grupos as $grupo) {
                $nestable[] = [
                    'id' => $grupo['id'],
                ];
            }
        }

        $this->params['title'] = 'AGRUPAR GRUPOS POR INSTITUCIÓN';
        $this->params['filter'] = $request->input('filter');
        $this->params['data']['items'] = $grupos;
        $this->params['data']['nestable'] = $nestable;

        return view($this->route_name.'.nest')->with('params', $this->params);
    }

    public function saveNestedGroups(Request $request, $institucion_id)
    {
        // Verifica que la institucion corresponda a la del usuario
        if (! Auth::user()->hasRole('sysadmin')) {
            if (Auth::user()->sede->institucion->id != $institucion_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Institución inválida!!',
                ], 200);
            }
        }

        // Verifica la institucion seleccionada
        if (! $this->get_institucion($institucion_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'El grupo de preguntas seleccionado es inválido!!!',
            ], 200);
        }

        $institucion = $this->params['data']['institucion'];

        try {
            $institucion->grupos_agrupados = json_decode($request->input('grupos_agrupados'));
            $institucion->save();
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo guardar los cambios. Intente más tarde.',
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Los cambios se guardaron correctamente.',
        ], 200);
    }
}
