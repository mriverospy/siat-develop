<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestUtils;
use App\Models\GrupoDePreguntas;
use App\Models\Institucion;
use App\Models\Pregunta;
use App\Models\Reporte;
use App\Services\ReporteService;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Settings;

class ReportesController extends Controller
{
    /**
     * Ruta
     *
     * @var string
     */
    private $route_name = 'reportes';

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
            'title' => 'REPORTES',
            'subtitle' => 'LISTADO DE REPORTES',
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

        $usuarioInstitucionId = Auth::user()->sede->institucion_id;

        $this->params['filter'] = $request->input('filter');
        // $this->params['data']['items'] = Auth::user()->reportes()->filteredPaginate($this->params['filter'], $this->params['items_per_page']);
        $this->params['data']['items'] = Reporte::where('usuario_id', Auth::user()->id)
            ->orWhere(function ($reporte) use ($usuarioInstitucionId) {
                $reporte->where('compartido', true);
                $reporte->where(function ($query) use ($usuarioInstitucionId) {
                    $query->whereNull('instituciones');
                    $query->orWhereRaw("JSON_EXTRACT(instituciones, REPLACE(JSON_SEARCH(instituciones, 'one', '{$usuarioInstitucionId}', '', '$[*].id'), '\"', '')) = '{$usuarioInstitucionId}'");
                });
            })->filteredPaginate($this->params['filter'], $this->params['items_per_page']);

