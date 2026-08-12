<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestUtils;
use App\Models\Institucion;
use App\Models\ReporteDatos;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use League\Csv\Writer;
use PDF;
use Settings;

class ReporteDatosController extends Controller
{
    /**
     * Ruta
     *
     * @var string
     */
    private $route_name = 'reporte_datos';

    /**
     * Parámetros
     *
     * @var array
     */
    private $params = [];

    private $comparadores = [
        'igual' => 'es igual a',
        'no_es_igual' => 'no es igual a',
        'mayor' => 'es mayor a',
        'mayor_o_igual' => 'es mayor o igual a',
        'menor' => 'es menor a',
        'menor_o_igual' => 'es menor o igual a',
        'contiene' => 'contiene a',
        'no_contiene' => 'no contiene a',
    ];

    private $filtros = [
        'departamento_id' => 'Departamento',
        'distrito_id' => 'Distrito',
        'localidad_id' => 'Localidad',
        'caratula' => 'Carátula',
        'expediente_judicial' => 'Expediente Judicial',
        'sumario_policial' => 'Sumario Policial',
        'otro' => 'Otro',
        'dependencia' => 'Dependencia',
        'lugar' => 'Lugar',
        'tipo_camino' => 'Tipo de Camino',
        'altura' => 'Altura',
        'codigo_postal' => 'Código Postal',
        'heridos' => 'Heridos',
        'fallecidos' => 'Fallecidos',
        'ilesos' => 'Ilesos',
        'participantes_siniestro' => 'Participantes Siniestro',
    ];

