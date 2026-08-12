<?php

namespace App\Services;

class ReporteService
{
    /**
     * Comparadores
     *
     * @var array
     */
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

    /**
     * Filtros
     *
     * @var array
     */
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
    ];

    /**
     * Service Constructor
     */
    public function __construct()
    {
        // silence is golden
    }

    public function comparadores()
    {
        return $this->comparadores;
    }

    public function filtros($usuario)
    {
        if ($usuario->hasRole('sysadmin')) {
            $this->filtros['institucion_id'] = 'Institución';
            $this->filtros['sede_id'] = 'Sede';
        } elseif ($usuario->hasRole('administrador')) {
            // nivel institucion
            $this->filtros['sede_id'] = 'Sede';
        } elseif ($usuario->hasRole('directivo')) {
            // nivel sede
        } else {
            // nivel usuario
        }

        return $this->filtros;
    }
}
