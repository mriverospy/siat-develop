<?php

namespace App\Models;

use Auth;
use DB;
use Illuminate\Database\Eloquent\Model;

class ReporteDatos extends Model
{
    /**
     * Campos de Asignacion Masiva
     *
     * @var array
     */
    protected $fillable = [
        'usuario_id',
        'nombre',
        'descripcion',
        'filtros',
        'agrupar_campos',
        'campos',
        'compartido',
        'instituciones',
        'ejecutar_como_usuario',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'agrupar_campos' => 'boolean',
        'filtros' => 'array',
        'campos' => 'array',
        'compartido' => 'boolean',
        'instituciones' => 'array',
    ];

    /**
     * Un Reporte pertenece a un Usuario
     *
     * @return Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class);
    }

    /**
     * Realiza el paginado para las listas de los ABM's
     * en caso que se le pase un filtro, filtra por los campos
     * establecidos de la forma 'campo' like '%filtro%'
     */
    public function scopeFilteredPaginate($query, $filter = '', $count = 10)
    {
        if (trim($filter) !== '') {
            $query->where('nombre', 'like', '%'.$filter.'%')
                    ->orWhere('descripcion', 'like', '%'.$filter.'%')
                    ->orWhere('id', 'like', '%'.$filter.'%');
        }

        return $query->orderBy('nombre')->paginate($count);
    }

    public function getData($reporte, $fecha_inicio, $fecha_fin, $paginate = false, $count = 10)
    {
        // Mapeo de nombres de campo a columnas
        $columnsMap = [
            'departamento' => 'departamentos.nombre',
            'distrito' => 'distritos.nombre',
            'localidad' => 'localidades.nombre',
            'institucion' => 'instituciones.nombre',
            'sede' => 'sedes.nombre',
            'usuario' => 'CONCAT(usuarios.nombres, \' \', usuarios.apellidos)',
            'caratula' => 'accidentes.caratula',
            'fecha_accidente' => 'DATE(accidentes.fecha_accidente)',
            'expediente_judicial' => 'accidentes.expediente_judicial',
            'sumario_policial' => 'accidentes.sumario_policial',
            'otro' => 'accidentes.otro',
            'dependencia' => 'accidentes.dependencia',
            'lugar' => 'accidentes.lugar',
            'tipo_camino' => 'accidentes.tipo_camino',
            'altura' => 'accidentes.altura',
            'codigo_postal' => 'accidentes.codigo_postal',
            'heridos' => 'accidentes.heridos',
            'fallecidos' => 'accidentes.fallecidos',
            'ilesos' => 'accidentes.ilesos',
            'total_participantes' => 'accidentes.participantes_siniestro',
        ];

        // Mapeo de nombre de filtros a columnas
        $filtrosMapa = [
            'departamento_id' => 'departamentos.nombre',
            'distrito_id' => 'distritos.nombre',
            'localidad_id' => 'localidades.nombre',
            'caratula' => 'accidentes.caratula',
            'expediente_judicial' => 'accidentes.expediente_judicial',
            'sumario_policial' => 'accidentes.sumario_policial',
            'otro' => 'accidentes.otro',
            'dependencia' => 'accidentes.dependencia',
            'lugar' => 'accidentes.lugar',
            'tipo_camino' => 'accidentes.tipo_camino',
            'altura' => 'accidentes.altura',
            'codigo_postal' => 'accidentes.codigo_postal',
            'heridos' => 'accidentes.heridos',
            'fallecidos' => 'accidentes.fallecidos',
            'ilesos' => 'accidentes.ilesos',
            'participantes_siniestro' => 'accidentes.participantes_siniestro',
            'institucion_id' => 'instituciones.nombre',
            'sede_id' => 'sedes.nombre',
        ];

        // Mapeo de nombre de comparadores a comparadores
        $comparadores = [
            'igual' => '=',
            'no_es_igual' => '!=',
            'mayor' => '>',
            'mayor_o_igual' => '>=',
            'menor' => '<',
            'menor_o_igual' => '<=',
            'contiene' => 'LIKE',
            'no_contiene' => 'NOT LIKE',
        ];

        // Query builder
        $query = DB::table('accidentes')
            ->leftJoin('usuarios', 'usuarios.id', '=', 'accidentes.usuario_id')
            ->leftJoin('sedes', 'sedes.id', '=', 'accidentes.sede_id')
            ->leftJoin('instituciones', 'instituciones.id', '=', 'accidentes.institucion_id')
            ->leftJoin('localidades', 'localidades.id', '=', 'accidentes.localidad_id')
            ->leftJoin('distritos', 'distritos.id', '=', 'localidades.distrito_id')
            ->leftJoin('departamentos', 'departamentos.id', '=', 'distritos.departamento_id');

        // Filtrado de datos / ACL
        if (Auth::user()->hasRole('sysadmin')) {
            // no es necesario filtrar nada
        } elseif (Auth::user()->hasRole('administrador')) {
            // filtrar por institucion
            $query->where('accidentes.institucion_id', Auth::user()->sede->institucion->id);
        } elseif (Auth::user()->hasRole('directivo')) {
            // filtrar por sede
            $query->where('accidentes.sede_id', Auth::user()->sede->id);
        } else {
            // filtrar por usuario
            $query->where('accidentes.usuario_id', Auth::user()->id);
        }

        // Agregar filtro de fecha
        $query->where('accidentes.fecha_accidente', '>=', $fecha_inicio.' 00:00:00');
        $query->where('accidentes.fecha_accidente', '<=', $fecha_fin.' 23:59:59');

        if (count($reporte->filtros) > 0) {
            foreach ($reporte->filtros as $filtro) {
                if (stristr(strtolower($filtro['comparador']['valor']), 'contiene') !== false) {
                    $query->where($filtrosMapa[$filtro['filtro']['valor']], $comparadores[$filtro['comparador']['valor']], '%'.$filtro['valor'].'%');
                } else {
                    $query->where($filtrosMapa[$filtro['filtro']['valor']], $comparadores[$filtro['comparador']['valor']], $filtro['valor']);
                }
            }
        }

        // Agregar columnas
        foreach ($reporte->campos as $campo) {
            $query->addSelect(DB::raw($columnsMap[$campo['valor']].' AS '.$campo['valor']));
        }

        // Si hay que agrupar, agregar columnas como group by
        if ($reporte->agrupar_campos) {
            $groupBy = [];
            foreach ($reporte->campos as $campo) {
                $groupBy[] = $columnsMap[$campo['valor']];
            }

            $query->addSelect(DB::raw('COUNT(*) AS total_accidentes'));
            $query->addSelect(DB::raw('SUM(accidentes.heridos) AS total_heridos'));
            $query->addSelect(DB::raw('SUM(accidentes.fallecidos) AS total_fallecidos'));
            $query->addSelect(DB::raw('SUM(accidentes.ilesos) AS total_ilesos'));
            $query->addSelect(DB::raw('SUM(accidentes.participantes_siniestro) AS total_participantes'));

            if ($paginate) {
                $query->groupBy(DB::raw(implode(', ', $groupBy)));
            } else {
                $query->groupBy(DB::raw(implode(', ', $groupBy).' WITH ROLLUP'));
            }
        } else {
            $query->addSelect('accidentes.heridos', 'accidentes.fallecidos', 'accidentes.ilesos', 'accidentes.participantes_siniestro');
        }

        if ($paginate) {
            return $query->paginate($count);
        } else {
            return collect($query->get());
        }
    }

