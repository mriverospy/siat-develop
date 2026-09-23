<?php

namespace App\Soap;

use App\Models\Accidente;
use App\Models\GrupoDePreguntas;
use App\Models\Localidad;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Usuario;
use DB;
use Illuminate\Database\QueryException;

class WebService
{
    private $respuesta = [];

    public function __construct()
    {
        $this->respuesta = [
            'status' => 'ERROR',
            'message' => 'Parámetros Incorrectos',
            'data' => null,
        ];
    }

    public function handler($params)
    {
        $received = json_decode($params, true);
        //VALIDACIONES
        if (! is_array($received) || count($received) < 2) {
            $this->respuesta['message'] = 'Parámetros Incorrectos.';

            return $this->respuesta;
        }

        if (empty($received['api_key'])) {
            $this->respuesta['message'] = 'No se recibió el Token del Usuario';

            return $this->respuesta;
        }

        if (empty($received['action'])) {
            $this->respuesta['message'] = 'No se recibio el parametro acción.';

            return $this->respuesta;
        }

        //BUSCA EL USUARIO
        $user = $this->getUserByToken($received['api_key']);
        if (is_null($user)) {
            $this->respuesta['message'] = 'API-KEY incorrecto o no existe.';

            return $this->respuesta;
        }

        //ACCIONES
        if ($received['action'] == 'get_preguntas') {
            return $this->getPreguntas($user);
        } elseif ($received['action'] == 'get_localidades') {
            return $this->getLocalidades();
        } elseif ($received['action'] == 'cargar_respuestas') {
            if (empty($received['data'])) {
                $this->respuesta['message'] = 'No se recibieron los datos para la carga.';

                return $this->respuesta;
            }

            return $this->cargarRespuestas($user, $received['data']);
        } elseif ($received['action'] == 'get_accidentes') {
            return $this->getAccidentes($received['data']);
        } else {
            $this->respuesta['message'] = 'No se pudo determinar la acción a realizar.';

            return $this->respuesta;
        }
    }

    /**
     * Devuelve el ususario con el API-KEY recibido
     * Si no lo encuentra, retorna null
     */
    public function getUserByToken($token)
    {
        return Usuario::where('api_key', '=', $token)->first();
    }

    /**
     * Retorna el listado de preguntas para la institución del
     * del usuario que solicitó el listado (api-key)
     */
    private function getPreguntas($user)
    {
        $institucionales = $user->sede->institucion
                            ->grupoDePreguntas()
                            ->with(['preguntas' => function ($query) {
                                $query->activas()->orderBy('orden', 'asc');
                            }])
                            ->where('master', '=', 0)
                            ->orderBy('orden')
                            ->get();

        $masters = GrupoDePreguntas::where('master', '=', 1)
                    ->with(['preguntas' => function ($query) {
                        $query->activas()->orderBy('orden', 'asc');
                    }])
                    //->union($institucionales)
                    ->get();

        $listadoCompleto = array_merge($this->estructurarGrupos($masters), $this->estructurarGrupos($institucionales));

        $this->respuesta['status'] = 'SUCCESS';
        $this->respuesta['message'] = '';
        $this->respuesta['data'] = $listadoCompleto;

        return $this->respuesta;
    }

    /**
     * Crea un nuevo array con los campos necesarios
     * de la tabla grupos para enviarlos
     */
    private function estructurarGrupos($listado)
    {
        $respuesta = [];
        foreach ($listado as $grupo) {
            $item = [];
            $item['id'] = $grupo->id;
            $item['nombre'] = $grupo->nombre;
            $item['orden'] = $grupo->orden;
            $item['master'] = $grupo->master;
            $item['preguntas'] = $this->estructurarPreguntas($grupo->preguntas);
            array_push($respuesta, $item);
        }

        return $respuesta;
    }

    /**
     * Crea agrega al array grupos las preguntas del mismo,
     * pero solo los campos necesarios para el envío
     */
    private function estructurarPreguntas($listado)
    {
        $respuesta = [];
        foreach ($listado as $pregunta) {
            $item = [];
            $item['id'] = $pregunta->id;
            $item['grupo_de_preguntas_id'] = $pregunta->grupo_de_preguntas_id;
            $item['pregunta'] = $pregunta->pregunta;
            $item['orden'] = $pregunta->orden;
            $item['tipo'] = $pregunta->tipo;
            $item['opciones'] = $pregunta->opciones;
            $item['instrucciones'] = $pregunta->instrucciones;
            $item['mostrar_otro'] = $pregunta->mostrar_otro;
            $item['titulo_otro'] = $pregunta->titulo_otro;
            array_push($respuesta, $item);
        }

        return $respuesta;
    }

