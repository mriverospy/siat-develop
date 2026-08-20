<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestUtils;
use App\Models\Accidente;
use App\Models\GrupoDePreguntas;
use App\Models\Pregunta;
use App\Models\Respuesta;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Settings;

class ReportesGenericosController extends Controller
{
    /**
     * Ruta
     *
     * @var string
     */
    private $route_name = 'reportes_genericos';

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
    public function carga_registros_por_institucion(Request $request)
    {
        if (! RequestUtils::isSysadmin()) {
            return redirect('/home');
        }

        $items = null;
        $campo = 'accidentes.fecha_accidente';
        $fechaInicio = Carbon::now()->subDays(29);
        $fechaFin = Carbon::now();
        if ($request->has('tipo') and $request->has('fecha_inicio') and $request->has('fecha_fin')) {
            if ($request->get('tipo') == 'carga') {
                $campo = 'accidentes.created_at';
            }
            $fechaInicio = Carbon::parse($request->get('fecha_inicio'));
            $fechaFin = Carbon::parse($request->get('fecha_fin'));
        }

        $items = Accidente::query()
            ->select([
                'instituciones.nombre AS nombre_institucion',
                DB::raw('COUNT(*) AS total'),
            ])
            ->join('instituciones', 'instituciones.id', '=', 'accidentes.institucion_id')
            ->where($campo, '>=', $fechaInicio->format('Y-m-d').' 00:00:00')
            ->where($campo, '<=', $fechaFin->format('Y-m-d').' 23:59:59')
            ->groupBy('instituciones.nombre')
            ->orderBy('instituciones.nombre')
            ->get();

        $logo_membrete = Settings::logo_membrete();
        $this->params['items'] = $items;
        $this->params['campo'] = $campo;
        $this->params['fechaInicio'] = $fechaInicio;
        $this->params['fechaFin'] = $fechaFin;
        $this->params['logo_membrete'] = $logo_membrete;

        return view($this->route_name.'.carga_registros_por_institucion', compact('logo_membrete'))->with('params', $this->params);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function total_accidentes(Request $request)
    {
        if (! RequestUtils::havePermission($this->route_name, 'total_accidentes')) {
            return redirect('/home');
        }

        $items = null;
        $titulo = 'Total de Accidentes agrupados por Año';
        $tipo = 'agrupar_anho';
        $fechaInicio = Carbon::now()->subDays(29);
        $fechaFin = Carbon::now();
        if ($request->has('tipo') and $request->has('fecha_inicio') and $request->has('fecha_fin')) {
            $tipo = trim(strtolower($request->get('tipo')));
            $fechaInicio = Carbon::parse($request->get('fecha_inicio'));
            $fechaFin = Carbon::parse($request->get('fecha_fin'));
        }

        $query = Accidente::query();

        if (Auth::user()->hasRole('sysadmin')) {
            // sysadmin puede ver todo
        } elseif (Auth::user()->hasRole('administrador')) {
            // nivel institucion
            $query->where('accidentes.institucion_id', Auth::user()->sede->institucion_id);
        } else {
            // directivo/agente/usuario (todos a nivel sede)
            // nivel sede
            $query->where('accidentes.sede_id', Auth::user()->sede_id);
        }

        $query->where('accidentes.fecha_accidente', '>=', $fechaInicio->format('Y-m-d').' 00:00:00')
                ->where('accidentes.fecha_accidente', '<=', $fechaFin->format('Y-m-d').' 23:59:59');

        switch ($tipo) {
            case 'agrupar_mes':
                $titulo = 'Total de Accidentes agrupados por Mes';
                $query->select([
                    DB::raw("CASE MONTH(accidentes.fecha_accidente)
                        WHEN 1 THEN 'Enero'
                        WHEN 2 THEN 'Febrero'
                        WHEN 3 THEN 'Marzo'
                        WHEN 4 THEN 'Abril'
                        WHEN 5 THEN 'Mayo'
                        WHEN 6 THEN 'Junio'
                        WHEN 7 THEN 'Julio'
                        WHEN 8 THEN 'Agosto'
                        WHEN 9 THEN 'Setiembre'
                        WHEN 10 THEN 'Octubre'
                        WHEN 11 THEN 'Noviembre'
                        WHEN 12 THEN 'Diciembre'
                        END AS agrupador"),
                    DB::raw('COUNT(*) AS total'),
                ]);
                $query->groupBy('agrupador');
                $query->orderBy(DB::raw('agrupador'));
                break;

            case 'agrupar_dia':
                $titulo = 'Total de Accidentes agrupados por Día';
                $query->select([
                    DB::raw("CONCAT('Dia ', LPAD(DAYOFMONTH(accidentes.fecha_accidente), 2, '0')) AS agrupador"),
                    DB::raw('COUNT(*) AS total'),
                ]);
                $query->groupBy(DB::raw("CONCAT('Dia ', LPAD(DAYOFMONTH(accidentes.fecha_accidente), 2, '0'))"));
                $query->orderBy(DB::raw("CONCAT('Dia ', LPAD(DAYOFMONTH(accidentes.fecha_accidente), 2, '0'))"));
                break;

            case 'agrupar_dia_semana':
                $titulo = 'Total de Accidentes agrupados por Día de la Semana';
                $query->select([
                    DB::raw("CASE DAYOFWEEK(accidentes.fecha_accidente)
                        WHEN 1 THEN 'Domingo'
                        WHEN 2 THEN 'Lunes'
                        WHEN 3 THEN 'Martes'
                        WHEN 4 THEN 'Miércoles'
                        WHEN 5 THEN 'Jueves'
                        WHEN 6 THEN 'Viernes'
                        WHEN 7 THEN 'Sábado'
                        END AS agrupador"),
                    DB::raw('COUNT(*) AS total'),
                ]);
                $query->groupBy('agrupador');
                $query->orderBy(DB::raw('agrupador'));
                break;

            case 'agrupar_dia_anho':
                $titulo = 'Total de Accidentes agrupados por Día del Año';
                $query->select([
                    DB::raw("CONCAT('Dia ', LPAD(DAYOFYEAR(accidentes.fecha_accidente), 2, '0')) AS agrupador"),
                    DB::raw('COUNT(*) AS total'),
                ]);
                $query->groupBy(DB::raw("CONCAT('Dia ', LPAD(DAYOFYEAR(accidentes.fecha_accidente), 2, '0'))"));
                $query->orderBy(DB::raw("CONCAT('Dia ', LPAD(DAYOFYEAR(accidentes.fecha_accidente), 2, '0'))"));
                break;

            case 'agrupar_hora':
                $titulo = 'Total de Accidentes agrupados por Hora';
                $query->select([
                    DB::raw("CONCAT('Hora ', LPAD(HOUR(accidentes.fecha_accidente), 2, '0')) AS agrupador"),
                    DB::raw('COUNT(*) AS total'),
                ]);
                $query->groupBy(DB::raw("CONCAT('Hora ', LPAD(HOUR(accidentes.fecha_accidente), 2, '0'))"));
                $query->orderBy(DB::raw("CONCAT('Hora ', LPAD(HOUR(accidentes.fecha_accidente), 2, '0'))"));
                break;

            case 'agrupar_anho':
            default:
                $titulo = 'Total de Accidentes agrupados por Año';
                $query->select([
                    DB::raw('YEAR(accidentes.fecha_accidente) AS agrupador'),
                    DB::raw('COUNT(*) AS total'),
                ]);
                $query->groupBy(DB::raw('YEAR(accidentes.fecha_accidente)'));
                $query->orderBy(DB::raw('YEAR(accidentes.fecha_accidente)'));
                break;
        }

        $items = $query->get();

        $keys = $values = [];
        foreach ($items as $item) {
            $keys[] = $item->agrupador;
            $values[] = $item->total;
        }
        $logo_membrete = Settings::logo_membrete();
        $this->params['items'] = $items;
        $this->params['titulo'] = $titulo;
        $this->params['tipo'] = $tipo;
        $this->params['fechaInicio'] = $fechaInicio;
        $this->params['fechaFin'] = $fechaFin;
        $this->params['keys'] = $keys;
        $this->params['values'] = $values;
        $this->params['tipo-serie'] = ($items->count() > 3) ? 'line' : 'bar';
        $this->params['logo_membrete'] = $logo_membrete;

        return view($this->route_name.'.total_accidentes', compact('logo_membrete'))->with('params', $this->params);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function ubicacion_accidentes(Request $request)
    {
        if (! RequestUtils::havePermission($this->route_name, 'ubicacion_accidentes')) {
            return redirect('/home');
        }

        $fechaInicio = Carbon::now()->subDays(29);
        $fechaFin = Carbon::now();
        if ($request->has('fecha_inicio') and $request->has('fecha_fin')) {
            $fechaInicio = Carbon::parse($request->get('fecha_inicio'));
            $fechaFin = Carbon::parse($request->get('fecha_fin'));
        }

        $query = Accidente::query()->select(['id', 'caratula', 'gps_latitud', 'gps_longitud', 'fecha_accidente']);

        if (Auth::user()->hasRole('sysadmin')) {
            // sysadmin puede ver todo
        } elseif (Auth::user()->hasRole('administrador')) {
            // nivel institucion
            $query->where('accidentes.institucion_id', Auth::user()->sede->institucion_id);
        } else {
            // directivo/agente/usuario (todos a nivel sede)
            // nivel sede
            $query->where('accidentes.sede_id', Auth::user()->sede_id);
        }

        $items = $query->where('accidentes.fecha_accidente', '>=', $fechaInicio->format('Y-m-d').' 00:00:00')
                ->where('accidentes.fecha_accidente', '<=', $fechaFin->format('Y-m-d').' 23:59:59')
                ->where('gps_latitud', '<>', '')
                ->where('gps_longitud', '<>', '')
                ->get();

        $logo_membrete = Settings::logo_membrete();
        $this->params['items'] = $items;
        $this->params['fechaInicio'] = $fechaInicio;
        $this->params['fechaFin'] = $fechaFin;
        $this->params['logo_membrete'] =  $logo_membrete;

        return view($this->route_name.'.ubicacion_accidentes', compact('logo_membrete'))->with('params', $this->params);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function totales_por_indicador(Request $request)
    {
        if (! RequestUtils::havePermission($this->route_name, 'totales_por_indicador')) {
            return redirect('/home');
        }

        if (Auth::user()->hasRole('sysadmin')) {
            $grupoPreguntas = GrupoDePreguntas::with(['preguntas' => function ($query) {
                $query->orderBy('orden', 'asc');
            }])->get();
        } else {
            // Obtener grupos y preguntas masters
            $masters = GrupoDePreguntas::where('master', '=', 1)
                        ->with(['preguntas' => function ($query) {
                            $query->orderBy('orden', 'asc');
                        }])
                        ->get();

            // Obtener grupos y preguntas de la institución del usuario
            $institucionales = Auth::user()->sede->institucion->grupoDePreguntas()
                                ->with(['preguntas' => function ($query) {
                                    $query->orderBy('orden', 'asc');
                                }])
                                ->where('master', '=', 0)
                                ->orderBy('orden')
                                ->get();

            // Fusionar grupos master con grupos de la institución
            $grupoPreguntas = $masters->merge($institucionales);
        }

        $preguntas = [];
        foreach ($grupoPreguntas as $grupo) {
            foreach ($grupo->preguntas as $pregunta) {
                $preguntas[$pregunta->id] = $grupo->nombre.' / '.$pregunta->pregunta;
            }
        }

        $items = collect([]);
        $pregunta = null;
        $pregunta_id = null;
        $fechaInicio = Carbon::now()->subDays(29);
        $fechaFin = Carbon::now();
        $keys = $values = [];
        $titulo = '';
        if ($request->has('pregunta_id') and $request->has('fecha_inicio') and $request->has('fecha_fin')) {
            $pregunta_id = trim(strtolower($request->get('pregunta_id')));
            $fechaInicio = Carbon::parse($request->get('fecha_inicio'));
            $fechaFin = Carbon::parse($request->get('fecha_fin'));

            $pregunta = Pregunta::find($pregunta_id);

            if ($pregunta->tipo == 'number') {
                $fields = [
                    DB::raw("TRIM(IFNULL(respuesta_numero, '')) AS agrupador"),
                    DB::raw('COUNT(*) AS total'),
                ];
                $groupBy = DB::raw("TRIM(IFNULL(respuesta_numero, ''))");
                $orderBy = DB::raw("TRIM(IFNULL(respuesta_numero, ''))");
            } else {
                $fields = [
                    DB::raw("TRIM(IFNULL(respuesta_texto, '')) AS agrupador"),
                    DB::raw('COUNT(*) AS total'),
                ];
                $groupBy = DB::raw("TRIM(IFNULL(respuesta_texto, ''))");
                $orderBy = DB::raw("TRIM(IFNULL(respuesta_texto, ''))");
            }

            $titulo = 'Datos de Respuestas para '.$pregunta->pregunta;

            $query = Respuesta::query()
                ->select($fields)
                ->join('accidentes', 'accidentes.id', '=', 'respuestas.accidente_id', 'left');

            if (Auth::user()->hasRole('sysadmin')) {
                // sysadmin puede ver todo
            } elseif (Auth::user()->hasRole('administrador')) {
                // nivel institucion
                $query->where('accidentes.institucion_id', Auth::user()->sede->institucion_id);
            } else {
                // directivo/agente/usuario (todos a nivel sede)
                // nivel sede
                $query->where('accidentes.sede_id', Auth::user()->sede_id);
            }

            $query->where('accidentes.fecha_accidente', '>=', $fechaInicio->format('Y-m-d').' 00:00:00')
                    ->where('accidentes.fecha_accidente', '<=', $fechaFin->format('Y-m-d').' 23:59:59')
                    ->where('pregunta_id', $pregunta->id)
                    ->groupBy($groupBy)
                    ->orderBy($orderBy);

            $items = $query->get();

            foreach ($items as $item) {
                $keys[] = $item->agrupador;
                $values[] = $item->total;
            }
        }

        $logo_membrete = Settings::logo_membrete();
        $this->params['preguntas'] = $preguntas;
        $this->params['items'] = $items;
        $this->params['pregunta_id'] = $pregunta_id;
        $this->params['fechaInicio'] = $fechaInicio;
        $this->params['fechaFin'] = $fechaFin;
        $this->params['keys'] = $keys;
        $this->params['values'] = $values;
        $this->params['titulo'] = $titulo;
        $this->params['logo_membrete'] = $logo_membrete;

        return view($this->route_name.'.totales_por_indicador', compact('logo_membrete'))->with('params', $this->params);
    }
}