    private $campos = [
        'departamento' => 'Departamento',
        'distrito' => 'Distrito',
        'localidad' => 'Localidad',
        'institucion' => 'Institución',
        'sede' => 'Sede',
        'usuario' => 'Usuario',
        'caratula' => 'Carátula',
        'fecha_accidente' => 'Fecha Accidente',
        'expediente_judicial' => 'Expediente Judicial',
        'sumario_policial' => 'Sumario Policial',
        'otro' => 'Otro',
        'dependencia' => 'Dependencia',
        'lugar' => 'Lugar',
        'tipo_camino' => 'Tipo de Camino',
        'altura' => 'Altura',
        'codigo_postal' => 'Código Postal',
    ];

    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        $this->params = [
            'title' => 'REPORTES DE DATOS',
            'subtitle' => 'LISTADO DE REPORTE DE DATOS',
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
        // $this->params['data']['items'] = Auth::user()->reporte_datos()->filteredPaginate($this->params['filter'], $this->params['items_per_page']);
        $this->params['data']['items'] = ReporteDatos::where('usuario_id', Auth::user()->id)
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
    public function create()
    {
        // Verifica permiso
        if (! RequestUtils::havePermission($this->route_name, 'create')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $this->params['subtitle'] = 'NUEVO REPORTE DE DATOS';

        if (Auth::user()->hasRole('sysadmin')) {
            $this->filtros['institucion_id'] = 'Institución';
            $this->filtros['sede_id'] = 'Sede';
        } elseif (Auth::user()->hasRole('administrador')) {
            // nivel institucion
            $this->filtros['sede_id'] = 'Sede';
        } elseif (Auth::user()->hasRole('directivo')) {
            // nivel sede
        } else {
            // nivel usuario
        }

        $this->params['filtros'] = $this->filtros;

        $this->params['comparadores'] = $this->comparadores;

        $this->params['campos'] = $this->campos;

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

            $item = ReporteDatos::create($data);
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
    public function show($id, Request $request)
    {
        // Verifica permiso
        if (! RequestUtils::havePermission($this->route_name, 'list')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        try {
            $reporte = Auth::user()->reporte_datos()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect('/reporte_datos')
                ->withMessages(['type' => 'error', 'text' => 'No se encontró el reporte solicitado.']);
        }

        if ($reporte->compartido == false && $reporte->usuario_id != Auth::user()->id) {
            abort(404);
        } elseif ($reporte->compartido == true && $reporte->usuario_id != Auth::user()->id) {
            if (! is_null($reporte->instituciones) && ! in_array(Auth::user()->sede->institucion_id, collect($reporte->instituciones)->pluck('id')->toArray())) {
                abort(404);
            }
        }

        $fecha_inicio = $request->get('fecha_inicio');
        $fecha_fin = $request->get('fecha_fin');
        $reporteDatos = $columns = collect([]);

        if ($request->has('fecha_inicio') and $request->has('fecha_fin')) {
            $reporteDatos = (new ReporteDatos)->getData($reporte, $request->get('fecha_inicio'), $request->get('fecha_fin'), true, $this->params['items_per_page']);

            $columns = [];
            if ($reporteDatos->count() > 0) {
                foreach ($reporteDatos->first() as $field_key => $field_value) {
                    $columns[$field_key] = ucwords(str_replace('_', ' ', $field_key));
                }
            }
        }

        return view($this->route_name.'.show', compact('reporte', 'reporteDatos', 'columns', 'fecha_inicio', 'fecha_fin'))->with('params', $this->params);
    }

    /**
     * Generates a PDF report
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function pdf($id, Request $request)
    {
        // Verifica permiso
        if (! RequestUtils::havePermission($this->route_name, 'list')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $reporte = Auth::user()->reporte_datos()->findOrFail($id);

        $fecha_inicio = $request->get('fecha_inicio');
        $fecha_fin = $request->get('fecha_fin');
        $reporteDatos = (new ReporteDatos)->getData($reporte, $request->get('fecha_inicio'), $request->get('fecha_fin'), false);

        $columns = [];
        if ($reporteDatos->count() > 0) {
            foreach ($reporteDatos->first() as $field_key => $field_value) {
                $columns[$field_key] = ucwords(str_replace('_', ' ', $field_key));
            }
        }

        $pdfData = [
            'logo_membrete' => Settings::logo_membrete(),
            'user' => Auth::user(),
            'nombres' => Auth::user()->nombreCompleto(),
            'institucion' => Auth::user()->sede->institucion->nombre,
        ];

        $pdf = PDF::loadView($this->route_name.'.pdf', compact('pdfData', 'reporte', 'reporteDatos', 'columns', 'fecha_inicio', 'fecha_fin'));
        $pdf->setPaper('Legal');
        $pdf->setOrientation('landscape');
        $pdf->setOption('header-html', \View::make('pdf.header', compact('pdfData')));
        $pdf->setOption('footer-html', \View::make('pdf.footer', compact('pdfData')));
        $pdf->setOption('enable-local-file-access', true);
        $pdf->setOption('header-spacing', 2);

        return $pdf->stream();
    }

    /**
     * Generates a CSV report
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function csv($id, Request $request)
    {
        // Verifica permiso
        if (! RequestUtils::havePermission($this->route_name, 'list')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $reporte = Auth::user()->reporte_datos()->findOrFail($id);

        $reporteDatos = (new ReporteDatos)->getData($reporte, $request->get('fecha_inicio'), $request->get('fecha_fin'));

        $columns = [];
        if ($reporteDatos->count() > 0) {
            foreach ($reporteDatos->first() as $field_key => $field_value) {
                $columns[$field_key] = ucwords(str_replace('_', ' ', $field_key));
            }
        }

        // sep=|
        $csvWriter = Writer::createFromFileObject(new \SplTempFileObject());
        $csvWriter->setDelimiter(',');
        $csvWriter->setNewline("\r\n"); // use windows line endings
        $csvWriter->setOutputBOM(Writer::BOM_UTF8); //adding the BOM sequence on output

        // Definir cabecera
        $csvHeader = [];
        foreach ($columns as $column_key => $column_name) {
            $csvHeader[] = $column_key;
        }
        $csvWriter->insertOne($csvHeader);

        // Agregar datos
        foreach ($reporteDatos as $item) {
            $csvData = [];
            foreach ($columns as $column_key => $column_name) {
                $csvData[] = (string) $item->$column_key;
            }
            $csvWriter->insertOne($csvData);
        }

        return response($csvWriter, 200)
                    ->header('Content-Disposition', 'attachment;filename=reporte_'.$reporte->id.'.csv')
                    ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0') // private?
                    ->header('Content-type', 'text/csv') // application/force-download?
                    ->header('Expires', '0')
                    ->header('Pragma', 'public');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Verifica permiso
        if (! RequestUtils::havePermission($this->route_name, 'edit')) {
            return redirect('home')
                ->withMessages(['type' => 'error', 'text' => 'No cuenta con permiso para ingresar a la sección solicitada']);
        }

        $this->params['subtitle'] = 'EDITAR REPORTE';

        if (Auth::user()->hasRole('sysadmin')) {
            $this->filtros['institucion_id'] = 'Institución';
            $this->filtros['sede_id'] = 'Sede';
        } elseif (Auth::user()->hasRole('administrador')) {
            // nivel institucion
            $this->filtros['sede_id'] = 'Sede';
        } elseif (Auth::user()->hasRole('directivo')) {
            // nivel sede
        } else {
            // nivel usuario
        }

        $this->params['filtros'] = $this->filtros;

        $this->params['comparadores'] = $this->comparadores;

        $this->params['campos'] = $this->campos;

        $this->params['instituciones'] = Institucion::orderBy('nombre')->get()->pluck('nombre', 'id');

        $this->params['data']['item'] = Auth::user()->reporte_datos()->findOrFail($id);

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
        $row = Auth::user()->reporte_datos()->findOrFail($id);

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
                    ->route('reportes.show', [$id])
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
        $row = Auth::user()->reporte_datos()->findOrFail($id);

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