    /**
     * Devuelve el listado de Departamentos, Distritos y Localidades
     */
    private function getLocalidades()
    {
        $listadoCompleto = Localidad::with(['distrito', 'distrito.departamento'])->get();

        $this->respuesta['status'] = 'SUCCESS';
        $this->respuesta['message'] = '';
        $this->respuesta['data'] = $listadoCompleto;

        return $this->respuesta;
    }

    private function getAccidentesFields($tipo = 'all')
    {
        $textos = ['caratula', 'expediente_judicial', 'sumario_policial', 'otro', 'dependencia',  'lugar', 'altura', 'tipo_camino', 'gps_latitud', 'gps_longitud'];
        $fechas = ['fecha_accidente'];
        $numeros = ['codigo_postal', 'localidad_id'];
        $arrays = ['respuestas'];

        $todos = array_merge($textos, $fechas, $numeros, $arrays);

        return $tipo === 'text' ? $textos : ($tipo === 'date' ? $fechas : ($tipo === 'number' ? $numeros : ($tipo === 'array' ? $arrays : $todos)));
    }

    /**
     * Guarda las respuestas
     */
    private function cargarRespuestas($user, $data)
    {
        $textos = $this->getAccidentesFields('text');
        $fechas = $this->getAccidentesFields('date');
        $numeros = $this->getAccidentesFields('number');
        $arrays = $this->getAccidentesFields('array');

        $camposCabecera = $this->getAccidentesFields();

        //VALIDACIONES
        //Verifica el total de los campos obligatorios
        if (count($data) < count($camposCabecera)) {
            $this->respuesta['message'] = 'No se han recibido los campos obligatorios del accidente!!';

            return $this->respuesta;
        }

        //Verifica que los campos recibidos tengan el nombre y tipo correctos
        foreach ($data as $key => $value) {
            //Valida todos los campos
            if (in_array($key, $textos)) {
                if (empty($value)) {
                    $this->respuesta['message'] = 'El campo '.$key.' no puede estar vacio.';

                    return $this->respuesta;
                }
            } elseif (in_array($key, $fechas)) {
                if (! $this->validateDate($value)) {
                    $this->respuesta['message'] = 'El valor del campo '.$key.' debe ser del tipo fecha (yyyy-mm-dd hh:mm:ss).';

                    return $this->respuesta;
                }
            } elseif (in_array($key, $numeros)) {
                if (! is_int($value)) {
                    $this->respuesta['message'] = 'El valor del campo '.$key.' debe ser un número entero.';

                    return $this->respuesta;
                }
            } elseif (in_array($key, $arrays)) {
                if (empty($value) || ! is_array($value)) {
                    $this->respuesta['message'] = 'El campo '.$key.' debe ser un array de datos.';

                    return $this->respuesta;
                }
            } else {
                $this->respuesta['message'] = 'No se reconoce el campo: '.$key.'.';

                return $this->respuesta;
            }
        }

        //Verifica localidad
        if (is_null(Localidad::find($data['localidad_id']))) {
            $this->respuesta['message'] = 'La Localidad Seleccionada no existe!!';

            return $this->respuesta;
        }

        //Crea el nuevo accidente
        //$accidente = with(new Accidente)->newInstance($data,true);
        $accidente = new Accidente($data);
        $accidente->estado_id = 1;
        $accidente->fecha_apertura = date('Y-m-d H:i:s');
        $accidente->usuario_id = $user->id;
        $accidente->sede_id = $user->sede->id;
        $accidente->institucion_id = $user->sede->institucion->id;

        DB::beginTransaction();

        try {
            $accidente->save();

            //Procesar Respuestas
            $respuestas = $data['respuestas'];
            $camposRespuestas = ['pregunta_id', 'respuesta'];
            if (! is_array($respuestas) || count($respuestas) < 1) {
                $this->respuesta['message'] = 'No se ha recibido ninguna respuesta.';
                DB::rollBack();

                return $this->respuesta;
            }

            //Valida los campos y Obtiene los IDs de las preguntas
            $preguntasIds = [];
            foreach ($respuestas as $r) {
                //Valida que se encuentren todos los campos
                if (! isset($r['pregunta_id']) || ! isset($r['respuesta'])) {
                    $this->respuesta['message'] = 'No se han recibido los campos obligatorios para la respuesta.';
                    DB::rollBack();

                    return $this->respuesta;
                }
                array_push($preguntasIds, $r['pregunta_id']);
            }
            $preguntasIds = array_unique($preguntasIds);

            $preguntasCollection = Pregunta::activas()->with('detalles')->find($preguntasIds);
            $preguntas = [];
            foreach ($preguntasCollection as $pregunta) {
                $preguntas[$pregunta->id] = $pregunta->toArray();
            }

            $catalogo = $preguntasCollection->keyBy('id');
            foreach ($respuestas as $respuesta) {
                $pregunta = $catalogo->get($respuesta['pregunta_id']);
                if (!$pregunta || !is_scalar($respuesta['respuesta']) || !$pregunta->permiteRespuesta($respuesta['respuesta'])) {
                    $this->respuesta['message'] = 'Pregunta u opción inactiva o no permitida.';
                    DB::rollBack();
                    return $this->respuesta;
                }
            }

            // Guardar respuestas
            foreach ($respuestas as $respuesta) {
                //Valida que no sea una cadena vacia
                if ($respuesta['respuesta'] == '') {
                    $this->respuesta['message'] = 'No se han recibido los campos obligatorios para la respuesta.';
                    DB::rollBack();

                    return $this->respuesta;
                }

                //Crea una nueva instancia de respuesta
                $nuevaRespuesta = new Respuesta();
                $nuevaRespuesta->accidente_id = $accidente->id;
                $nuevaRespuesta->usuario_id = $user->id;
                $nuevaRespuesta->agrupador = $respuesta['agrupador'];
                $nuevaRespuesta->pregunta_id = $respuesta['pregunta_id'];
                $nuevaRespuesta->pregunta_texto = $preguntas[$respuesta['pregunta_id']]['pregunta'];
                $nuevaRespuesta->pregunta_tipo = $preguntas[$respuesta['pregunta_id']]['tipo'];

                switch ($preguntas[$respuesta['pregunta_id']]['tipo']) {
                    case 'number':
                        if (! is_numeric($respuesta['respuesta'])) {
                            $this->respuesta['message'] = 'Las Respuestas de tipo Número deben tener formato numérico ';
                            DB::rollBack();

                            return $this->respuesta;
                        }
                        $nuevaRespuesta->respuesta_numero = $respuesta['respuesta'];
                        break;

                    case 'date':
                        if (! $this->validateDate($respuesta['respuesta'])) {
                            $this->respuesta['message'] = 'Las Respuestas de tipo Fecha deben tener el formato (yyyy-mm-dd hh:mm:ss).';
                            DB::rollBack();

                            return $this->respuesta;
                        }
                        $nuevaRespuesta->respuesta_fecha = $respuesta['respuesta'];
                        break;

                    default:
                        $nuevaRespuesta->respuesta_texto = $respuesta['respuesta'];
                        break;
                }

                $nuevaRespuesta->save();
            }
        } catch (QueryException $e) {
            $this->respuesta['message'] = 'No se pudo guardar el Accidente: '.$e->getMessage();
            DB::rollBack();

            return $this->respuesta;
        }

        $this->respuesta['status'] = 'SUCCESS';
        $this->respuesta['message'] = 'Accidente cargado correctamente';
        DB::commit();

        return $this->respuesta;
    }

