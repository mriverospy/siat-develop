<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccidenteRequest;
use App\Http\Requests\RequestUtils;
use App\Jobs\ProcesarArchivoDatos;
use App\Models\Accidente;
use App\Models\Autorizacion;
use App\Models\Departamento;
use App\Models\Derivacion;
use App\Models\Distrito;
use App\Models\GrupoDePreguntas;
use App\Models\Institucion;
use App\Models\Localidad;
use App\Models\Notificacion;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Sede;
use App\Models\Usuario;
use App\Services\AccidenteService;
use App\Services\FormularioAccidente;
use App\Settings\GeneralSettings;
use Auth;
use Carbon\Carbon;
use DateTime;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use League\Csv\Writer;
use Mail;
use PDF;
use Settings;
use Storage;
use Validator;

class AccidentesController extends Controller
{
    private $route_name = 'accidentes';

    private $params = [];


    public function __construct()
    {
        $this->params = [
            'title' => 'ACCIDENTES',
            'subtitle' => 'LISTADO DE ACCIDENTES',
            'panel_color' => 'panel-midnightblue',
            'search' => 'YES',
            'filter' => '',
            'items_per_page' => 10,
            'route_name' => $this->route_name,
            'route' => '/' . $this->route_name,
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
        if (!RequestUtils::havePermission($this->route_name, 'any')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $this->params['filter'] = $request->input('filter');

        $accidente = Accidente::with([
            'localidad',
            'localidad.distrito',
            'localidad.distrito.departamento',
            'usuario',
            'sede',
            'institucion',
        ]);

        $estado = 'abierto';
        if (strtolower($request->get('estado')) == 'cerrado') {
            $estado = 'cerrado';
        }
        $accidente->where('estado', $estado);

        $this->params['estado'] = $estado;

        if (Auth::user()->hasRole('sysadmin') || Auth::user()->hasRole('consulta')) {
            $this->params['data']['items'] = $accidente->filteredPaginate($this->params['filter'], $this->params['items_per_page']);
        } elseif (Auth::user()->hasRole('administrador')) {
            $this->params['data']['items'] = $accidente->where('institucion_id', Auth::user()->sede->institucion->id)->filteredPaginate($this->params['filter'], $this->params['items_per_page']);
        } elseif (Auth::user()->hasRole('directivo')) {
            $this->params['data']['items'] = $accidente->where('sede_id', Auth::user()->sede->id)->filteredPaginate($this->params['filter'], $this->params['items_per_page']);
        } else {
            $this->params['data']['items'] = $accidente->where('usuario_id', Auth::user()->id)->filteredPaginate($this->params['filter'], $this->params['items_per_page']);
        }

        return view($this->route_name . '.index')->with('params', $this->params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'create')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $this->params['subtitle'] = 'NUEVO ACCIDENTE';
        $this->params['data']['patern_list'] = Departamento::pluck('nombre', 'id');

        return view('layouts.form_layout')->with('params', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(AccidenteRequest $request)
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'create')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        try {
            Accidente::create($request->all());
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
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'list')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $accidente = new Accidente;
        $accidente = $accidente->with([
            'localidad',
            'localidad.distrito',
            'localidad.distrito.departamento',
            'respuestas' => function ($q) {
                $q->orderBy('id');
            },
            'respuestas.pregunta',
            'respuestas.pregunta.grupoDePreguntas',
            'respuestas.usuario',
        ])->where('id', $id);

        if (!Auth::user()->hasRole('sysadmin')) {
            if (Auth::user()->hasRole('administrador')) {
                $accidente = $accidente->where('institucion_id', Auth::user()->sede->institucion->id);
            } elseif (Auth::user()->hasRole('directivo')) {
                $accidente = $accidente->where('sede_id', Auth::user()->sede->id);
            } else {
                $accidente = $accidente->where('usuario_id', Auth::user()->id);
            }
        }

        try {
            $accidente = $accidente->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return redirect('/')
                ->withMessages(['type' => 'error', 'text' => 'No se encontró el accidente solicitado.']);
        }

        // Obtener respuestas
        $respuestas = [];
        if ($accidente->respuestas->count() > 0) {
            foreach ($accidente->respuestas as $respuesta) {
                $indexKey = implode('_', [$respuesta->pregunta->grupoDePreguntas->id, $respuesta->agrupador]);
                $respuestas[$indexKey]['grupo'] = $respuesta->pregunta->grupoDePreguntas->nombre;
                $respuestas[$indexKey]['respuestas'][$respuesta->pregunta->orden][$respuesta->pregunta_texto][] = $respuesta;
            }
        }

        // Ordenar
        ksort($respuestas);
        foreach ($respuestas as $index => $grupo) {
            ksort($respuestas[$index]['respuestas']);
        }

        $respuestas = collect($respuestas);

        return view($this->route_name . '.show', compact('accidente', 'respuestas'))->with('params', $this->params);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function pdf($id)
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'pdf-report')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $accidente = Accidente::with([
            'localidad',
            'localidad.distrito',
            'localidad.distrito.departamento',
            'respuestas',
            'respuestas.pregunta',
            'respuestas.pregunta.grupoDePreguntas',
            'respuestas.usuario',
        ])->find($id);

        // Obtener respuestas
        $respuestas = [];
        if ($accidente->respuestas->count() > 0) {
            foreach ($accidente->respuestas as $respuesta) {
                $indexKey = implode('_', [$respuesta->pregunta->grupoDePreguntas->id, $respuesta->agrupador]);
                $respuestas[$indexKey]['grupo'] = $respuesta->pregunta->grupoDePreguntas->nombre;
                $respuestas[$indexKey]['respuestas'][$respuesta->pregunta->orden][$respuesta->pregunta_texto][] = $respuesta;
            }
        }

        // Ordenar
        ksort($respuestas);
        foreach ($respuestas as $index => $grupo) {
            ksort($respuestas[$index]);
        }

        $respuestas = collect($respuestas);

        $pdfData = [
            'logo_membrete' => Settings::logo_membrete(),
            'user' => Auth::user(),
            'nombres' => Auth::user()->nombreCompleto(),
            'institucion' => Auth::user()->sede->institucion->nombre,
        ];

        $pdf = PDF::loadView($this->route_name . '.pdf', compact('accidente', 'respuestas'));
        $pdf->setPaper('Legal');
        $pdf->setOrientation('portrait');
        $pdf->setOption('header-html', \View::make('pdf.header', compact('pdfData')));
        $pdf->setOption('footer-html', \View::make('pdf.footer', compact('pdfData')));
        $pdf->setOption('enable-local-file-access', true);
        $pdf->setOption('header-spacing', 2);

        return $pdf->stream();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        // Verificar si hay autorizaciones para este accidente y usuario
        $autorizacion = Autorizacion::where('accidente_id', $id)
            ->where('recibe_usuario_id', Auth::user()->id)
            ->where('fecha_inicio_autorizacion', '<=', date('Y-m-d'))
            ->where('fecha_fin_autorizacion', '>=', date('Y-m-d'))
            ->get();

        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'edit') and is_null($autorizacion)) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $accidente = new Accidente;
        $accidente = $accidente->with([
            'localidad',
            'localidad.distrito',
            'localidad.distrito.departamento',
        ])->where('id', $id);

        // Si no es syadmin y no tiene autorizacion disponible
        if (!Auth::user()->hasRole('sysadmin') and is_null($autorizacion)) {
            if (Auth::user()->hasRole('administrador')) {
                $accidente = $accidente->where('institucion_id', Auth::user()->sede->institucion->id);
            } elseif (Auth::user()->hasRole('directivo')) {
                $accidente = $accidente->where('sede_id', Auth::user()->sede->id);
            } else {
                $accidente = $accidente->where('usuario_id', Auth::user()->id);
            }
        }

        try {
            $accidente = $accidente->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return redirect('/')
                ->withMessages(['type' => 'error', 'text' => 'No se encontró el accidente solicitado.']);
        }

        $displayFields = [
            'caratula',
            'expediente_judicial',
            'sumario_policial',
            'otro',
            'dependencia',
            'fecha_accidente',
            'lugar',
            'altura',
            'tipo_camino',
            'localidad_id',
            'codigo_postal',
            'ubicacion',
            'heridos',
            'fallecidos',
            'ilesos',
            'participantes_siniestro',
        ];

        if (!is_null($autorizacion) and count($autorizacion) > 0) {
            $displayFields = [];
            foreach ($autorizacion as $autoriza) {
                if (count($autoriza->campos_autorizados) > 0) {
                    foreach ($autoriza->campos_autorizados as $campo) {
                        if (in_array($campo, $displayFields)) {
                            continue;
                        }

                        if (stristr($campo, 'pregunta_') === false) {
                            $displayFields[] = $campo;
                        }
                    }
                }
            }
        }

        $this->params['subtitle'] = 'EDITAR ACCIDENTE';
        $this->params['data']['patern_list'] = Departamento::pluck('nombre', 'id');
        $this->params['data']['item'] = $accidente;
        $this->params['displayFields'] = $displayFields;
        $this->params['localidades'] = Localidad::with(['distrito', 'distrito.departamento'])->get();
        $this->params['grupos'] = GrupoDePreguntas::with(['preguntas' => function ($query) {
            $query->orderBy('orden', 'asc');
        }])->get();