        return view($this->route_name.'.index')->with('params', $this->params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(ReporteService $reporteService)
    {
        // Verifica permiso
        if (! RequestUtils::havePermission($this->route_name, 'create')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $this->params['subtitle'] = 'NUEVO REPORTE';

        $this->params['filtros'] = $reporteService->filtros(Auth::user());

        $this->params['comparadores'] = $reporteService->comparadores();

        // Obtener grupos y preguntas masters
        $masters = GrupoDePreguntas::where('master', '=', 1)
                    ->with(['preguntas' => function ($query) {
                        $query->orderBy('orden', 'asc');
                    }])
                    ->get();

        // Obtener grupos y preguntas de la institución del usuario
        $institucionales = Auth::user()->sede->institucion
                            ->grupoDePreguntas()
                            ->with(['preguntas' => function ($query) {
                                $query->orderBy('orden', 'asc');
                            }])
                            ->where('master', '=', 0)
                            ->orderBy('orden')
                            ->get();

        $grupos = $masters->merge($institucionales);

        $campos = [];
        foreach ($grupos as $grupo) {
            foreach ($grupo->preguntas as $pregunta) {
                $campos[] = [
                    'id' => $pregunta->id,
                    'tipo' => $pregunta->tipo,
                    'nombre' => $grupo->nombre.' - '.$pregunta->pregunta,
                ];
            }
        }

        $this->params['campos'] = $campos;

        $this->params['instituciones'] = Institucion::orderBy('nombre')->get()->pluck('nombre', 'id');

        return view('layouts.form_layout')->with('params', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();

            $data['usuario_id'] = Auth::user()->id;

            if (Auth::user()->hasRole('sysadmin')) {
                if (trim($data['compartido']) == 'true') {
                    $data['compartido'] = true;
                    $data['ejecutar_como_usuario'] = Auth::user()->id;
                } else {
                    $data['compartido'] = false;
                    $data['instituciones'] = null;
                    $data['ejecutar_como_usuario'] = null;
                }

                if (is_array($data['instituciones']) and count($data['instituciones']) == 0) {
                    $data['instituciones'] = null;
                }
            } else {
                unset($data['compartido']);
                unset($data['instituciones']);
                unset($data['ejecutar_como_usuario']);
            }

            $item = Reporte::create($data);
        } catch (QueryException $e) {
            if ($request->ajax()) {
                return ['status' => 'error', 'message' => $e->getMessage(), 'payload' => null];
            } else {
                return redirect($this->route_name)
                    ->withMessages(['type' => 'error', 'text' => 'No se pudo agregar el nuevo registro!!!']);
            }
        }

        if ($request->ajax()) {
            return ['status' => 'success', 'message' => null, 'payload' => $item];
        } else {
            return redirect()
                    ->route('reportes.show', [$item->id])
                    ->withMessages(['type' => 'success', 'text' => 'Nuevo Registro creado exitosamente!!!']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Verifica permiso
        if (! RequestUtils::havePermission($this->route_name, 'list')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $reporte = Reporte::findOrFail($id);

        if ($reporte->compartido == false && $reporte->usuario_id != Auth::user()->id) {
            abort(404);
        } elseif ($reporte->compartido == true && $reporte->usuario_id != Auth::user()->id) {
            if (! is_null($reporte->instituciones) && ! in_array(Auth::user()->sede->institucion_id, collect($reporte->instituciones)->pluck('id')->toArray())) {
                abort(404);
            }
        }

        $campos = [];
        $reporteCampos = $reporte->campos;
        if (count($reporteCampos) > 0) {
            foreach ($reporteCampos as $index => $campo) {
                if (count($campo['grupos']) > 0) {
                    foreach ($campo['grupos'] as $gindex => $grupo) {
                        if ($reporteCampos[$index]['grupos'][$gindex]['habilitado'] == 'false') {
                            unset($reporteCampos[$index]['grupos'][$gindex]);
                        } else {
                            unset($campo['grupos']);
                            $campos[] = [
                                'campo' => $campo,
                                'agrupador' => $grupo,
                            ];
                        }
                    }
                }
            }
        }

        $campos = collect($campos);

        $logo_membrete = Settings::logo_membrete();

        return view($this->route_name.'.show', compact('logo_membrete', 'reporte', 'campos'))->with('params', $this->params);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(ReporteService $reporteService, $id)
    {
        // Verifica permiso
        if (! RequestUtils::havePermission($this->route_name, 'edit')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $this->params['subtitle'] = 'EDITAR REPORTE';

        $this->params['filtros'] = $reporteService->filtros(Auth::user());

        $this->params['comparadores'] = $reporteService->comparadores();

        // Obtener grupos y preguntas masters
        $masters = GrupoDePreguntas::where('master', '=', 1)
                    ->with(['preguntas' => function ($query) {
                        $query->orderBy('orden', 'asc');
                    }])
                    ->get();

        // Obtener grupos y preguntas de la institución del usuario
        $institucionales = Auth::user()->sede->institucion
                            ->grupoDePreguntas()
                            ->with(['preguntas' => function ($query) {
                                $query->orderBy('orden', 'asc');
                            }])
                            ->where('master', '=', 0)
                            ->orderBy('orden')
                            ->get();

        $grupos = $masters->merge($institucionales);

        $campos = [];
        foreach ($grupos as $grupo) {
            foreach ($grupo->preguntas as $pregunta) {
                $campos[] = [
                    'id' => $pregunta->id,
                    'tipo' => $pregunta->tipo,
                    'nombre' => $grupo->nombre.' - '.$pregunta->pregunta,
                ];
            }
        }

        $this->params['campos'] = $campos;

        $this->params['instituciones'] = Institucion::orderBy('nombre')->get()->pluck('nombre', 'id');

        $this->params['data']['item'] = Auth::user()->reportes()->findOrFail($id);

        return view('layouts.form_layout')->with('params', $this->params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $row = Auth::user()->reportes()->findOrFail($id);

        try {
            $data = $request->all();

            if (Auth::user()->hasRole('sysadmin')) {
                if (trim($data['compartido']) == 'true') {
                    $data['compartido'] = true;
                    $data['ejecutar_como_usuario'] = Auth::user()->id;
                } else {
                    $data['compartido'] = false;
                    $data['instituciones'] = null;
                    $data['ejecutar_como_usuario'] = null;
                }

                if (! isset($data['instituciones'])) {
                    $data['instituciones'] = null;
                }
            }

            $row->update($data);
        } catch (QueryException $e) {
            if ($request->ajax()) {
                return ['status' => 'error', 'message' => $e->getMessage(), 'payload' => null];
            } else {
                return redirect($this->route_name)
                    ->withMessages(['type' => 'error', 'text' => 'Las modificaciones no fueron guardadas!!!']);
            }
        }

        if ($request->ajax()) {
            return ['status' => 'success', 'message' => null, 'payload' => $row];
        } else {
            return redirect()
                    ->route('reportes.show', [$item->id])
                    ->withMessages(['type' => 'success', 'text' => 'Registro actualizado con éxito!!!']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $row = Auth::user()->reportes()->findOrFail($id);

        try {
            $row->delete();
        } catch (Exception $e) {
            return redirect($this->route_name)
                    ->withMessages(['type' => 'error', 'text' => 'El registro seleccionado no puede ser eliminado!!!'.$e]);
        }

        return redirect($this->route_name)
                    ->withMessages(['type' => 'success', 'text' => 'Registro eliminado exitosamente!!!']);
    }

    public function getReportData($id)
    {
        // Verifica permiso
        if (! RequestUtils::havePermission($this->route_name, 'list')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $reporte = Auth::user()->reportes()->findOrFail($id);

        $campos = [];
        $reporteCampos = $reporte->campos;
        if (count($reporteCampos) > 0) {
            foreach ($reporteCampos as $index => $campo) {
                if (count($campo['grupos']) > 0) {
                    foreach ($campo['grupos'] as $gindex => $grupo) {
                        if ($reporteCampos[$index]['grupos'][$gindex]['habilitado'] == 'false') {
                            unset($reporteCampos[$index]['grupos'][$gindex]);
                        } else {
                            unset($campo['grupos']);
                            $campos[] = [
                                'campo' => $campo,
                                'agrupador' => $grupo,
                            ];
                        }
                    }
                }
            }
        }

        $campos = collect($campos);

        return view($this->route_name.'.data', compact('reporte', 'campos'))->with('params', $this->params);
    }

    public function getGraphSummaryData(Request $request)
    {
        $reporte = Reporte::findOrFail($request->input('reporte_id'));

        if ($reporte->compartido == false && $reporte->usuario_id != Auth::user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró el reporte seleccionado.',
            ], 200);
        }

        if ($reporte->compartido == true) {
            if (! is_null($reporte->instituciones) && ! in_array(Auth::user()->sede->institucion_id, collect($reporte->instituciones)->pluck('id')->toArray())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se encontró el reporte seleccionado.',
                ], 200);
            }
        }

        $fechaInicio = Carbon::parse($request->get('fecha_inicio'));
        $fechaFin = Carbon::parse($request->get('fecha_fin'));

        $reporteQuery = new Reporte();
        $data = $reporteQuery->sumarioReporte($reporte, $fechaInicio, $fechaFin);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'payload' => $data,
            ], 200);
        } else {
            return redirect('reportes');
        }
    }

    public function getGraphData(Request $request)
    {
        $reporte = Reporte::findOrFail($request->input('reporte_id'));

        if ($reporte->compartido == false && $reporte->usuario_id != Auth::user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró el reporte seleccionado.',
            ], 200);
        }

        if ($reporte->compartido == true) {
            if (! is_null($reporte->instituciones) && ! in_array(Auth::user()->sede->institucion_id, collect($reporte->instituciones)->pluck('id')->toArray())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se encontró el reporte seleccionado.',
                ], 200);
            }
        }

        try {
            $pregunta = Pregunta::where('id', $request->input('campo_id'))->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró la pregunta seleccionada.',
            ], 200);
        }

        // agrupador
        $agrupador = [$request->input('agrupador')];

        $fechaInicio = Carbon::parse($request->get('fecha_inicio'));
        $fechaFin = Carbon::parse($request->get('fecha_fin'));

        $reporteQuery = new Reporte();
        $data = $reporteQuery->estadisticaPregunta($pregunta, $fechaInicio, $fechaFin, $reporte, $agrupador);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'payload' => [
                    'keys' => $data->keys(),
                    'values' => $data->values(),
                ],
            ], 200);
        } else {
            return redirect('reportes');
        }
    }
}
