<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreguntaRequest;
use App\Http\Requests\RequestUtils;
use App\Models\GrupoDePreguntas;
use App\Models\Pregunta;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\DB;

class PreguntasController extends Controller
{
    private $route_name = 'preguntas';

    private $route_parent = '/grupos';

    private $title = 'PREGUNTAS';

    private $tipos = ['text' => 'Texto', 'number' => 'Número', 'radio' => 'Selección simple', 'check' => 'Selección múltiple', 'date' => 'Fecha'];

    private $params = [];

    public function __construct()
    {
        $this->params = [
            'title' => $this->title,
            'subtitle' => 'LISTADO DE PREGUNTAS',
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

        // Verifica el Grupo de Preguntas
        if (! $this->get_grupo($request->segment(2))) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'El grupo de preguntas seleccionado es inválido!!!']);
        }

        $this->params['filter'] = $request->input('filter');
        $this->params['data']['items'] = $this->params['data']['grupo']->preguntas()->orderBy('orden')->get();

        return view($this->route_name.'.index')->with('params', $this->params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($grupo_id, $tipo = 'text')
    {
        // Verifica el Grupo de Preguntas
        if (! $this->get_grupo($grupo_id)) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'El grupo de preguntas seleccionado es inválido!!!']);
        }

        // Verifica el Tipo
        $tipoNombre = $this->nombreTipo($tipo);
        if (! $tipoNombre) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'El Tipo de Pregunta es inválido!!!']);
        }

        $this->params['subtitle'] = 'NUEVA PREGUNTA';
        $this->params['tipo'] = $tipo;
        $this->params['tipoNombre'] = strtoupper($tipoNombre);

        return view($this->route_name.'._form')->with('params', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store($grupo_id, PreguntaRequest $request)
    {
        // Verifica el Grupo de Preguntas
        if (! $this->get_grupo($grupo_id)) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'El grupo de preguntas seleccionado es inválido!!!']);
        }

        $new = $request->except('detalles');
        if ($request->has('detalles')) unset($new['opciones']);
        $new['orden'] = $this->params['data']['grupo']->preguntas()->ordenActual();

        try {
            DB::transaction(function () use ($new, $request) {
                $pregunta = $this->params['data']['grupo']->preguntas()->create($new);
                if ($request->has('detalles')) $pregunta->guardarDetalles($request->input('detalles'));
            });
        } catch (QueryException $e) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'No se pudo agregar el nuevo registro!!!']);
        }

        return redirect($this->params['route'])
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
    public function edit($grupo_id, $id)
    {
        // Verifica el Grupo de Preguntas
        if (! $this->get_grupo($grupo_id)) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'El grupo de preguntas seleccionado es inválido!!!']);
        }

        $this->params['subtitle'] = 'EDITAR PREGUNTA';

        try {
            $this->params['data']['item'] = $this->params['data']['grupo']->preguntas()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'No encontró el registro solicitado!!!']);
        }

        //Verifica el Tipo
        $tipoNombre = $this->nombreTipo($this->params['data']['item']['tipo']);
        if (! $tipoNombre) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'El Tipo de Pregunta es inválido!!!']);
        }

        $this->params['tipo'] = $this->params['data']['item']['tipo'];
        $this->params['tipoNombre'] = strtoupper($tipoNombre);

        return view($this->route_name.'._form')->with('params', $this->params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($grupo_id, $id, PreguntaRequest $request)
    {
        // Verifica el Grupo de Preguntas
        if (! $this->get_grupo($grupo_id)) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'El grupo de preguntas seleccionado es inválido!!!']);
        }

        // Busca el registro en base de datos
        try {
            $row = $this->params['data']['grupo']->preguntas()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'No encontró el registro solicitado!!!']);
        }

        // Actualiza
        try {
            $data = $request->except('detalles');
            if ($request->has('detalles')) unset($data['opciones']);

            if (! isset($data['mostrar_selector'])) {
                $data['mostrar_selector'] = false;
            } else {
                $data['mostrar_selector'] = ((int) $data['mostrar_selector'] == 1);
            }

            $data['obligatorio'] = isset($data['obligatorio']) && (int)$data['obligatorio'] == 1;


            DB::transaction(function () use ($row, $data, $request) {
                $row->update($data);
                if ($request->has('detalles')) $row->guardarDetalles($request->input('detalles'));
            });
        } catch (QueryException $e) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'Las modificaciones No fueron guardadas!!!']);
        }

        return redirect($this->params['route'])
                ->withMessages(['type' => 'success', 'text' => 'Registro actualizado con éxito!!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($grupo_id, $id)
    {
        // Verifica el Grupo de Preguntas
        if (! $this->get_grupo($grupo_id)) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'El grupo de preguntas seleccionado es inválido!!!']);
        }

        // Busca el registro en base de datos
        try {
            $row = $this->params['data']['grupo']->preguntas()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'No encontró el registro solicitado!!!']);
        }

        // Elimina el registro
        try {
            $row->delete();
            $this->reordenarPreguntas();
        } catch (QueryException $e) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'El registro seleccionado no puede ser eliminado!!!']);
        }

        return redirect($this->params['route'])
                ->withMessages(['type' => 'success', 'text' => 'Registro eliminado exitosamente!!!']);
    }

    public function get_grupo($route_id)
    {
        try {
            if (Auth::user()->hasRole('sysadmin')) {
                $this->params['data']['grupo'] = GrupoDePreguntas::findOrFail($route_id);
            } else {
                $this->params['data']['grupo'] = Auth::user()->sede->institucion->grupoDePreguntas()->findOrFail($route_id);
            }
        } catch (ModelNotFoundException $e) {
            return false;
        }

        $this->params['title'] = $this->title.'<br><span class="h5">'.$this->params['data']['grupo']['nombre'].'</span>';
        $this->params['route'] = $this->route_parent.'/'.$route_id.'/'.$this->route_name;

        return true;
    }

    /* ####  ORDENAMIENTO  #### */

    /**
     * Guarda los cambios realizados al orden de los grupos de preguntas
     */
    public function save_order($grupo_id, Request $request)
    {
        // Verifica el Grupo de Preguntas
        if (! $this->get_grupo($grupo_id)) {
            return redirect(url($this->route_parent))
                ->withMessages(['type' => 'error', 'text' => 'El grupo de preguntas seleccionado es inválido!!!']);
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
                Pregunta::where('id', '=', $n['id'])->update(['orden' => $n['orden']]);
            }
        } catch (QueryException $e) {
            Session::flash('messages', ['type' => 'error', 'text' => 'No se pudieron guardar los cambios!!!']);

            return 'ERROR';
        }

        Session::flash('messages', ['type' => 'success', 'text' => 'El nuevo orden de las Preguntas ha sido guardado!!!']);

        return 'OK';
    }

    /**
     * Reordena la numeración de las preguntas
     */
    private function reordenarPreguntas()
    {
        $list = $this->params['data']['grupo']->preguntas()->orderBy('orden')->get();
        foreach ($list as $key => $item) {
            $item['orden'] = $key + 1;
            $item->save();
        }
    }

    /**
     * Retorna el nombre para mostrar del tipo redicibo
     * por parametro
     */
    public function nombreTipo($tipo = 'text')
    {
        if ($this->existeTipo($tipo)) {
            return $this->tipos[$tipo];
        }

        return false;
    }

    /**
     * Verifica si existe el tipo recibido por parametro
     * en el array de tipos
     */
    private function existeTipo($tipo)
    {
        return isset($this->tipos[$tipo]);
    }
}