    /**
     * Verifica si el texto recibido corresponde a una fecha válida
     */
    private function validateDate($date)
    {
        $format = 'Y-m-d H:i:s';
        $d = \DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) == $date;
    }

    private function getAccidentes($data)
    {
        $filters = [];
        if (isset($data['filters']) && is_array($data['filters'])) {
            $filters = $data['filters'];
        }
        $limit = isset($data['limit']) ? $data['limit'] : 10;
        $offset = isset($data['offset']) ? $data['offset'] : 0;

        //VALIDACIONES DE FILTROS
        $campos = array_merge($this->getAccidentesFields(), ['id']);
        $operadores = ['like', '=', '>', '<', '>=', '<=', '!='];
        foreach ($filters as $f) {
            //Verifica que se encuentren los 3 campos del filtro recibido
            if (! isset($f['field']) || ! isset($f['operator']) || ! isset($f['value'])) {
                $this->respuesta['message'] = 'El formato de los filtros es incorrecto (field, operator, value).';

                return $this->respuesta;
            }
            //Verifica que en field se haya recibido un campo de accidentes válido
            if (! in_array($f['field'], $campos)) {
                $this->respuesta['message'] = 'No se reconoce el campo '.$f['field'].'.';

                return $this->respuesta;
            }
            //Comprueba que el operador recibido sea admitido
            if (! in_array($f['operator'], $operadores)) {
                $this->respuesta['message'] = "No se reconoce el operador '".$f['operator']."', puede utilizar: 'like', '=', '>', '<', '>=', '<=' y '!='.";

                return $this->respuesta;
            }
            if (count($f['value']) === '') {
                $this->respuesta['message'] = 'Debe ingresar un valor para el filtro del campo: '.$f['field'];

                return $this->respuesta;
            }
        }

        $orderBy = $data['orderBy'];

        try {
            $listado = Accidente::listFiltered($filters, $orderBy, $limit, $offset)->get();
        } catch (QueryException $e) {
            $this->respuesta['message'] = $e->getMessage();
            DB::rollBack();

            return $this->respuesta;
        }

        $this->respuesta['status'] = 'SUCCESS';
        $this->respuesta['message'] = '';
        $this->respuesta['data'] = $listado;

        return $this->respuesta;
    }
}