    public function sumarioReporte($reporte)
    {
        // Mapa filtros
        $filtrosMapa = [
            'departamento_id' => '`departamentos`.`nombre`',
            'distrito_id' => '`distritos`.`nombre`',
            'localidad_id' => '`localidades`.`nombre`',
            'caratula' => '`accidentes`.`caratula`',
            'expediente_judicial' => '`accidentes`.`expediente_judicial`',
            'sumario_policial' => '`accidentes`.`sumario_policial`',
            'otro' => '`accidentes`.`otro`',
            'dependencia' => '`accidentes`.`dependencia`',
            'lugar' => '`accidentes`.`lugar`',
            'tipo_camino' => '`accidentes`.`tipo_camino`',
            'altura' => '`accidentes`.`altura`',
            'codigo_postal' => '`accidentes`.`codigo_postal`',
            'heridos' => '`accidentes`.`heridos`',
            'fallecidos' => '`accidentes`.`fallecidos`',
            'ilesos' => '`accidentes`.`ilesos`',
            'participantes_siniestro' => '`accidentes`.`participantes_siniestro`',
            'institucion_id' => '`instituciones`.`nombre`',
            'sede_id' => '`sedes`.`nombre`',
        ];

        $comparadores = [
            'igual' => "= '?'",
            'no_es_igual' => "!= '?'",
            'mayor' => "> '?'",
            'mayor_o_igual' => ">= '?'",
            'menor' => "< '?'",
            'menor_o_igual' => "<= '?'",
            'contiene' => "LIKE '%?%'",
            'no_contiene' => "NOT LIKE '%?%'",
        ];

        $query = 'SELECT DATE(`accidentes`.`fecha_accidente`) AS `fecha_accidente`, ';
        $query .= '   SUM(`accidentes`.`heridos`) AS `total_heridos`,';
        $query .= '   SUM(`accidentes`.`fallecidos`) AS `total_fallecidos`,';
        $query .= '   SUM(`accidentes`.`ilesos`) AS `total_ilesos`,';
        $query .= '   SUM(`accidentes`.`participantes_siniestro`) AS `total_participantes_siniestro`';
        $query .= ' FROM `accidentes` ';
        $query .= ' LEFT JOIN `sedes` ON `sedes`.`id` = `accidentes`.`sede_id` ';
        $query .= ' LEFT JOIN `instituciones` ON `instituciones`.`id` = `accidentes`.`institucion_id` ';
        $query .= ' LEFT JOIN `localidades` ON `localidades`.`id` = `accidentes`.`localidad_id` ';
        $query .= ' LEFT JOIN `distritos` ON `distritos`.`id` = `localidades`.`distrito_id` ';
        $query .= ' LEFT JOIN `departamentos` ON `departamentos`.`id` = `distritos`.`departamento_id` ';
        $query .= ' WHERE ';
        if (Auth::user()->hasRole('sysadmin')) {
            // no es necesario filtrar nada
        } elseif (Auth::user()->hasRole('administrador')) {
            // filtrar por institucion
            $query .= '    `accidentes`.`institucion_id` = '.Auth::user()->sede->institucion->id.' ';
            $query .= '    AND ';
        } elseif (Auth::user()->hasRole('directivo')) {
            // filtrar por sede
            $query .= '    `accidentes`.`sede_id` = '.Auth::user()->sede->id.' ';
            $query .= '    AND ';
        } else {
            // filtrar por usuario
            $query .= '    `accidentes`.`usuario_id` = '.Auth::user()->id.' ';
            $query .= '    AND ';
        }
        $query .= '    `accidentes`.`fecha_accidente` >= \''.$reporte->fecha_inicio->format('Y-m-d').' 00:00:00\'';
        $query .= '    AND ';
        $query .= '    `accidentes`.`fecha_accidente` <= \''.$reporte->fecha_fin->format('Y-m-d').' 23:59:59\'';
        if (count($reporte->filtros) > 0) {
            foreach ($reporte->filtros as $filtro) {
                $query .= '    AND '.$filtrosMapa[$filtro['filtro']['valor']].' '.str_replace('?', $filtro['valor'], $comparadores[$filtro['comparador']['valor']]).' ';
            }
        }
        $query .= ' GROUP BY `fecha_accidente`';
        $query .= ' ORDER BY `fecha_accidente`';

        $results = DB::select($query);

        $stats = [];
        $rowStats = [];
        foreach ($results as $row) {
            $rowStats[$row->fecha_accidente]['total_heridos'] = $row->total_heridos;
            $rowStats[$row->fecha_accidente]['total_fallecidos'] = $row->total_fallecidos;
            $rowStats[$row->fecha_accidente]['total_ilesos'] = $row->total_ilesos;
            $rowStats[$row->fecha_accidente]['total_participantes_siniestro'] = $row->total_participantes_siniestro;
        }

        $begin = new \DateTime($reporte->fecha_inicio);
        $end = new \DateTime($reporte->fecha_fin);
        $end = $end->modify('+1 day');
        $interval = new \DateInterval('P1D');
        $daterange = new \DatePeriod($begin, $interval, $end);
        foreach ($daterange as $date) {
            $stats['labels'][] = $date->format('j/n');
            if (isset($rowStats[$date->format('Y-m-d')])) {
                $stats['total_heridos'][] = (int) $rowStats[$date->format('Y-m-d')]['total_heridos'];
                $stats['total_fallecidos'][] = (int) $rowStats[$date->format('Y-m-d')]['total_fallecidos'];
                $stats['total_ilesos'][] = (int) $rowStats[$date->format('Y-m-d')]['total_ilesos'];
                $stats['total_participantes_siniestro'][] = (int) $rowStats[$date->format('Y-m-d')]['total_participantes_siniestro'];
            } else {
                $stats['total_heridos'][] = 0;
                $stats['total_fallecidos'][] = 0;
                $stats['total_ilesos'][] = 0;
                $stats['total_participantes_siniestro'][] = 0;
            }
        }

        return $stats;
    }
}
