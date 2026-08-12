<?php

namespace App\Models;

use Auth;
use DB;
use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
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

    public function sumarioReporte($reporte, $fechaInicio, $fechaFin)
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

        if (! $reporte->compartido) {
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
        }

        $query .= '    `accidentes`.`fecha_accidente` >= \''.$fechaInicio->format('Y-m-d').' 00:00:00\'';
        $query .= '    AND ';
        $query .= '    `accidentes`.`fecha_accidente` <= \''.$fechaFin->format('Y-m-d').' 23:59:59\'';
        if (count($reporte->filtros) > 0) {
            foreach ($reporte->filtros as $filtro) {
                $query .= '    AND '.$filtrosMapa[$filtro['filtro']['valor']].' '.str_replace('?', $filtro['valor'], $comparadores[$filtro['comparador']['valor']]).' ';
            }
        }
        $query .= ' GROUP BY DATE(`accidentes`.`fecha_accidente`)';
        $query .= ' ORDER BY DATE(`accidentes`.`fecha_accidente`)';

        $results = DB::select($query);

        $stats = [];
        $rowStats = [];
        foreach ($results as $row) {
            $rowStats[$row->fecha_accidente]['total_heridos'] = $row->total_heridos;
            $rowStats[$row->fecha_accidente]['total_fallecidos'] = $row->total_fallecidos;
            $rowStats[$row->fecha_accidente]['total_ilesos'] = $row->total_ilesos;
            $rowStats[$row->fecha_accidente]['total_participantes_siniestro'] = $row->total_participantes_siniestro;
        }

        $begin = new \DateTime($fechaInicio);
        $end = new \DateTime($fechaFin);
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

    public function estadisticaPregunta($pregunta, $fechaInicio, $fechaFin, $reporte, $agrupadores)
    {
        // Funciones de Agrupación
        $funcionesAgrupacion = [
            'count' => [
                'sql' => '`?` AS `columna`, COUNT(`?`) AS agrupador_valor',
                'nombre' => 'Contar',
            ],
            'sum' => [
                'sql' => '`?` AS `columna`, SUM(`?`) AS agrupador_valor',
                'nombre' => 'Sumar',
            ],
            'avg' => [
                'sql' => '`?` AS `columna`, AVG(`?`) AS agrupador_valor',
                'nombre' => 'Promedio',
            ],
            'max' => [
                'sql' => '`?` AS `columna`, MAX(`?`) AS agrupador_valor',
                'nombre' => 'Máximo',
            ],
            'min' => [
                'sql' => '`?` AS `columna`, MIN(`?`) AS agrupador_valor',
                'nombre' => 'Mínimo',
            ],
            'std' => [
                'sql' => '`?` AS `columna`, STD(`?`) AS agrupador_valor',
                'nombre' => 'Desviación Estándar',
            ],
            'variance' => [
                'sql' => '`?` AS `columna`, VARIANCE(`?`) AS agrupador_valor',
                'nombre' => 'Varianza Estándar',
            ],
        ];

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

        // definir el campo a agrupar
        $mapaTipoCampo = [
            'text' => 'respuesta_texto',
            'number' => 'respuesta_numero',
            'radio' => 'respuesta_texto',
            'check' => 'respuesta_texto',
            'date' => 'respuesta_fecha',
        ];

        // Construir select
        $campoSelect = [];
        foreach ($agrupadores as $agrupador) {
            $campoSelect[] = str_replace('?', $mapaTipoCampo[$pregunta->tipo], $funcionesAgrupacion[$agrupador]['sql']);
        }

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

        $query = 'SELECT '.implode(', ', $campoSelect);
        $query .= ' FROM `respuestas` ';
        $query .= ' WHERE `accidente_id` IN (';
        $query .= '  SELECT DISTINCT `accidentes`.`id` ';
        $query .= '  FROM `accidentes` ';
        $query .= '  LEFT JOIN `sedes` ON `sedes`.`id` = `accidentes`.`sede_id` ';
        $query .= '  LEFT JOIN `instituciones` ON `instituciones`.`id` = `accidentes`.`institucion_id` ';
        $query .= '  LEFT JOIN `localidades` ON `localidades`.`id` = `accidentes`.`localidad_id` ';
        $query .= '  LEFT JOIN `distritos` ON `distritos`.`id` = `localidades`.`distrito_id` ';
        $query .= '  LEFT JOIN `departamentos` ON `departamentos`.`id` = `distritos`.`departamento_id` ';
        $query .= '  WHERE ';

        if (! $reporte->compartido) {
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
        }

        $query .= '    `fecha_accidente` >= \''.$fechaInicio->format('Y-m-d').' 00:00:00\'';
        $query .= '    AND ';
        $query .= '    `fecha_accidente` <= \''.$fechaFin->format('Y-m-d').' 23:59:59\'';
        if (count($reporte->filtros) > 0) {
            foreach ($reporte->filtros as $filtro) {
                $query .= '    AND '.$filtrosMapa[$filtro['filtro']['valor']].' '.str_replace('?', $filtro['valor'], $comparadores[$filtro['comparador']['valor']]).' ';
            }
        }
        $query .= ') ';
        $query .= ' AND `pregunta_id` = '.$pregunta->id;
        $query .= ' GROUP BY `columna`';
        $query .= ' ORDER BY `columna`';

        $results = DB::select($query);

        $datasetSeries = [];
        if (in_array($pregunta->tipo, ['radio', 'check'])) {
            $opciones = explode('|', $pregunta->opciones);
            foreach ($opciones as $opcion) {
                $datasetSeries[$opcion] = 0;
            }

            foreach ($results as $result) {
                if (isset($datasetSeries[$result->columna])) {
                    $datasetSeries[$result->columna] = $result->agrupador_valor;
                }
            }
        } else {
            foreach ($results as $result) {
                $datasetSeries[$result->columna] = $result->agrupador_valor;
            }
        }

        return collect($datasetSeries);
    }
}
