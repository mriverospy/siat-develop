<?php

namespace App\Jobs;

use App\Models\Accidente;
use App\Models\Notificacion;
use App\Models\Pregunta;
use DateTime;
use Illuminate\Database\QueryException;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use League\Csv\Reader;
use Validator;

class ProcesarArchivoDatos extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $jobPayload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $jobPayload)
    {
        $this->jobPayload = $jobPayload;
    }

    public function insertarAccidente($accidente, $respuestas)
    {
        if (count($accidente) == 0) {
            return [
                'estado' => false,
                'errores' => ['No se encontraron los datos de cabecera para el accidente'],
            ];
        }

        if (count($respuestas) == 0) {
            return [
                'estado' => false,
                'errores' => ['No se definieron respuestas para el accidente'],
            ];
        }

        $errores = [];

        // Validar cabecera accidente
        $validator = Validator::make($accidente, [
            'caratula' => 'required',
            'fecha_accidente' => 'required|date_format:Y-m-d H:i:s',
            'localidad_id' => 'required|exists:localidades,id',
            'heridos' => 'integer',
            'fallecidos' => 'integer',
            'ilesos' => 'integer',
            'participantes_siniestro' => 'integer',
            'fecha_apertura' => 'date_format:Y-m-d H:i:s',
        ]);
        if ($validator->fails()) {
            $validationMessages = $validator->messages()->toArray();
            foreach ($validationMessages as $field => $messages) {
                foreach ($messages as $message) {
                    $errores[] = $message;
                }
            }

            return [
                'estado' => false,
                'errores' => $errores,
            ];
        }

        // Validar respuestas
        $preguntaIds = [];
        foreach ($respuestas as $respuesta) {
            if (! in_array($respuesta['pregunta_id'], $preguntaIds)) {
                $preguntaIds[] = $respuesta['pregunta_id'];
            }
        }

        $preguntasCollection = Pregunta::activas()->with('detalles')->find($preguntaIds);
        $preguntas = [];
        foreach ($preguntasCollection as $pregunta) {
            $preguntas[$pregunta->id] = $pregunta->toArray();
        }

        if (is_array($respuestas) and count($respuestas) > 0) {
            foreach ($respuestas as $respuesta) {
                $opciones = [];

                $modelo = $preguntasCollection->firstWhere('id', $respuesta['pregunta_id']);
                if (!$modelo || \App\Services\Bloque24Horas::esPregunta($modelo) || !is_scalar($respuesta['valor']) || !$modelo->permiteRespuesta($respuesta['valor'])) {
                    $errores[] = 'Pregunta u opción inactiva o no permitida: '.$respuesta['pregunta_id'];
                    continue;
                }
                $pregunta = $preguntas[$respuesta['pregunta_id']];

                // Validar preguntas tipo radio y check
                if (in_array($pregunta['tipo'], ['radio', 'check'])) {
                    // Sólo validamos si no puede ingresar un valor distinto en "otro"
                    // si puede, se deja abierto
                    if ((int) $pregunta['mostrar_otro'] != 1) {
                        $opciones = explode('|', $pregunta['opciones']);

                        $respuestaValor = trim($respuesta['valor']);

                        if ($respuestaValor != '') {
                            if (! in_array($respuestaValor, $opciones)) {
                                $errores[] = $pregunta['pregunta'].' sólo puede contener uno de los siguientes valores: '.implode(', ', $opciones).'. Valor proveído: '.$respuestaValor;
                            }
                        }
                    }
                }

                // Validar preguntas tipo número
                if (in_array($pregunta['tipo'], ['number'])) {
                    $respuestaValor = trim($respuesta['valor']);

                    if ($respuestaValor != '') {
                        if (! is_numeric($respuestaValor)) {
                            $errores[] = $pregunta['pregunta'].' sólo puede contener un valor numérico. Valor proveído: '.$respuestaValor;
                        }
                    }
                }

                // Validar preguntas tipo fecha
                if (in_array($pregunta['tipo'], ['date'])) {
                    $respuestaValor = trim($respuesta['valor']);
                    if ($respuestaValor != '') {
                        $formattedDate = DateTime::createFromFormat('Y-m-d', $respuestaValor);
                        $valid = DateTime::getLastErrors();
                        if ($valid['warning_count'] != 0 or $valid['error_count'] != 0) {
                            $errores[] = $pregunta['pregunta'].' sólo puede contener una fecha válida. Valor proveído: '.$respuestaValor;
                        }
                    }
                }
            }
        }

        if (count($errores) > 0) {
            return [
                'estado' => false,
                'errores' => $errores,
            ];
        }

        // Guardar Accidente
        try {
            $accidente = Accidente::create($accidente);
        } catch (QueryException $e) {
            return [
                'estado' => false,
                'errores' => [$e->getMessage()],
            ];
        }

        // Procesar respuestas
        if (is_array($respuestas) and count($respuestas) > 0) {
            foreach ($respuestas as $respuesta) {
                $res = [
                    'pregunta_id' => $respuesta['pregunta_id'],
                    'usuario_id' => $accidente['usuario_id'],
                    'agrupador' => $respuesta['agrupador'],
                    'pregunta_texto' => $preguntas[$respuesta['pregunta_id']]['pregunta'],
                    'pregunta_tipo' => $preguntas[$respuesta['pregunta_id']]['tipo'],
                ];
                switch ($preguntas[$respuesta['pregunta_id']]['tipo']) {
                    case 'text':
                    case 'radio':
                        $respuestaValor = trim($respuesta['valor']);
                        if ($respuestaValor != '') {
                            $res['respuesta_texto'] = $respuestaValor;
                            $accidente->respuestas()->create($res);
                        }
                        break;

                    case 'number':
                        $respuestaValor = trim($respuesta['valor']);
                        if ($respuestaValor != '') {
                            $res['respuesta_numero'] = $respuestaValor;
                            $accidente->respuestas()->create($res);
                        }
                        break;

                    case 'check':
                        $res['respuesta_texto'] = trim($respuesta['valor']);
                        if ($res['respuesta_texto'] != '') {
                            $accidente->respuestas()->create($res);
                        }
                        break;

                    case 'date':
                        $respuestaValor = trim($respuesta['valor']);
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

        return [
            'estado' => (count($errores) == 0),
            'errores' => $errores,
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Notificar al usuario
        Notificacion::create([
            'usuario_id' => $this->jobPayload['user']->id,
            'fecha' => date('Y-m-d H:i:s'),
            'asunto' => 'Inicio de Procesamiento de Datos',
            'mensaje' => "El SIAT ha comenzado a procesar su archivo de datos {$this->jobPayload['originalName']}.",
            'leido' => false,
        ]);

        $accidenteCabecera = [
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
            'gps_latitud',
            'gps_longitud',
            'heridos',
            'fallecidos',
            'ilesos',
            'participantes_siniestro',
            'fecha_apertura',
        ];

        $dataFile = $this->jobPayload['targetPath'].'/'.$this->jobPayload['targetFileName'];

        // Procesar el archivo
        if (file_exists($dataFile)) {
            // Iniciar transacción
            DB::beginTransaction();

            // Leer CSV
            $reader = Reader::createFromPath($dataFile);

            // Iterar sobre CSV
            $errores = [];
            $accidente = $respuestas = [];
            foreach ($reader as $index => $row) {
                // si fila no es array, ignorar
                if (! is_array($row)) {
                    continue;
                }

                // Si es array pero hay un solo elemento
                if (is_array($row) and count($row) < 2) {
                    continue;
                }

                // Si es primera fila y no empieza con caratula
                if ($index == 0 and $row[0] != 'caratula') {
                    DB::rollBack();
                    $errores = [
                        'El archivo de datos debe iniciar con el campo "caratula". El valor encontrado es: '.htmlspecialchars(utf8_decode($row[0])),
                    ];
                    Notificacion::create([
                        'usuario_id' => $this->jobPayload['user']->id,
                        'fecha' => date('Y-m-d H:i:s'),
                        'asunto' => 'Error al Importar Datos',
                        'mensaje' => "El SIAT ha encontrado uno o más errores al procesar su archivo de datos {$this->jobPayload['originalName']}.\nLista de errores:\n".implode("\n", $errores),
                        'leido' => false,
                    ]);

                    return true;
                }

                // chequear si inicia otro accidente
                if ($row[0] == 'caratula' and $index > 0) {
                    $accidente['estado_id'] = 1;
                    $accidente['usuario_id'] = $this->jobPayload['user']->id;
                    $accidente['sede_id'] = $this->jobPayload['user']->sede->id;
                    $accidente['institucion_id'] = $this->jobPayload['user']->sede->institucion->id;
                    $resultado = $this->insertarAccidente($accidente, $respuestas);
                    if ($resultado['estado'] == false) {
                        $errores = array_merge($errores, $resultado['errores']);
                    }
                    $accidente = $respuestas = [];
                }

                // Chequea si primera columna es una columna de cabecera
                if (in_array($row[0], $accidenteCabecera)) {
                    $accidente[$row[0]] = $row[1];
                }
                // chequea si columna de cabecera no es numérico
                elseif (is_numeric($row[0]) == false) {
                    DB::rollBack();
                    $errores = [
                        'Existe un registro con el nombre de campo que no forma parte de los valores de cabecera. Valor encontrado: '.(string) $row[0].' - Valores válidos: '.implode(', ', $accidenteCabecera),
                    ];

                    Notificacion::create([
                        'usuario_id' => $this->jobPayload['user']->id,
                        'fecha' => date('Y-m-d H:i:s'),
                        'asunto' => 'Error al Importar Datos',
                        'mensaje' => "El SIAT ha encontrado uno o más errores al procesar su archivo de datos {$this->jobPayload['originalName']}.\nLista de errores:\n".implode("\n", $errores),
                        'leido' => false,
                    ]);

                    return true;
                } else {
                    // Si el registro tiene menos de 3 columnas (debe tener 3 si o si)
                    if (count($row) < 3) {
                        DB::rollBack();

                        $errores = [
                            'El registro que inicia con la pregunta id '.(string) $row[0].' no tiene un agrupador definido. Valores encontrados: '.implode(', ', $row),
                        ];

                        Notificacion::create([
                            'usuario_id' => $this->jobPayload['user']->id,
                            'fecha' => date('Y-m-d H:i:s'),
                            'asunto' => 'Error al Importar Datos',
                            'mensaje' => "El SIAT ha encontrado uno o más errores al procesar su archivo de datos {$this->jobPayload['originalName']}.\nLista de errores:\n".implode("\n", $errores),
                            'leido' => false,
                        ]);

                        return true;
                    }
                    // Si el registro tiene 3 columnas pero la ultima esta vacia (necesita agrupador)
                    elseif (isset($row[2]) and trim($row[2]) == '') {
                        DB::rollBack();

                        $errores = [
                            'El registro que inicia con la pregunta id '.(string) $row[0].' no tiene un agrupador definido. Valores encontrados: '.implode(', ', $row),
                        ];

                        Notificacion::create([
                            'usuario_id' => $this->jobPayload['user']->id,
                            'fecha' => date('Y-m-d H:i:s'),
                            'asunto' => 'Error al Importar Datos',
                            'mensaje' => "El SIAT ha encontrado uno o más errores al procesar su archivo de datos {$this->jobPayload['originalName']}.\nLista de errores:\n".implode("\n", $errores),
                            'leido' => false,
                        ]);

                        return true;
                    } else {
                        $respuestas[] = [
                            'pregunta_id' => $row[0],
                            'valor' => $row[1],
                            'agrupador' => $row[2],
                        ];
                    }
                }
            }

            // Para procesar último accidente en el csv
            if (count($accidente) > 0 and count($respuestas) > 0) {
                $accidente['estado_id'] = 1;
                $accidente['usuario_id'] = $this->jobPayload['user']->id;
                $accidente['sede_id'] = $this->jobPayload['user']->sede->id;
                $accidente['institucion_id'] = $this->jobPayload['user']->sede->institucion->id;
                $resultado = $this->insertarAccidente($accidente, $respuestas);
                if ($resultado['estado'] == false) {
                    $errores = array_merge($errores, $resultado['errores']);
                }
            }

            // Borrar archivo del servidor
            @unlink($dataFile);

            // Si no hubo errores, commit de lo datos, sino, descartamos todo
            if (count($errores) == 0) {
                DB::commit();
                Notificacion::create([
                    'usuario_id' => $this->jobPayload['user']->id,
                    'fecha' => date('Y-m-d H:i:s'),
                    'asunto' => 'Importación de datos finalizado',
                    'mensaje' => "El SIAT ha procesado correctamente su archivo de datos.\nID Usuario: {$this->jobPayload['user']->id}\nUbicación del archivo en el servidor: {$this->jobPayload['originalName']}",
                    'leido' => false,
                ]);
            } else {
                DB::rollBack();
                Notificacion::create([
                    'usuario_id' => $this->jobPayload['user']->id,
                    'fecha' => date('Y-m-d H:i:s'),
                    'asunto' => 'Error al Importar Datos',
                    'mensaje' => "El SIAT ha encontrado uno o más errores al procesar su archivo de datos {$this->jobPayload['originalName']}.\nLista de errores:\n".implode("\n", $errores),
                    'leido' => false,
                ]);
            }
        } else {
            Notificacion::create([
                'usuario_id' => $this->jobPayload['user']->id,
                'fecha' => date('Y-m-d H:i:s'),
                'asunto' => 'Error al Importar Datos',
                'mensaje' => "El SIAT ha encontrado un error al iniciar el proceso de importación de datos.\nEl archivo de datos no se he encontrado en el servidor.\nPuede contactar con el administrador y proveer los siguientes datos:\nID Usuario: {$this->jobPayload['user']->id}\nUbicación del archivo en el servidor: {$dataFile}",
                'leido' => false,
            ]);
        }
    }
}