        return view('layouts.form_layout')->with('params', $this->params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @return Response
     */
    public function update($id, AccidenteRequest $request)
    {
        // Verificar si hay autorizaciones para este accidente y usuario
        $autorizacion = Autorizacion::where('accidente_id', $id)
            ->where('recibe_usuario_id', Auth::user()->id)
            ->where('fecha_inicio_autorizacion', '<=', date('Y-m-d'))
            ->where('fecha_fin_autorizacion', '>=', date('Y-m-d'))
            ->get();

        $accidente = new Accidente;
        $accidente = $accidente->where('id', $id);

        // Si no es syadmin y no tiene autorizacion disponible
        if (!Auth::user()->hasRole('sysadmin') and is_null($autorizacion)) {
            if (Auth::user()->hasRole('administrador')) {
                $accidente = $accidente->where('institucion_id', Auth::user()->sede->institucion->id);
            } elseif (Auth::user()->hasRole('directivo')) {
                $accidente = $accidente->where('sede_id', Auth::user()->sede->id);
            } else {
                $accidente = $accidente->where('usuario_id', Auth::user()->id);
            }
        }

        try {
            $accidente = $accidente->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return redirect('/')
                ->withMessages(['type' => 'error', 'text' => 'No se encontró el accidente solicitado.']);
        }

        $allFields = [
            'caratula',
            'expediente_judicial',
            'sumario_policial',
            'otro',
            'dependencia',
            'fecha_accidente',
            'lugar',
            'altura',
            'tipo_camino',
            'codigo_postal',
            'gps_latitud',
            'gps_longitud',
            'heridos',
            'fallecidos',
            'ilesos',
            'participantes_siniestro',
        ];

        if (!is_null($autorizacion) and count($autorizacion) > 0) {
            $allFields = [];
            foreach ($autorizacion as $autoriza) {
                if (count($autoriza->campos_autorizados) > 0) {
                    foreach ($autoriza->campos_autorizados as $campo) {
                        if (stristr($campo, 'pregunta_') === false) {
                            if ($campo == 'ubicacion') {
                                $allFields[] = 'gps_latitud';
                                $allFields[] = 'gps_longitud';
                            } else {
                                $allFields[] = $campo;
                            }
                        }
                    }
                }
            }
        }

        // Datos recibidos
        $data = $request->all();
        foreach ($data as $key => $value) {
            if (!in_array($key, $allFields)) {
                unset($data[$key]);
            }
        }

        try {
            $accidente->update($data);
        } catch (QueryException $e) {
            return redirect('/')->withMessages(['type' => 'error', 'text' => 'Las modificaciones no fueron guardadas!']);
        }

        if (!is_null($autorizacion)) {
            return redirect('autorizaciones/received')->withMessages(['type' => 'success', 'text' => 'Accidente actualizado con éxito!']);
        } else {
            return redirect('accidentes/' . $accidente->id)->withMessages(['type' => 'success', 'text' => 'Accidente actualizado con éxito!']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'delete')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $row = Accidente::findOrFail($id);

        try {
            $row->delete();
        } catch (QueryException $e) {
            return redirect($this->route_name)
                ->withMessages(['type' => 'error', 'text' => 'El registro seleccionado no puede ser eliminado!!!']);
        }

        return redirect($this->route_name)
            ->withMessages(['type' => 'success', 'text' => 'Registro eliminado exitosamente!!!']);
    }

    public function send($id)
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'send')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $this->params['data']['item'] = $accidente = Accidente::findOrFail($id);

        // Chequear si ya existen derivaciones pendientes
        if ($accidente->derivaciones()->where('estado', 'pendiente')->count() > 0) {
            return redirect()
                ->route('derivaciones.sent')
                ->withMessages(['type' => 'error', 'text' => 'El accidente ya tiene una derivación pendiente.']);
        }

        $this->params['subtitle'] = 'DERIVAR ACCIDENTE';
        $this->params['instituciones'] = Institucion::orderBy('nombre')->get();

        return view($this->route_name . '.send')->with('params', $this->params);
    }

    public function doSend($id, AccidenteRequest $request)
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'send')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        // Busca el registro en base de datos
        try {
            $accidente = Accidente::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'No se encontró el accidente asociado.']);
        }

        // Chequear si ya existen derivaciones pendientes
        if ($accidente->derivaciones()->where('estado', 'pendiente')->count() > 0) {
            return redirect()
                ->route('derivaciones.sent')
                ->withMessages(['type' => 'error', 'text' => 'El accidente ya tiene una derivación pendiente.']);
        }

        try {
            $input = $request->all();
            $data['institucion_id'] = Auth::user()->sede->institucion->id;
            $data['sede_id'] = Auth::user()->sede->id;
            $data['usuario_id'] = Auth::user()->id;
            $data['deriva_a_institucion_id'] = $input['institucion_id'];
            $data['deriva_a_sede_id'] = null;
            $data['deriva_a_usuario_id'] = null;
            $data['fecha_derivacion'] = date('Y-m-d H:i:s');
            $data['estado'] = 'pendiente';

            $accidente->derivaciones()->create($data);
        } catch (QueryException $e) {
            return redirect($this->route_name)
                ->withMessages(['type' => 'error', 'text' => 'No se pudo agregar derivar el accidente seleccionado.']);
        }

        return redirect()
            ->route('derivaciones.sent')
            ->withMessages(['type' => 'success', 'text' => 'El accidente seleccionado se ha derivado exitosamente.']);
    }

    public function authorize($id)
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'send-authorization')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'Disculpe, no cuenta con el permiso de emitir autorizaciones.']);
        }

        $this->params['data']['item'] = $accidente = Accidente::findOrFail($id);

        $availableFields = [
            'caratula' => 'Carátula',
            'expediente_judicial' => 'Expediente Judicial',
            'sumario_policial' => 'Sumario Policial',
            'otro' => 'Otro',
            'dependencia' => 'Dependencia',
            'fecha_accidente' => 'Fecha del Accidente',
            'lugar' => 'Lugar',
            'altura' => 'Altura',
            'tipo_camino' => 'Tipo de Camino',
            'localidad_id' => 'Localidad',
            'codigo_postal' => 'Código Postal',
            'ubicacion' => 'Ubicación GPS',
            'heridos' => 'Cantidad de Heridos',
            'fallecidos' => 'Cantidad de Fallecidos',
            'ilesos' => 'Cantidad de Ilesos',
        ];

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

        foreach ($grupos as $grupo) {
            foreach ($grupo->preguntas as $pregunta) {
                $availableFields['pregunta_' . $pregunta->id] = $grupo->nombre . ' - ' . $pregunta->pregunta;
            }
        }

        $this->params['subtitle'] = 'AUTORIZAR CAMBIOS';
        $this->params['sedes'] = Sede::orderBy('nombre')->get();
        $this->params['instituciones'] = Institucion::orderBy('nombre')->get();
        $this->params['usuarios'] = Usuario::where('activo', 1)->get();
        $this->params['available_fields'] = $availableFields;

        return view($this->route_name . '.authorize')->with('params', $this->params);
    }

    public function doAuthorize($id, Request $request)
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'send-authorization')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'Disculpe, no cuenta con el permiso de recibir autorizaciones.']);
        }

        // Busca el registro en base de datos
        try {
            $accidente = Accidente::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect($this->params['route'])
                ->withMessages(['type' => 'error', 'text' => 'No encontró el accidente asociado.']);
        }

        try {
            $data['otorga_usuario_id'] = Auth::user()->id;
            $data['recibe_usuario_id'] = $request->get('usuario_id');
            $data['descripcion'] = $request->get('descripcion');
            $data['campos_autorizados'] = $request->get('campos');
            $data['fecha_autorizacion'] = date('Y-m-d H:i:s');
            $data['fecha_inicio_autorizacion'] = $request->get('fecha_inicio');
            $data['fecha_fin_autorizacion'] = $request->get('fecha_fin');
            $accidente->autorizaciones()->create($data);
        } catch (QueryException $e) {
            return redirect($this->route_name)
                ->withMessages(['type' => 'error', 'text' => 'No se pudo autorizar el accidente seleccionado.']);
        }

        // Notificar al usuario
        Notificacion::create([
            'usuario_id' => $data['recibe_usuario_id'],
            'fecha' => date('Y-m-d H:i:s'),
            'asunto' => 'Autorización de Cambios',
            'mensaje' => Auth::user()->nombres . ' ' . Auth::user()->apellidos . ' te ha autorizado a realizar cambios en el accidente caratulado ' . $accidente->caratula . ".\nÉsta autorización es válida del " . $data['fecha_inicio_autorizacion'] . ' al ' . $data['fecha_fin_autorizacion'] . '.',
            'leido' => false,
        ]);

        $userRecipient = Usuario::whereId($data['recibe_usuario_id'])->get()->first();
        if ($userRecipient and trim($userRecipient->email) != '') {
            Mail::queue(
                'emails.do-authorize',
                [
                    'autorizacion' => $data,
                    'accidente' => $accidente,
                    'remitente' => Auth::user(),
                    'destinatario' => $userRecipient,
                ],
                function ($message) use ($userRecipient) {
                    $message->from('no_responder@siat.gov.py', 'SIAT - No Responder');
                    $message->to($userRecipient->email, $userRecipient->nombres . ' ' . $userRecipient->apellidos);
                    $message->replyTo($userRecipient->email);
                    $message->subject('SIAT - Autorización de Cambios');
                }
            );
        }

        return redirect()
            ->route('autorizaciones.sent')
            ->withMessages(['type' => 'success', 'text' => 'El accidente seleccionado se ha autorizado exitosamente.']);
    }

    /**
     * Cargar accidente
     *
     * @return
     */
    public function cargar()
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'create')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        // Obtener institución actual
        $institucion = Auth::user()->sede->institucion;

        // Obtener departamentos, distritos y localidades
        $departamentos = Departamento::select(['id', 'nombre'])->orderBy('orden')->get();

        // Obtener grupos y preguntas masters
        $masters = GrupoDePreguntas::where('master', '=', 1)
            ->with(['preguntas' => function ($query) {
                $query->orderBy('orden', 'asc');
            }])
            ->get();

        // Obtener grupos y preguntas de la institución del usuario
        $institucionales = $institucion->grupoDePreguntas()
            ->with(['preguntas' => function ($query) {
                $query->orderBy('orden', 'asc');
            }])
            ->where('master', '=', 0)
            ->orderBy('orden')
            ->get();

        // Fusionar grupos master con grupos de la institución
        $grupoPreguntas = $masters->merge($institucionales)->where('visible', true);

        // Traer grupos anidados (si hay), sino, crear uno al vuelo
        $gruposAgrupados = [];
        if (is_array($institucion->grupos_agrupados)) {
            $gruposAgrupados = $institucion->grupos_agrupados;
        } else {
            foreach ($grupoPreguntas as $grupo) {
                $gruposAgrupados[] = [
                    'id' => $grupo->id,
                ];
            }
        }

        /**
         * Preparar secciones de la interfaz de carga
         */
        $index = 0;
        $secciones = [];

        // Preparar los grupos de preguntas con sus preguntas
        $grupos = [];
        $agrupador = 0;
        foreach ($grupoPreguntas as $grupo) {
            // Preparamos las preguntas que van dentro de cada grupo de pregunta
            $preguntas = [];
            foreach ($grupo->preguntas as $pregunta) {
                $preguntas[] = [
                    // 'index' => $index,
                    'grupo_id' => $grupo->id,
                    'grupo_nombre' => $grupo->nombre,
                    'grupo_descripcion' => $grupo->descripcion,
                    'grupo_master' => ((int)$grupo->master == 1),
                    'grupo_duplicable' => ((int)$grupo->puede_duplicar == 1),
                    'grupo_orden' => $grupo->orden,
                    'pregunta_id' => $pregunta->id,
                    'pregunta' => $pregunta->pregunta,
                    'pregunta_orden' => (int)$pregunta->orden,
                    'tipo' => $pregunta->tipo,
                    'opciones' => explode('|', $pregunta->opciones),
                    'instrucciones' => $pregunta->instrucciones,
                    'mostrar_otro' => ((int)$pregunta->mostrar_otro == 1),
                    'titulo_otro' => $pregunta->titulo_otro,
                    'mostrar_selector' => ((int)$pregunta->mostrar_selector == 1),
                    'obligatorio' => ((int)$pregunta->obligatorio == 1),

                ];
            }

            // Asignamos las preguntos y el grupo
            $grupos[$grupo->id] = [
                'id' => $grupo->id,
                'agrupador' => (((int)$grupo->puede_duplicar == 1) ? $agrupador++ : 0),
                'grupo_nombre' => $grupo->nombre,
                'tipo' => 'grupo',
                'duplicable' => ((int)$grupo->puede_duplicar == 1),
                'preguntas' => $preguntas,
            ];
        }

        // Sección Bloque 24 horas
        $secciones[] = [
            'index' => $index,
            'seccion_nombre' => 'Bloque 24 Horas',
            'tipo' => 'agrupado',
            'grupos' => [
                [
                    'id' => null,
                    'agrupador' => 0,
                    'grupo_nombre' => 'Bloque 24 Horas',
                    'tipo' => 'cabecera',
                    'preguntas' => [],
                ],
            ],
        ];

        // Secciones anidadas
        foreach ($gruposAgrupados as $agrupado) {
            // Preparar subgrupos
            $subGrupos = [];

            // Agregar grupo "root"
            if (isset($agrupado['id']) && isset($grupos[$agrupado['id']])) {
                $subGrupos[] = $grupos[$agrupado['id']];
            }

            // Agregar grupos anidados
            if (isset($agrupado['children']) and count($agrupado['children']) > 0) {
                foreach ($agrupado['children'] as $subAgrupado) {
                    if (isset($subAgrupado['id']) and isset($grupos[$subAgrupado['id']])) {
                        $subGrupos[] = $grupos[$subAgrupado['id']];
                    }
                }
            }

            // Los grupos ocultos pueden seguir referenciados en el agrupamiento.
            if (count($subGrupos) === 0) {
                continue;
            }

            // Agregamos los grupos (y sus preguntas) a la sección
            $index = $index + 1;

            $seccionNombre = $subGrupos[0]['grupo_nombre'];

            if (count($subGrupos) == 2) {
                $seccionNombre .= ' y ' . $subGrupos[1]['grupo_nombre'];
            }

            if (count($subGrupos) > 2) {
                $seccionNombre .= ' y otros ' . count($subGrupos) . ' grupo(s)';
            }

            $secciones[] = [
                'index' => $index,
                'seccion_nombre' => $seccionNombre,
                'tipo' => 'agrupado',
                'grupos' => $subGrupos,
            ];
        }

        $secciones = collect($secciones);
        $departamentos = collect($departamentos);
        $agrupados = collect($gruposAgrupados);

        return view($this->route_name . '.cargar', compact('agrupados', 'secciones', 'departamentos'));
    }

    /**
     * Cargar Respuestas AJAX handler - guarda respuestas a BD
     */
    public function cargarRespuestas(AccidenteService $accidenteService, Request $request)
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'create')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $usuarioId = Auth::user()->id;

        $formulario = new FormularioAccidente;
        $validator = $formulario->cabecera($request->all());
        $validator->after(function ($validator) use ($request) {
            if ($validator->errors()->isNotEmpty()) return;
            if (!in_array($request->input('ubicacion_confirmada'), ['1', 1, true], true)) {
                $validator->errors()->add('gps_latitud', 'Confirme la ubicación del siniestro en el mapa.');
                return;
            }
            $error = app(\App\Services\GeografiaAccidente::class)->validar($request->all());
            if ($error) $validator->errors()->add('gps_latitud', $error);
        });


        if ($validator->fails()) {
            $errores = [];
            $validationMessages = $validator->messages()->toArray();
            foreach ($validationMessages as $field => $messages) {
                foreach ($messages as $message) {
                    $errores[] = [
                        'pregunta_id' => null,
                        'grupo_pregunta_id' => null,
                        'mensaje' => $message,
                    ];
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Se encontraron errores.',
                'payload' => $errores,
            ], 200);
        }

        $respuestas = $request->get('respuestas', []);
        if (!is_array($respuestas)) $respuestas = [];
        $institucion = Auth::user()->sede->institucion;
        $grupoIds = GrupoDePreguntas::where('master', 1)->pluck('id')
            ->merge($institucion->grupoDePreguntas()->pluck('grupos_de_preguntas.id'));
        $preguntasCollection = Pregunta::whereIn('grupo_de_preguntas_id', $grupoIds)
            ->whereHas('grupoDePreguntas', fn ($query) => $query->where('visible', true))
            ->get();
        $errores = $formulario->respuestas($respuestas, $preguntasCollection);
        if ($errores) {
            return response()->json(['status' => 'error', 'message' => 'Se encontraron errores.', 'payload' => $errores]);
        }
        $preguntas = $preguntasCollection->keyBy('id')->toArray();

        $accidente = DB::transaction(function () use ($request, $accidenteService, $usuarioId, $respuestas, $preguntas) {
            // Guardar Accidente
            $accidenteService->setUser(Auth::user());
            $fechaApertura = date('Y-m-d H:i:s');
            $accidente = $request->only(['caratula', 'expediente_judicial', 'sumario_policial', 'otro', 'dependencia', 'fecha_accidente', 'lugar', 'altura', 'tipo_camino', 'area', 'localidad_id', 'codigo_postal', 'gps_latitud', 'gps_longitud']);
            $accidente['fecha_apertura'] = $fechaApertura;
            $accidente['fecha_cierre'] = $accidenteService->calcularFechaCierre($fechaApertura);
            $accidente['estado_id'] = 1;
            $accidente['estado'] = 'abierto';
            $accidente['usuario_id'] = $usuarioId;
            $accidente['sede_id'] = Auth::user()->sede->id;
            $accidente['institucion_id'] = Auth::user()->sede->institucion->id;

            $accidente = Accidente::create($accidente);

            // Procesar respuestas
            if (is_array($respuestas) and count($respuestas) > 0) {

                // Guardar respuestas
                foreach ($respuestas as $index => $_respuestas) {
                    foreach ($_respuestas as $preguntaId => $respuesta) {
                        $res = [
                            'pregunta_id' => $preguntaId,
                            'usuario_id' => $usuarioId,
                            'agrupador' => $index,
                            'pregunta_texto' => $preguntas[$preguntaId]['pregunta'],
                            'pregunta_tipo' => $preguntas[$preguntaId]['tipo'],
                        ];
                        switch ($preguntas[$preguntaId]['tipo']) {
                            case 'text':
                            case 'radio':
                                if (is_array($respuesta)) {
                                    $respuestaValor = trim(current($respuesta));
                                } else {
                                    $respuestaValor = trim($respuesta);
                                }

                                if ($respuestaValor != '') {
                                    $res['respuesta_texto'] = $respuestaValor;
                                    $accidente->respuestas()->create($res);
                                }
                                break;

                            case 'number':
                                if (is_array($respuesta)) {
                                    $respuestaValor = trim(current($respuesta));
                                } else {
                                    $respuestaValor = trim($respuesta);
                                }

                                if ($respuestaValor != '') {
                                    $res['respuesta_numero'] = $respuestaValor;
                                    $accidente->respuestas()->create($res);
                                }
                                break;

                            case 'check':
                                if (is_array($respuesta)) {
                                    foreach ($respuesta as $r) {
                                        $res['respuesta_texto'] = trim($r);
                                        if ($res['respuesta_texto'] != '') {
                                            $accidente->respuestas()->create($res);
                                        }
                                    }
                                } else {
                                    $res['respuesta_texto'] = trim($respuesta);
                                    if ($res['respuesta_texto'] != '') {
                                        $accidente->respuestas()->create($res);
                                    }
                                }
                                break;

                            case 'date':
                                if (is_array($respuesta)) {
                                    $respuestaValor = current($respuesta);
                                } else {
                                    $respuestaValor = trim($respuesta);
                                }

                                if ($respuestaValor != '') {
                                    $res['respuesta_fecha'] = $respuestaValor;
                                    $accidente->respuestas()->create($res);
                                }
                                break;

                            default:
                                // no hacer nada, ignorar dato
                                break;
                        }

                        // procesar estado
                        if ((int)$preguntaId === 134) {
                            switch ($res['respuesta_texto']) {
                                case 'Fallecido (murió de inmediato o en las 24 horas siguientes)':
                                    $accidente->increment('participantes_siniestro', 1);
                                    $accidente->increment('fallecidos', 1);
                                    break;

                                case 'Lesionado hospitalizado (al menos 24 horas)':
                                case 'Lesionado no hospitalizado (menos de 24 horas)':
                                    $accidente->increment('participantes_siniestro', 1);
                                    $accidente->increment('heridos', 1);
                                    break;

                                case 'Ileso (no sufrió ningún traumatismo)':
                                    $accidente->increment('participantes_siniestro', 1);
                                    $accidente->increment('ilesos', 1);
                                    break;

                                default:
                                    $accidente->increment('participantes_siniestro', 1);
                                    // code...
                                    break;
                            }
                        }
                    }
                }
            }

            return $accidente;
        });

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
            ], 200);
        } else {
            return redirect('accidentes/' . $accidente->id);
        }
    }

    /**
     * Editar Respuesta
     */
    public function editarRespuesta($accidenteId, $preguntaId)
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'edit')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        // Verificar si hay autorizaciones para este accidente y usuario
        $autorizacion = Autorizacion::where('accidente_id', $accidenteId)
            ->where('recibe_usuario_id', Auth::user()->id)
            ->where('fecha_inicio_autorizacion', '<=', date('Y-m-d'))
            ->where('fecha_fin_autorizacion', '>=', date('Y-m-d'))
            ->first();

        $accidente = new Accidente;
        $accidente = $accidente->with([
            'localidad',
            'localidad.distrito',
            'localidad.distrito.departamento',
            'respuestas' => function ($q) {
                $q->orderBy('id');
            },
            'respuestas.pregunta',
            'respuestas.pregunta.grupoDePreguntas',
            'respuestas.usuario',
        ])->where('id', $accidenteId);

        // Si no es syadmin y no tiene autorizacion disponible
        if (!Auth::user()->hasRole('sysadmin') and is_null($autorizacion)) {
            if (Auth::user()->hasRole('administrador')) {
                $accidente = $accidente->where('institucion_id', Auth::user()->sede->institucion->id);
            } elseif (Auth::user()->hasRole('directivo')) {
                $accidente = $accidente->where('sede_id', Auth::user()->sede->id);
            } else {
                $accidente = $accidente->where('usuario_id', Auth::user()->id);
            }
        }

        try {
            $accidente = $accidente->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return redirect('/')
                ->withMessages(['type' => 'error', 'text' => 'No se encontró el accidente solicitado.']);
        }

        // Traer ids de preguntas en autorizacion
        $preguntaIds = [];
        if (!is_null($autorizacion)) {
            foreach ($autorizacion->campos_autorizados as $campo) {
                if (stristr($campo, 'pregunta_') !== false) {
                    $preguntaIds[] = (int)str_replace('pregunta_', '', $campo);
                }
            }
        }

        // Si hay autorizacion y preguntas autorizadas, chequear pregunta id
        if (count($preguntaIds) > 0 and !in_array($preguntaId, $preguntaIds)) {
            return redirect('/')
                ->withMessages(['type' => 'error', 'text' => 'Disculpe, no cuenta con los permisos necesarios para poder modificar los datos de esta pregunta.']);
        }

        $pregunta = Pregunta::whereHas('grupoDePreguntas', fn ($query) => $query->where('visible', true))
            ->findOrFail($preguntaId);
        $respuestas = Respuesta::where('accidente_id', $accidenteId)->where('pregunta_id', $preguntaId)->get();

        $this->params['title'] = 'ACCIDENTE &mdash; ' . $accidente->caratula;
        $this->params['subtitle'] = 'Editar Respuesta - ' . $accidente->caratula;

        return view($this->route_name . '.partials.' . $pregunta->tipo . '-edit', compact('accidente', 'pregunta', 'respuestas'))->with('params', $this->params);
    }

    /**
     * Guardar Respuesta
     */
    public function guardarRespuesta($accidenteId, $preguntaId, Request $request)
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'edit')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        // Verificar si hay autorizaciones para este accidente y usuario
        $autorizacion = Autorizacion::where('accidente_id', $accidenteId)
            ->where('recibe_usuario_id', Auth::user()->id)
            ->where('fecha_inicio_autorizacion', '<=', date('Y-m-d'))
            ->where('fecha_fin_autorizacion', '>=', date('Y-m-d'))
            ->first();

        $accidente = new Accidente;
        $accidente = $accidente->with([
            'localidad',
            'localidad.distrito',
            'localidad.distrito.departamento',
            'respuestas' => function ($q) {
                $q->orderBy('id');
            },
            'respuestas.pregunta',
            'respuestas.pregunta.grupoDePreguntas',
            'respuestas.usuario',
        ])->where('id', $accidenteId);

        // Si no es syadmin y no tiene autorizacion disponible
        if (!Auth::user()->hasRole('sysadmin') and is_null($autorizacion)) {
            if (Auth::user()->hasRole('administrador')) {
                $accidente = $accidente->where('institucion_id', Auth::user()->sede->institucion->id);
            } elseif (Auth::user()->hasRole('directivo')) {
                $accidente = $accidente->where('sede_id', Auth::user()->sede->id);
            } else {
                $accidente = $accidente->where('usuario_id', Auth::user()->id);
            }
        }

        try {
            $accidente = $accidente->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return redirect('/')
                ->withMessages(['type' => 'error', 'text' => 'No se encontró el accidente solicitado.']);
        }

        // Traer ids de preguntas en autorizacion
        $preguntaIds = [];
        if (!is_null($autorizacion)) {
            foreach ($autorizacion->campos_autorizados as $campo) {
                if (stristr($campo, 'pregunta_') !== false) {
                    $preguntaIds[] = (int)str_replace('pregunta_', '', $campo);
                }
            }
        }

        // Si hay autorizacion y preguntas autorizadas, chequear pregunta id
        if (count($preguntaIds) > 0 and !in_array($preguntaId, $preguntaIds)) {
            return redirect('/')
                ->withMessages(['type' => 'error', 'text' => 'Disculpe, no cuenta con los permisos necesarios para poder modificar los datos de esta pregunta.']);
        }

        $usuarioId = Auth::user()->id;
        $pregunta = Pregunta::whereHas('grupoDePreguntas', fn ($query) => $query->where('visible', true))
            ->findOrFail($preguntaId);

        // Validar respuestas
        $respuestas = $request->get('respuestas');
        $errores = [];

        if (is_array($respuestas) and count($respuestas) > 0) {
            // Validar respuestas
            foreach ($respuestas as $respuesta) {
                $opciones = [];

                // Validar preguntas tipo radio y check
                if (in_array($pregunta->tipo, ['radio', 'check'])) {
                    // Sólo validamos si no puede ingresar un valor distinto en "otro"
                    // si puede, se deja abierto
                    if ((int)$pregunta->mostrar_otro != 1) {
                        $opciones = explode('|', $pregunta->opciones);

                        if (is_array($respuesta)) {
                            $respuestaValor = trim(current($respuesta));
                        } else {
                            $respuestaValor = trim($respuesta);
                        }

                        if ($respuestaValor != '') {
                            if (!in_array($respuestaValor, $opciones)) {
                                $errores[] = [
                                    'pregunta_id' => $pregunta->id,
                                    'grupo_pregunta_id' => $pregunta->grupo_de_preguntas_id,
                                    'mensaje' => $pregunta->pregunta . ' sólo puede contener uno de los siguientes valores: ' . implode(',', $opciones) . '.',
                                ];
                            }
                        }
                    }
                }

                // Validar preguntas tipo número
                if (in_array($pregunta->tipo, ['number'])) {
                    if (is_array($respuesta)) {
                        $respuestaValor = trim(current($respuesta));
                    } else {
                        $respuestaValor = trim($respuesta);
                    }

                    if ($respuestaValor != '') {
                        if (!is_numeric($respuestaValor)) {
                            $errores[] = [
                                'pregunta_id' => $pregunta->id,
                                'grupo_pregunta_id' => $pregunta->grupo_de_preguntas_id,
                                'mensaje' => $pregunta->pregunta . ' sólo puede contener un valor numérico.',
                            ];
                        }
                    }
                }

                // Validar preguntas tipo fecha
                if (in_array($pregunta->tipo, ['date'])) {
                    if (is_array($respuesta)) {
                        $respuestaValor = current($respuesta);
                    } else {
                        $respuestaValor = trim($respuesta);
                    }

                    if ($respuestaValor != '') {
                        $formattedDate = DateTime::createFromFormat('Y-m-d', $respuestaValor);
                        $valid = DateTime::getLastErrors();
                        if ($valid['warning_count'] != 0 or $valid['error_count'] != 0) {
                            $errores[] = [
                                'pregunta_id' => $pregunta->id,
                                'grupo_pregunta_id' => $pregunta->grupo_de_preguntas_id,
                                'mensaje' => $pregunta->pregunta . ' sólo puede contener una fecha válida.',
                            ];
                        }
                    }
                }
            }
        }

        // Si hubo errores de validación
        if (count($errores) > 0) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Se encontraron errores.',
                    'payload' => $errores,
                ], 200);
            } else {
                return redirect('accidentes/' . $accidenteId . '/pregunta/' . $preguntaId . '/edit')
                    ->withMessages([
                        'type' => 'error',
                        'text' => implode('<br>', collect($errores)->pluck('mensaje')->toArray()),
                    ]);
            }
        }

        $respuestasModel = Respuesta::where('accidente_id', $accidenteId)->where('pregunta_id', $preguntaId)->get();
        $agrupador = '';
        foreach ($respuestasModel as $_respuesta) {
            $agrupador = $_respuesta->agrupador;
            $_respuesta->delete();
        }

        // Procesar respuestas
        if (is_array($respuestas) and count($respuestas) > 0) {
            // Guardar respuestas
            foreach ($respuestas as $respuesta) {
                $res = [
                    'pregunta_id' => $pregunta->id,
                    'usuario_id' => $usuarioId,
                    'agrupador' => $agrupador,
                    'pregunta_texto' => $pregunta->pregunta,
                    'pregunta_tipo' => $pregunta->tipo,
                ];
                switch ($pregunta->tipo) {
                    case 'text':
                    case 'radio':
                        if (is_array($respuesta)) {
                            $respuestaValor = trim(current($respuesta));
                            $accidente->respuestas()->create($res);
                        } else {
                            $respuestaValor = trim($respuesta);
                        }

                        if ($respuestaValor != '') {
                            $res['respuesta_texto'] = $respuestaValor;
                            $accidente->respuestas()->create($res);
                        }
                        break;

                    case 'number':
                        if (is_array($respuesta)) {
                            $respuestaValor = trim(current($respuesta));
                            $accidente->respuestas()->create($res);
                        } else {
                            $respuestaValor = trim($respuesta);
                            $accidente->respuestas()->create($res);
                        }

                        if ($respuestaValor != '') {
                            $res['respuesta_numero'] = $respuestaValor;
                            $accidente->respuestas()->create($res);
                        }
                        break;

                    case 'check':
                        if (is_array($respuesta)) {
                            foreach ($respuesta as $r) {
                                $res['respuesta_texto'] = trim($r);
                                if ($res['respuesta_texto'] != '') {
                                    $accidente->respuestas()->create($res);
                                }
                            }
                        } else {
                            $res['respuesta_texto'] = trim($respuesta);
                            if ($res['respuesta_texto'] != '') {
                                $accidente->respuestas()->create($res);
                            }
                        }
                        break;

                    case 'date':
                        if (is_array($respuesta)) {
                            $respuestaValor = current($respuesta);
                            $accidente->respuestas()->create($res);
                        } else {
                            $respuestaValor = trim($respuesta);
                            $accidente->respuestas()->create($res);
                        }

                        if ($respuestaValor != '') {
                            $res['respuesta_fecha'] = $respuestaValor;
                            $accidente->respuestas()->create($res);
                        }
                        break;

                    default:
                        // no hacer nada, ignorar dato
                        break;
                }
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
            ], 200);
        } else {
            if (!is_null($autorizacion)) {
                return redirect('autorizaciones/received');
            } else {
                return redirect('accidentes/' . $accidente->id);
            }
        }
    }

    /**
     * Editar Accidente
     */
    public function editarAccidente(AccidenteService $accidenteService, $accidenteId)
    {
        $accidenteService->setUser(Auth::user());
        $estadoPermiso = $accidenteService->puedeEditar($accidenteId);

        if (!$estadoPermiso['puedeEditar']) {
            return redirect()
                ->route('accidentes.index')
                ->withMessages([
                    'type' => 'error',
                    'text' => $estadoPermiso['mensaje'],
                ]);
        }

        $accidente = Accidente::with([
            'localidad',
            'localidad.distrito',
            'localidad.distrito.departamento',
            'respuestas' => function ($q) {
                $q->orderBy('id');
            },
            'respuestas.pregunta',
            'respuestas.pregunta.grupoDePreguntas',
            'respuestas.usuario',
        ])->find($accidenteId);

        $this->params['title'] = 'Editar Accidente';
        $this->params['subtitle'] = $accidente->caratula;
        $this->params['accidente'] = $accidente;
        $this->params['localidades'] = Localidad::with(['distrito', 'distrito.departamento'])->get();
        $this->params['agrupadores_accidente'] = $accidenteService->obtenerAgrupadores($accidenteId);
        $this->params['grupo_preguntas'] = $accidenteService->obtenerGruposPreguntas();
        $this->params['respuestas_accidente'] = $accidenteService->obtenerRespuestas($accidenteId);

        return view($this->route_name . '.editar')->with('params', $this->params);
    }

    public function actualizarAccidente(AccidenteService $accidenteService, Request $request, $accidenteId)
    {
        $accidenteService->setUser(Auth::user());
        $estadoPermiso = $accidenteService->puedeEditar($accidenteId);

        if (!$estadoPermiso['puedeEditar']) {
            return response()->json([
                'status' => 'error',
                'message' => $estadoPermiso['mensaje'],
                'data' => $accidenteId,
            ], 200);
        }

        $validator = (new FormularioAccidente)->cabecera($request->input('accidente', []), true);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => implode("\n", $validator->errors()->all())]);
        }
        $ediciones = $request->input('ediciones', []);
        if (!is_array($ediciones)) {
            return response()->json(['status' => 'error', 'message' => 'Formato de ediciones inválido.']);
        }
        $answers = [];
        foreach (Respuesta::where('accidente_id', $accidenteId)->get() as $row) {
            $answers[$row->agrupador][$row->pregunta_id][] = $row->respuesta_texto ?? $row->respuesta_numero ?? ($row->respuesta_fecha ? $row->respuesta_fecha->format('Y-m-d') : '');
        }
        $original = $answers;
        foreach ($ediciones as $edit) {
            if (!is_array($edit) || !isset($edit['agrupador'], $edit['preguntaId'], $edit['modoEdicion']) || !in_array($edit['modoEdicion'], ['agregar', 'modificar', 'borrar'])) {
                return response()->json(['status' => 'error', 'message' => 'Edición inválida.']);
            }
            if (!Pregunta::where('id', $edit['preguntaId'])
                ->whereHas('grupoDePreguntas', fn ($query) => $query->where('visible', true))
                ->exists()) {
                return response()->json(['status' => 'error', 'message' => 'La pregunta no está disponible para edición.']);
            }
            $answers[$edit['agrupador']][$edit['preguntaId']] = $edit['modoEdicion'] === 'borrar' ? '' : ($edit['respuestaNueva'] ?? '');
        }
        if ($ediciones) {
            $ids = [];
            foreach ($answers as $group) $ids = array_merge($ids, array_keys($group));
            $groupIds = Pregunta::whereIn('id', $ids)->pluck('grupo_de_preguntas_id');
            $questions = Pregunta::whereIn('grupo_de_preguntas_id', $groupIds)->get();
            $errors = (new FormularioAccidente)->respuestas($answers, $questions);
            if ($errors) return response()->json(['status' => 'error', 'message' => implode("\n", array_column($errors, 'mensaje'))]);
            // Descarta cambios y respuestas anteriores que ya no aplican al nuevo contexto.
            $ediciones = array_values(array_filter($ediciones, fn ($e) => array_key_exists($e['preguntaId'], $answers[$e['agrupador']] ?? [])));
            foreach ($original as $key => $group) {
                foreach ($group as $id => $value) {
                    if (!array_key_exists($id, $answers[$key] ?? [])) {
                        $ediciones[] = ['modoEdicion' => 'borrar', 'preguntaId' => $id, 'agrupador' => $key];
                    }
                }
            }
        }
        $cabecera = $request->get('accidente');
        $gravedades = [];
        foreach ($answers as $group) {
            if (isset($group[134])) $gravedades[] = FormularioAccidente::valor($group[134]);
        }
        if ($gravedades) {
            $cabecera['participantes_siniestro'] = count($gravedades);
            $cabecera['fallecidos'] = count(array_filter($gravedades, fn ($v) => $v === 'Fallecido (murió de inmediato o en las 24 horas siguientes)'));
            $cabecera['heridos'] = count(array_filter($gravedades, fn ($v) => in_array($v, ['Lesionado hospitalizado (al menos 24 horas)', 'Lesionado no hospitalizado (menos de 24 horas)'])));
            $cabecera['ilesos'] = count(array_filter($gravedades, fn ($v) => $v === 'Ileso (no sufrió ningún traumatismo)'));
        }
        $resultado = $accidenteService->actualizarAccidente($accidenteId, $cabecera, $ediciones);

        return response()->json([
            'status' => 'success',
            'message' => $resultado['mensaje'],
            'payload' => [
                'id' => $accidenteId,
            ],
        ], 200);
    }

    /**
     * Importar datos de accidente
     *
     * @return Response
     */
    public function import()
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'import')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $this->params['title'] = 'Importar Datos de Accidentes';

        return view($this->route_name . '.import')->with('params', $this->params);
    }

    /**
     * Carga archivo de datos
     *
     * @return Response
     */
    public function uploadDataFile(Request $request)
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'import')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        if ($request->hasFile('file')) {
            $targetPath = storage_path('data_import/usuario-' . Auth::user()->id);

            // Verificar que archivo tiene extensión CSV
            if (strtolower($request->file('file')->getClientOriginalExtension()) != 'csv') {
                return redirect('accidentes/importar')
                    ->withMessages(['type' => 'error', 'text' => 'El archivo de datos cargado no tiene extensión CSV!']);
            }

            $baseFileName = Str::random(30);
            $targetFileName = $baseFileName . '.' . $request->file('file')->getClientOriginalExtension();

            Storage::makeDirectory($targetPath);

            $request->file('file')->move($targetPath, $targetFileName);

            $this->dispatch(new ProcesarArchivoDatos([
                'user' => Auth::user(),
                'originalName' => $request->file('file')->getClientOriginalName(),
                'targetPath' => $targetPath,
                'targetFileName' => $targetFileName,
            ]));

            Auth::user()->notificaciones()->create([
                'fecha' => date('Y-m-d H:i:s'),
                'asunto' => 'Importación de Datos',
                'mensaje' => "El SIAT ha recibido correctamente su archivo de datos {$request->file('file')->getClientOriginalName()} y se procesará en breve.\nRecibirá otra notificación una vez que el sistema empiece a procesar su archivo de datos.",
                'leido' => false,
            ]);

            return redirect('accidentes/importar')
                ->withMessages(['type' => 'success', 'text' => 'Se ha cargado el archivo de datos correctamente, el sistema le notificará una vez que el proceso de importación de los datos finalice.']);
        } else {
            return redirect('accidentes/importar')
                ->withMessages(['type' => 'error', 'text' => 'Por favor, seleccione un archivo de datos para procesar!']);
        }
    }

    /**
     * Bajar formato de archivo de datos
     *
     * @return Response
     */
    public function downloadFileFormat()
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'import')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

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

        $csvContents = [
            ['caratula', 'Caratula del Accidente'],
            ['expediente_judicial', 'Expediente Judicial'],
            ['sumario_policial', 'Sumario Policial'],
            ['otro', 'Otro'],
            ['dependencia', 'Dependencia'],
            ['fecha_accidente', 'Fecha del Accidente (YYYY-MM-DD)'],
            ['lugar', 'Lugar'],
            ['altura', 'Altura'],
            ['tipo_camino', 'Tipo Camino'],
            ['localidad_id', 'ID de Localidad'],
            ['codigo_postal', 'Codigo Postal'],
            ['gps_latitud', 'GPS Latitud'],
            ['gps_longitud', 'GPS Longitud'],
            ['heridos', 'Cantidad de Heridos'],
            ['fallecidos', 'Cantidad de Fallecidos'],
            ['ilesos', 'Cantidad de Ilesos'],
            ['participantes_siniestro', 'Total Participantes Siniestro'],
            ['fecha_apertura', 'Fecha de Apertura (YYYY-MM-DD HH:MM:SS)'],
        ];

        foreach ($grupos as $grupo) {
            foreach ($grupo->preguntas as $pregunta) {
                $csvContents[] = [$pregunta->id, 'Respuesta para ' . $pregunta->pregunta . ' va aquí', 'Número de Agrupación va aquí'];
            }
        }

        // sep=|
        $csvWriter = Writer::createFromFileObject(new \SplTempFileObject());
        $csvWriter->setDelimiter(',');
        $csvWriter->setNewline("\r\n"); // use windows line endings
        $csvWriter->setOutputBOM(Writer::BOM_UTF8); //adding the BOM sequence on output
        $csvWriter->insertAll($csvContents);

        return response($csvWriter, 200)
            ->header('Content-Disposition', 'attachment;filename=siat_formato_archivo.csv')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0') // private?
            ->header('Content-type', 'text/csv') // application/force-download?
            ->header('Expires', '0')
            ->header('Pragma', 'public');
    }

    /**
     * Bajar diccionario de datos
     *
     * @return Response
     */
    public function downloadDataDictionary()
    {
        // Verifica permiso
        if (!RequestUtils::havePermission($this->route_name, 'import')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

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

        $cabecera = [
            'Nombre del Campo' => 'Descripción del Campo',
            'caratula' => 'Caratula del Accidente',
            'expediente_judicial' => 'Expediente Judicial',
            'sumario_policial' => 'Sumario Policial',
            'otro' => 'Otro',
            'dependencia' => 'Dependencia',
            'fecha_accidente' => 'Fecha del Accidente (YYYY-MM-DD)',
            'lugar' => 'Lugar',
            'altura' => 'Altura',
            'tipo_camino' => 'Tipo Camino',
            'localidad_id' => 'ID de Localidad',
            'codigo_postal' => 'Codigo Postal',
            'gps_latitud' => 'GPS Latitud',
            'gps_longitud' => 'GPS Longitud',
            'heridos' => 'Cantidad de Heridos',
            'fallecidos' => 'Cantidad de Fallecidos',
            'ilesos' => 'Cantidad de Ilesos',
            'participantes_siniestro' => 'Total Participantes Siniestro',
            'fecha_apertura' => 'Fecha de Apertura (YYYY-MM-DD HH:MM:SS)',
        ];

        // sep=|
        $csvWriter = Writer::createFromFileObject(new \SplTempFileObject());
        $csvWriter->setDelimiter(',');
        $csvWriter->setNewline("\r\n"); // use windows line endings
        $csvWriter->setOutputBOM(Writer::BOM_UTF8); //adding the BOM sequence on output
        foreach ($cabecera as $field => $value) {
            $csvWriter->insertOne([$field, $value ?? 'Valor Inválido']);
        }

        foreach ($grupos as $grupo) {
            foreach ($grupo->preguntas as $pregunta) {
                $fieldName = $pregunta->id;
                $fieldValue = 'Pregunta ' . $pregunta->pregunta . '.';
                switch ($pregunta->tipo) {
                    case 'check':
                        $fieldValue .= ' Puede elegir una de las siguientes opciones: ' . str_replace('|', ', ', $pregunta->opciones);
                        if ($pregunta->mostrar_otro == 1) {
                            $fieldValue .= ' o ingresar cualquier otro valor';
                        }
                        $fieldValue .= ' Puede definir más de un valor separándolos con el carácter | (por ejemplo: perro|gato|loro).';
                        break;

                    case 'date':
                        $fieldValue .= ' Ingrese una fecha en formato YYYY-MM-DD, por ejemplo, para el 10 de Diciembre de 2014, ingrese 2014-12-10.';
                        break;

                    case 'number':
                        $fieldValue .= ' Ingrese un valor numérico.';
                        break;

                    case 'radio':
                        $fieldValue .= ' Puede elegir una de las siguientes opciones: ' . str_replace('|', ', ', $pregunta->opciones);
                        if ($pregunta->mostrar_otro == 1) {
                            $fieldValue .= ' o ingresar cualquier otro valor';
                        }
                        $fieldValue .= '. Recuerde que puede elegir sólo un valor (y no varios).';
                        break;

                    case 'text':
                        $fieldValue .= ' Puede ingresar cualquier texto.';
                        break;

                    default:
                        break;
                }

                $csvWriter->insertOne([$fieldName, $fieldValue ?? 'Valor Inválido', 'Número de Agrupación. Defina un valor numérico único para agrupar varias respuestas que corresponden a un mismo objeto (vehículo, persona, etc).']);
            }
        }

        return response($csvWriter, 200)
            ->header('Content-Disposition', 'attachment;filename=siat_diccionario_datos.csv')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Content-type', 'text/csv')
            ->header('Expires', '0')
            ->header('Pragma', 'public');
    }

    /**
     * Bajar formulario de preguntas
     *
     * @return Response
     */
    // FIXME: Se ha comentado para ser reemplazado por la descarga de formulario estaticos hasta se descida sobre
    //        la creacion de formulario dinamico
    /*public function downloadQuestionsForm()
    {
        // Verifica permiso
        if (! RequestUtils::havePermission($this->route_name, 'import')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        // Obtener configuración
        $formato_formulario_unico = app(GeneralSettings::class)->formato_formulario_unico;
        $logo_membrete = Settings::logo_membrete();

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

        // Si Formato es Expandido
        // TODO: unificar código de ambos formatos en uno solo
        if ($formato_formulario_unico == 'expandido') {
            $pdf_view = $this->route_name.'.formulario-unico-expandido';

            // Agrupar en "filas"
            $filas = [];
            foreach ($grupos as $grupo) {
                if ($grupo->preguntas->count() > 0) {
                    $rowIndex = 0;
                    $filas[$grupo->id]['nombre'] = $grupo->nombre;
                    $filas[$grupo->id]['rows'] = [];
                    foreach ($grupo->preguntas as $pregunta) {
                        $preguntaArray = $pregunta->toArray();

                        $countOpciones = 0;
                        if (trim($pregunta->opciones) != '') {
                            $preguntaArray['opciones'] = explode('|', $pregunta->opciones);
                            $countOpciones = count($preguntaArray['opciones']);
                        }

                        $filas[$grupo->id]['rows'][$rowIndex]['cols'][] = $preguntaArray;
                        if (count($filas[$grupo->id]['rows'][$rowIndex]['cols']) == 4) {
                            $filas[$grupo->id]['rows'][$rowIndex]['colCount'] = count($filas[$grupo->id]['rows'][$rowIndex]['cols']);
                            $rowIndex++;
                        }
                    }

                    if (isset($filas[$grupo->id]['rows'][$rowIndex]) and ! isset($filas[$grupo->id]['rows'][$rowIndex]['colCount'])) {
                        $filas[$grupo->id]['rows'][$rowIndex]['colCount'] = count($filas[$grupo->id]['rows'][$rowIndex]['cols']);
                    }
                }
            }
        }

        // Si Formato es Condensando (esto se pidió en el 2016)
        if ($formato_formulario_unico == 'condensado') {
            $pdf_view = $this->route_name.'.formulario-unico';
            // Agrupar en "filas"
            $filas = [];
            foreach ($grupos as $grupo) {
                if ($grupo->preguntas->count() > 0) {
                    $rowIndex = 0;
                    $filas[$grupo->id]['nombre'] = $grupo->nombre;
                    $filas[$grupo->id]['rows'] = [];
                    foreach ($grupo->preguntas as $pregunta) {
                        $preguntaArray = $pregunta->toArray();

                        $countOpciones = 0;
                        if (trim($pregunta->opciones) != '') {
                            $preguntaArray['opciones'] = explode('|', $pregunta->opciones);
                            $countOpciones = count($preguntaArray['opciones']);
                        }

                        if (in_array($pregunta->tipo, ['check', 'radio']) and $countOpciones > 4) {
                            if (isset($filas[$grupo->id]['rows'][$rowIndex]['cols']) and count($filas[$grupo->id]['rows'][$rowIndex]['cols']) > 0) {
                                $filas[$grupo->id]['rows'][$rowIndex]['colCount'] = count($filas[$grupo->id]['rows'][$rowIndex]['cols']);
                                $rowIndex++;
                            }
                            $filas[$grupo->id]['rows'][$rowIndex]['cols'][] = $preguntaArray;
                            $filas[$grupo->id]['rows'][$rowIndex]['colCount'] = count($filas[$grupo->id]['rows'][$rowIndex]['cols']);
                            $rowIndex++;
                        } else {
                            $filas[$grupo->id]['rows'][$rowIndex]['cols'][] = $preguntaArray;
                            if (count($filas[$grupo->id]['rows'][$rowIndex]['cols']) == 3) {
                                $filas[$grupo->id]['rows'][$rowIndex]['colCount'] = count($filas[$grupo->id]['rows'][$rowIndex]['cols']);
                                $rowIndex++;
                            }
                        }
                    }

                    if (isset($filas[$grupo->id]['rows'][$rowIndex]) and ! isset($filas[$grupo->id]['rows'][$rowIndex]['colCount'])) {
                        $filas[$grupo->id]['rows'][$rowIndex]['colCount'] = count($filas[$grupo->id]['rows'][$rowIndex]['cols']);
                    }
                }
            }
        }

        $pdf = PDF::loadView($pdf_view, compact('logo_membrete', 'filas'));
        $pdf->setPaper('Legal')
            ->setOption('margin-top', 5)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 2)
            ->setOption('enable-local-file-access', true);

        return $pdf->stream();
    }*/
    public function downloadQuestionsForm()
    {
        return $this->downloadStaticPDF('documento_formulario.pdf');
    }

    /**
     * Bajar Anexo de Vehículos
     *
     * @return Response
     */
    // FIXME: Se ha comentado para ser reemplazado por la descarga de formulario estaticos hasta se descida sobre
    //        la creacion de formulario dinamico
    /*public function downloadAnexoVehiculos()
    {
        // Obtener grupos y preguntas masters
        $grupos = GrupoDePreguntas::where('nombre', 'Datos del Vehículo')
                    ->with(['preguntas' => function ($query) {
                        $query->orderBy('orden', 'asc');
                    }])
                    ->get();

        $formato_formulario_unico = app(GeneralSettings::class)->formato_formulario_unico;
        $separador_opciones = ($formato_formulario_unico == 'expandido') ? '<br />' : '&nbsp;';

        // Agrupar en "filas"
        $filas = [];
        foreach ($grupos as $grupo) {
            $rowIndex = 0;
            $filas[$grupo->id]['nombre'] = $grupo->nombre;
            $filas[$grupo->id]['rows'] = [];
            foreach ($grupo->preguntas as $pregunta) {
                $preguntaArray = $pregunta->toArray();

                $countOpciones = 0;
                if (trim($pregunta->opciones) != '') {
                    $preguntaArray['opciones'] = explode('|', $pregunta->opciones);
                    $countOpciones = count($preguntaArray['opciones']);
                }

                if (in_array($pregunta->tipo, ['check', 'radio']) and $countOpciones > 4) {
                    if (isset($filas[$grupo->id]['rows'][$rowIndex]['cols']) and count($filas[$grupo->id]['rows'][$rowIndex]['cols']) > 0) {
                        $filas[$grupo->id]['rows'][$rowIndex]['colCount'] = count($filas[$grupo->id]['rows'][$rowIndex]['cols']);
                        $rowIndex++;
                    }
                    $filas[$grupo->id]['rows'][$rowIndex]['cols'][] = $preguntaArray;
                    $filas[$grupo->id]['rows'][$rowIndex]['colCount'] = count($filas[$grupo->id]['rows'][$rowIndex]['cols']);
                    $rowIndex++;
                } else {
                    $filas[$grupo->id]['rows'][$rowIndex]['cols'][] = $preguntaArray;
                    if (count($filas[$grupo->id]['rows'][$rowIndex]['cols']) == 3) {
                        $filas[$grupo->id]['rows'][$rowIndex]['colCount'] = count($filas[$grupo->id]['rows'][$rowIndex]['cols']);
                        $rowIndex++;
                    }
                }
            }

            if (isset($filas[$grupo->id]['rows'][$rowIndex]) and ! isset($filas[$grupo->id]['rows'][$rowIndex]['colCount'])) {
                $filas[$grupo->id]['rows'][$rowIndex]['colCount'] = count($filas[$grupo->id]['rows'][$rowIndex]['cols']);
            }
        }

        $logo_membrete = Settings::logo_membrete();
        $pdf = PDF::loadView($this->route_name.'.anexo-vehiculos', compact('logo_membrete', 'filas', 'separador_opciones'));
        $pdf->setPaper('Legal')
            ->setOrientation('portrait')
            ->setOption('margin-top', 2)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 2)
            ->setOption('enable-local-file-access', true);

        return $pdf->stream();
    }*/

    public function downloadAnexoVehiculos()
    {
        return $this->downloadStaticPDF('documento_vehiculos.pdf');
    }

    /**
     * Bajar Anexo de Personas
     *
     * @return Response
     */
    // FIXME: Se ha comentado para ser reemplazado por la descarga de formulario estaticos hasta se descida sobre
    //        la creacion de formulario dinamico
    /*public function downloadAnexoPersonas()
    {
        // Obtener grupos y preguntas masters
        $grupos = GrupoDePreguntas::where('nombre', 'Datos de la persona involucrada')
                    ->with(['preguntas' => function ($query) {
                        $query->orderBy('orden', 'asc');
                    }])
                    ->get();

        $formato_formulario_unico = app(GeneralSettings::class)->formato_formulario_unico;
        $separador_opciones = ($formato_formulario_unico == 'expandido') ? '<br />' : '&nbsp;';

        // Agrupar en "filas"
        $filas = [];
        foreach ($grupos as $grupo) {
            $rowIndex = 0;
            $filas[$grupo->id]['nombre'] = $grupo->nombre;
            $filas[$grupo->id]['rows'] = [];
            foreach ($grupo->preguntas as $pregunta) {
                $preguntaArray = $pregunta->toArray();

                $countOpciones = 0;
                if (trim($pregunta->opciones) != '') {
                    $preguntaArray['opciones'] = explode('|', $pregunta->opciones);
                    $countOpciones = count($preguntaArray['opciones']);
                }

                if (in_array($pregunta->tipo, ['check', 'radio']) and $countOpciones > 4) {
                    if (isset($filas[$grupo->id]['rows'][$rowIndex]['cols']) and count($filas[$grupo->id]['rows'][$rowIndex]['cols']) > 0) {
                        $filas[$grupo->id]['rows'][$rowIndex]['colCount'] = count($filas[$grupo->id]['rows'][$rowIndex]['cols']);
                        $rowIndex++;
                    }
                    $filas[$grupo->id]['rows'][$rowIndex]['cols'][] = $preguntaArray;
                    $filas[$grupo->id]['rows'][$rowIndex]['colCount'] = count($filas[$grupo->id]['rows'][$rowIndex]['cols']);
                    $rowIndex++;
                } else {
                    $filas[$grupo->id]['rows'][$rowIndex]['cols'][] = $preguntaArray;
                    if (count($filas[$grupo->id]['rows'][$rowIndex]['cols']) == 3) {
                        $filas[$grupo->id]['rows'][$rowIndex]['colCount'] = count($filas[$grupo->id]['rows'][$rowIndex]['cols']);
                        $rowIndex++;
                    }
                }
            }

            if (isset($filas[$grupo->id]['rows'][$rowIndex]) and ! isset($filas[$grupo->id]['rows'][$rowIndex]['colCount'])) {
                $filas[$grupo->id]['rows'][$rowIndex]['colCount'] = count($filas[$grupo->id]['rows'][$rowIndex]['cols']);
            }
        }

        $logo_membrete = Settings::logo_membrete();
        $pdf = PDF::loadView($this->route_name.'.anexo-personas', compact('logo_membrete', 'filas', 'separador_opciones'));
        $pdf->setPaper('Legal')
            ->setOrientation('portrait')
            ->setOption('margin-top', 5)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 2)
            ->setOption('enable-local-file-access', true);

        return $pdf->stream();
    }
     */
    public function downloadAnexoPersonas()
    {
        return $this->downloadStaticPDF('documentos_personas.pdf');
    }

    /**
     *
     */
    private function downloadStaticPDF($filename)
    {
        if (Storage::exists("archive/$filename")) {
            $path = storage_path("app/archive/$filename");
            return response()->file($path);
        } else {
            return response()->json(['error' => 'El archivo PDF no existe.'], 404);
        }
    }


    /**
     * Obtener estadísticas
     *
     * @return Response
     */
    public function stats(Request $request)
    {
        $stats = [];

        if ($request->has('stat')) {
            $stat = $request->get('stat');

            $accidente = new Accidente;
            $derivacion = new Derivacion;
            $autorizacion = new Autorizacion;

            if (Auth::user()->hasRole('sysadmin')) {
                // Do nothing
            } elseif (Auth::user()->hasRole('administrador')) {
                // $accidente  = $accidente->where('institucion_id', Auth::user()->sede->institucion->id);
                $derivacion = $derivacion->where('institucion_id', Auth::user()->sede->institucion_id);
                // $autorizacion = $autorizacion->where('otorga_usuario_id', Auth::user()->id);
            } elseif (Auth::user()->hasRole('directivo')) {
                // $accidente  = $accidente->where('sede_id', Auth::user()->sede->id);
                $derivacion = $derivacion->where('institucion_id', Auth::user()->sede->institucion_id);
                // $autorizacion = $autorizacion->where('otorga_usuario_id', Auth::user()->id);
            } else {
                // $accidente  = $accidente->where('usuario_id', Auth::user()->id);
                $derivacion = $derivacion->where('institucion_id', Auth::user()->sede->institucion_id);
                // $autorizacion = $autorizacion->where('otorga_usuario_id', Auth::user()->id);
            }

            switch ($stat) {
                case 'quick':
                    $stats['total_accidentes'] = 0;
                    $stats['accidentes_derivados'] = 0;
                    $stats['autorizaciones_enviadas'] = 0;

                    // Total Accidentes
                    $results = $accidente->selectRaw('COUNT(*) AS total_accidentes')
                        ->get()
                        ->toArray();
                    $stats['total_accidentes'] = (int)$results[0]['total_accidentes'];

                    // Total Derivaciones
                    $results = $derivacion->selectRaw('COUNT(*) AS accidentes_derivados')
                        ->get()
                        ->toArray();
                    $stats['accidentes_derivados'] = (int)$results[0]['accidentes_derivados'];

                    // Total Autorizaciones
                    $results = $autorizacion->selectRaw('COUNT(*) AS autorizaciones_enviadas')
                        ->get()
                        ->toArray();
                    $stats['autorizaciones_enviadas'] = (int)$results[0]['autorizaciones_enviadas'];
                    break;

                case 'cluster':
                    $inicio = new Carbon($request->get('inicio') . ' 00:00:00');
                    $fin = new Carbon($request->get('fin') . ' 23:59:59');
                    $stats = $accidente
                        ->where('fecha_accidente', '>=', $inicio)
                        ->where('fecha_accidente', '<=', $fin)
                        ->select('id', 'caratula', 'gps_latitud', 'gps_longitud', 'fecha_accidente')
                        ->where('gps_latitud', '<>', '')
                        ->where('gps_longitud', '<>', '')
                        ->get();
                    break;

                case 'victimas':
                    if ($request->has('inicio') and $request->has('fin')) {
                        $inicio = new Carbon($request->get('inicio') . ' 00:00:00');
                        $fin = new Carbon($request->get('fin') . ' 23:59:59');
                        $records = $accidente->selectRaw('DATE(fecha_accidente) AS fecha, SUM(heridos) AS heridos, SUM(fallecidos) AS fallecidos, SUM(ilesos) AS ilesos, SUM(participantes_siniestro) AS total_personas')
                            ->where('fecha_accidente', '>=', $inicio)
                            ->where('fecha_accidente', '<=', $fin)
                            ->groupBy(DB::Raw('DATE(fecha_accidente)'))
                            ->orderBy(DB::Raw('DATE(fecha_accidente)'))
                            ->get()
                            ->toArray();

                        $rowStats = [];
                        foreach ($records as $row) {
                            $rowStats[$row['fecha']] = $row;
                        }

                        $begin = new \DateTime($inicio);
                        $end = new \DateTime($fin);
                        $end = $end->modify('+1 day');
                        $interval = new \DateInterval('P1D');
                        $daterange = new \DatePeriod($begin, $interval, $end);
                        foreach ($daterange as $date) {
                            $stats['labels'][] = $date->format('d/m');
                            if (isset($rowStats[$date->format('Y-m-d')])) {
                                $stats['heridos'][] = (int)$rowStats[$date->format('Y-m-d')]['heridos'];
                                $stats['fallecidos'][] = (int)$rowStats[$date->format('Y-m-d')]['fallecidos'];
                                $stats['ilesos'][] = (int)$rowStats[$date->format('Y-m-d')]['ilesos'];
                                $stats['total_personas'][] = (int)$rowStats[$date->format('Y-m-d')]['total_personas'];
                            } else {
                                $stats['heridos'][] = 0;
                                $stats['fallecidos'][] = 0;
                                $stats['ilesos'][] = 0;
                                $stats['total_personas'][] = 0;
                            }
                        }
                    }
                    break;
                case 'accidentes':
                    if ($request->has('inicio') and $request->has('fin')) {
                        $inicio = new Carbon($request->get('inicio') . ' 00:00:00');
                        $fin = new Carbon($request->get('fin') . ' 23:59:59');
                        $records = $accidente->selectRaw('DATE(fecha_accidente) AS fecha, SUM(heridos) AS heridos, SUM(fallecidos) AS fallecidos, SUM(ilesos) AS ilesos, SUM(participantes_siniestro) AS total_personas,COUNT(fecha_accidente) as total_accidentes')
                            ->where('fecha_accidente', '>=', $inicio)
                            ->where('fecha_accidente', '<=', $fin)
                            ->orderBy(DB::Raw('DATE(fecha_accidente)'))
                            ->groupBy(DB::Raw('DATE(fecha_accidente)'))
                            ->get()
                            ->toArray();

                        $rowStats = [];
                        foreach ($records as $row) {
                            $rowStats[$row['fecha']] = $row;
                        }

                        $begin = new \DateTime($inicio);
                        $end = new \DateTime($fin);
                        $end = $end->modify('+1 day');
                        $interval = new \DateInterval('P1D');
                        $daterange = new \DatePeriod($begin, $interval, $end);
                        foreach ($daterange as $date) {
                            $stats['labels'][] = $date->format('d/m');
                            if (isset($rowStats[$date->format('Y-m-d')])) {
                                $stats['heridos'][] = (int)$rowStats[$date->format('Y-m-d')]['heridos'];
                                $stats['fallecidos'][] = (int)$rowStats[$date->format('Y-m-d')]['fallecidos'];
                                $stats['total_accidentes'][] = (int)$rowStats[$date->format('Y-m-d')]['total_accidentes'];
                                $stats['ilesos'][] = (int)$rowStats[$date->format('Y-m-d')]['ilesos'];
                                $stats['total_personas'][] = (int)$rowStats[$date->format('Y-m-d')]['total_personas'];
                            } else {
                                $stats['heridos'][] = 0;
                                $stats['fallecidos'][] = 0;
                                $stats['ilesos'][] = 0;
                                $stats['total_accidentes'][] = 0;
                                $stats['total_personas'][] = 0;
                            }
                        }
                    }
                    break;
                default:
                    // code...
                    break;
            }
        }

        return $stats;
    }

    public function distritos(Request $request, $departamentoId)
    {
        $departamento = Departamento::select('id', 'nombre')->with(['distritos' => function ($query) {
            $query->select('id', 'nombre', 'departamento_id');
            $query->orderBy('nombre');
        }])->findOrFail($departamentoId);

        return response()->json([
            'status' => 'success',
            'message' => '',
            'data' => $departamento,
        ], 200);
    }

    public function localidades(Request $request, $distritoId)
    {
        $distrito = Distrito::select('id', 'nombre')->with(['localidades' => function ($query) {
            $query->select('id', 'nombre', 'distrito_id');
            $query->orderBy('nombre');
        }])->findOrFail($distritoId);

        return response()->json([
            'status' => 'success',
            'message' => '',
            'data' => $distrito,
        ], 200);
    }

    public function busquedaPorIndicadores(Request $request)
    {
        $this->params['search'] = 'NO';

        // Verifica permisos
        if (!RequestUtils::havePermission($this->route_name, 'any')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

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
                    'nombre' => $grupo->nombre . ' - ' . $pregunta->pregunta,
                ];
            }
        }

        $this->params['data']['indicadores'] = $campos;
        $this->params['data']['items'] = [];

        return view($this->route_name . '.busqueda_por_indicadores')->with('params', $this->params);
    }

    public function getOpciones(Request $request)
    {
        $id = $request->only('pregunta_id');
        $pregunta = Pregunta::where('id', '=', $id)->first();

        if ($pregunta) {
            if (in_array($pregunta->tipo, ['number', 'text'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tipo de Pregunta incorrecto.',
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Listado de Opciones para la Pregunta',
                'data' => explode('|', $pregunta->opciones),
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'No se encontró la Pregunta seleccionada.',
        ], 400);
    }

    public function filtrarPorIndicadores(Request $request)
    {
        $filter = $request->only(['indicador', 'valor', 'fecha_inicio', 'fecha_fin']);
        $data = [];
        $response = [
            'status' => 'error',
            'message' => 'No se han recibido todos los parámetros.',
        ];

        if (!isset($filter['indicador']) || !isset($filter['valor']) || !isset($filter['fecha_inicio']) || !isset($filter['fecha_fin'])) {
            return response()->json($response, 400);
        }

        $pregunta = Pregunta::where('id', '=', $filter['indicador'])->first();

        if (!$pregunta) {
            $response['message'] = 'No se ha encontrado la pregunta seleccionada.';

            return response()->json($response, 400);
        }

        $start_date = Carbon::parse($filter['fecha_inicio'] . ' 00:00:00');
        $end_date = Carbon::parse($filter['fecha_fin'] . ' 23:59:59');
        $valor = $filter['valor'];

        $queryRespuestas = Respuesta::select('accidente_id')->distinct('accidente_id')->where('pregunta_id', '=', $pregunta->id);

        if (in_array($pregunta->tipo, ['text', 'radio', 'check'])) {
            $queryRespuestas->where('respuesta_texto', 'like', '%' . $valor . '%');
        }

        if ($pregunta->tipo == 'number') {
            $queryRespuestas->where('respuesta_numero', '=', $valor);
        }

        if ($pregunta->tipo == 'date') {
            $fecha = Carbon::parse($valor);
            $queryRespuestas->where('respuesta_fecha', '=', $fecha);
        }

        $respuestas = $queryRespuestas->get()->pluck('accidente_id');

        if (Auth::user()->hasRole('sysadmin')) {
            $q = Accidente::with(['localidad', 'localidad.distrito', 'localidad.distrito.departamento', 'usuario', 'institucion', 'sede']);
            $q->where('fecha_accidente', '>=', $start_date);
            $q->where('fecha_accidente', '<=', $end_date);
            $q->whereIn('id', $respuestas);

            $data = $q->orderBy('created_at', 'desc')->get();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Consulta Exitosa',
            'data' => $data,
        ], 200);
    }
}
